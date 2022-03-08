import React, { useState, useEffect } from "react";
import { render } from "react-dom";
import DeclineMessage from "./components/DeclineMessage";
import SmallDangerModal from "./components/SmallDangerModal";
import PageNation from "./components/PageNation";
import ReactLoading from "react-loading";
import { useMountedRef } from "../../hooks/useMountedRef";

/**
 * 日時項目追加
 *
 * @param {*} param0
 * @returns
 */
const DocumentList = ({
    agencyAccount,
    documentCategories,
    currentTab,
    consts,
    permission
}) => {
    const mounted = useMountedRef(); // マウント・アンマウント制御

    const [tab, setTab] = useState(currentTab); // 選択中のタブ

    const [documentLists, setDocumentLists] = useState({}); // リストデータ
    const [deleteId, setDeleteId] = useState(null); // 削除ID

    const [isFetching, setIsFetching] = useState({}); // リスト読込中
    const [isDeleting, setIsDeleting] = useState(false); // 削除処理中

    // ページャー関連変数
    const [page, setPage] = useState({});
    const [lastPage, setLastPage] = useState({});
    const [total, setTotal] = useState({});
    const [sort, setSort] = useState({});
    const [direction, setDirection] = useState({});
    const perPage = 10; // 1ページ表示件数

    const [declineMessage, setDeclineMessage] = useState(""); // 削除完了メッセージ

    const fetch = async tab => {
        if (!mounted.current) return;
        if (isFetching[tab]) return; // 二重リクエスト防止

        setIsFetching({ ...isFetching, [tab]: true }); // API処理中フラグをOn

        const response = await axios
            .get(`/api/${agencyAccount}/document/${tab}/list`, {
                params: {
                    page: page?.[tab] || 1,
                    sort: sort?.[tab] || "id",
                    direction: direction?.[tab] || "asc",
                    per_page: perPage
                }
            })
            .finally(() => {
                if (mounted.current) {
                    setIsFetching({ ...isFetching, [tab]: false }); // API処理中フラグOff
                }
            });

        if (mounted.current && response?.data?.data) {
            const data = response.data;

            // リストデータ
            setDocumentLists({ ...documentLists, [tab]: data.data });

            // ページネーション関連のパラメータを更新
            setPage({ ...page, [tab]: data.meta.current_page });
            setLastPage({ ...lastPage, [tab]: data.meta.last_page });
            setTotal({ ...total, [tab]: data.meta.total });
        } else {
            setDocumentLists({ ...documentLists, [tab]: [] }); // 再取得防止のため空データをセット
        }
    };

    const handleDelete = async id => {
        if (!mounted.current) return;
        if (isDeleting) return; // 二重リクエスト防止

        setIsDeleting(true); // API処理中フラグをOn

        const response = await axios
            .delete(`/api/${agencyAccount}/document/${tab}/${deleteId}`)
            .finally(() => {
                $(".js-modal-close").trigger("click");
                setTimeout(function() {
                    if (mounted.current) {
                        setIsDeleting(false);
                    }
                }, 3000);
            });

        if (mounted.current && response) {
            const newTotal = total[tab] - 1; // 削除処理後の件数
            const newPage = Math.ceil(newTotal / perPage);
            if (newPage < page[tab]) {
                // 現在のページ番号よりも総ページ数が少なくなった場合はページ番号を再設定。totalはapiにより更新される
                setPage({ ...page, [tab]: newPage });
            } else {
                fetch(tab); // totalが変わった場合は手動fetch
            }

            setDeclineMessage("項目の削除が完了しました");
        }
    };

    // タブ切り替え
    const handleTabChange = tabCode => {
        setTab(tabCode);
    };

    // タブが切り替わったらリストを取得
    useEffect(() => {
        if (typeof documentLists[tab] === undefined) {
            if (!isFetching[tab]) fetch(tab); // リストデータ未セットなら当該タブデータを取得
        }
    }, [tab]);

    // ページが変わったらリストを更新
    useEffect(() => {
        if (!isFetching[tab]) fetch(tab); // リストデータ未セットなら当該タブデータを取得
    }, [page?.[tab]]);

    /**
     * ページネーションクリック時イベント
     *
     * @param {*} e
     * @param {*} targetPage ページ番号
     * @param {*} targetTab タブ
     */
    const handlePagerClick = (e, targetPage, targetTab) => {
        e.preventDefault();
        setPage({ ...page, [targetTab]: targetPage });
    };

    return (
        <>
            <DeclineMessage message={declineMessage} />
            <div id="tabNavi" className="document">
                <ul>
                    {/* タブ */}
                    {documentCategories &&
                        Object.keys(documentCategories).map(index => (
                            <li key={`tab${index}`}>
                                <span
                                    className={
                                        "tab " +
                                        (currentTab ===
                                        documentCategories[index]?.code
                                            ? "tabstay"
                                            : "")
                                    }
                                    onClick={() =>
                                        handleTabChange(
                                            documentCategories[index]?.code
                                        )
                                    }
                                >
                                    {documentCategories[index]?.name}
                                </span>
                            </li>
                        ))}
                </ul>
            </div>
            {/* コンテンツ部 */}
            {documentCategories &&
                Object.keys(documentCategories).map(index => {
                    const categoryCode = documentCategories[index]?.code;
                    return (
                        <div
                            key={`contents${index}`}
                            className={
                                "customList " +
                                (currentTab === categoryCode ? "show" : "")
                            }
                        >
                            <h2>
                                <span className="material-icons">article</span>
                                {documentCategories[index]?.name}

                                {permission?.[documentCategories[index].code]
                                    ?.create &&
                                    categoryCode !== consts.receipt && (
                                        <a
                                            href={`/${agencyAccount}/system/document/${categoryCode}/create`}
                                        >
                                            <span className="material-icons">
                                                add_circle
                                            </span>
                                            新規テンプレート追加
                                        </a>
                                    )}
                                {/**領収書は追加テンプレート不要 */}
                            </h2>
                            {!documentLists[categoryCode] ? (
                                <div>
                                    <ReactLoading
                                        type={"bubbles"}
                                        color={"#dddddd"}
                                    />
                                </div>
                            ) : (
                                <>
                                    <table>
                                        <thead>
                                            <tr>
                                                <th className="wd40">
                                                    テンプレート名
                                                </th>
                                                <th className="wd60">説明</th>
                                                <th>削除</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {documentLists[categoryCode].map(
                                                row => (
                                                    <tr
                                                        key={`${index}_${row.id}`}
                                                    >
                                                        <td>
                                                            <a
                                                                href={`/${agencyAccount}/system/document/${categoryCode}/${row?.id}/edit`}
                                                            >
                                                                {row?.name}
                                                            </a>
                                                        </td>
                                                        <td>
                                                            {row?.description}
                                                        </td>
                                                        <td>
                                                            {row?.undelete_item ? (
                                                                "-"
                                                            ) : (
                                                                <>
                                                                    <span
                                                                        className="material-icons js-modal-open"
                                                                        data-target="mdDeleteDocumentCategory"
                                                                        onClick={() =>
                                                                            setDeleteId(
                                                                                row?.id
                                                                            )
                                                                        }
                                                                    >
                                                                        delete
                                                                    </span>
                                                                </>
                                                            )}
                                                        </td>
                                                    </tr>
                                                )
                                            )}
                                        </tbody>
                                    </table>
                                    {lastPage?.[categoryCode] > 1 && (
                                        <PageNation
                                            currentTab={categoryCode}
                                            currentPage={page?.[categoryCode]}
                                            lastPage={lastPage?.[categoryCode]}
                                            onClick={handlePagerClick}
                                        />
                                    )}
                                </>
                            )}
                        </div>
                    );
                })}

            <SmallDangerModal
                id="mdDeleteDocumentCategory"
                title="この設定を削除しますか？"
                handleAction={handleDelete}
                isActioning={isDeleting}
            />
        </>
    );
};

const Element = document.getElementById("documentList");
if (Element) {
    const agencyAccount = Element.getAttribute("agencyAccount");
    const currentTab = Element.getAttribute("currentTab");
    const documentCategories = Element.getAttribute("documentCategories");
    const parsedDocumentCategories =
        documentCategories && JSON.parse(documentCategories);
    const consts = Element.getAttribute("consts");
    const parsedConsts = consts && JSON.parse(consts);
    const permission = Element.getAttribute("permission");
    const parsedPermission = permission && JSON.parse(permission);

    render(
        <DocumentList
            agencyAccount={agencyAccount}
            currentTab={currentTab}
            documentCategories={parsedDocumentCategories}
            consts={parsedConsts}
            permission={parsedPermission}
        />,
        document.getElementById("documentList")
    );
}
