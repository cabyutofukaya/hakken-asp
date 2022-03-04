import React, { useState, useEffect } from "react";
import { render } from "react-dom";
import PageNation from "./components/PageNation";
import SmallDangerModal from "./components/SmallDangerModal";
import DeclineMessage from "./components/DeclineMessage";
import ReactLoading from "react-loading";
import { useMountedRef } from "../../hooks/useMountedRef";

const AreaList = ({ agencyAccount, searchParam }) => {
    const mounted = useMountedRef(); // マウント・アンマウント制御

    // ページャー関連変数
    const [page, setPage] = useState(1);
    const [lastPage, setLastPage] = useState(0);
    const [sort, setSort] = useState("code");
    const [direction, setDirection] = useState("asc");
    const [total, setTotal] = useState(0);
    const perPage = 10; // 1ページ表示件数

    const [isLoading, setIsLoading] = useState(false); // 重複読み込み防止
    const [isDeleting, setIsDeleting] = useState(false); // 削除処理中か否か

    const [declineMessage, setDeclineMessage] = useState("");

    const [sortParam, setSortParam] = useState({
        code: "asc",
        name: "asc",
        name_en: "asc",
        "v_direction.name": "asc"
    });

    const [areas, setAreas] = useState([]); // 国・地域一覧
    const [deleteId, setDeleteId] = useState(null); // 削除対象ID

    const fetch = async () => {
        if (!mounted.current) return;
        if (isLoading) return;

        setIsLoading(true);

        const response = await axios
            .get(`/api/${agencyAccount}/area/list`, {
                params: {
                    ...searchParam,
                    page: page,
                    sort: sort,
                    direction: direction,
                    per_page: perPage
                }
            })
            .finally(() => {
                if (mounted.current) {
                    setIsLoading(false);
                }
            });
        if (mounted.current && response?.data?.data) {
            setDeleteId(null);
            setAreas(response.data.data);
            // ページャー関連
            setPage(response.data.meta.current_page);
            setLastPage(response.data.meta.last_page);
            setTotal(response.data.meta.total);
        }
    };

    useEffect(() => {
        fetch(); // 一覧取得
    }, [page, sort, direction]);

    // ページリンクをクリックした挙動（ページネーションコンポーネント用）
    const handlePagerClick = (e, targetPage) => {
        e.preventDefault();
        setPage(targetPage);
    };

    // 削除ボタンを押した時の挙動
    const handleDelete = async e => {
        if (!mounted.current) return;
        if (isDeleting) return;

        setIsDeleting(true);

        const response = await axios
            .delete(`/api/${agencyAccount}/area/${deleteId}`)
            .finally(() => {
                $(".js-modal-close").trigger("click"); // 削除モーダルclose
                setTimeout(function() {
                    if (mounted.current) {
                        setIsDeleting(false);
                    }
                }, 3000);
            });

        if (mounted.current && response) {
            const newTotal = total - 1; // 削除処理後の件数
            const newPage = Math.ceil(newTotal / perPage);

            if (newPage < page) {
                // 現在のページ番号よりも総ページ数が少なくなった場合はページ番号を再設定。totalはapiにより更新される
                setPage(newPage);
            } else {
                fetch(); // トータルのみの変更は手動fetch
            }

            setDeclineMessage("項目の削除が完了しました");
        }
    };

    // 並び替えリンクをクリックした挙動
    const handleSortClick = column => {
        const direction = sortParam[column] === "asc" ? "desc" : "asc";
        setDirection(direction);
        setSort(column);

        setSortParam({ ...sortParam, [column]: direction });
    };

    return (
        <>
            <DeclineMessage message={declineMessage} />
            <div className="tableCont">
                <table>
                    <thead>
                        <tr>
                            <th
                                className="sort wd20"
                                onClick={e => handleSortClick("code")}
                            >
                                <span>国・地域コード</span>
                            </th>
                            <th
                                className="sort wd50"
                                onClick={e => handleSortClick("name")}
                            >
                                <span>国・地域名称</span>
                            </th>
                            <th
                                className="sort wd50"
                                onClick={e => handleSortClick("name_en")}
                            >
                                <span>国・地域名称(英)</span>
                            </th>
                            <th
                                className="sort wd50"
                                onClick={e =>
                                    handleSortClick("v_direction.name")
                                }
                            >
                                <span>方面</span>
                            </th>
                            <th className="wd10 txtalc">
                                <span>削除</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        {isLoading && (
                            <tr>
                                <td colSpan={5}>
                                    <ReactLoading
                                        type={"bubbles"}
                                        color={"#dddddd"}
                                    />
                                </td>
                            </tr>
                        )}
                        {!isLoading && areas.length === 0 && (
                            <tr>
                                <td colSpan={5}>国・地域データはありません</td>
                            </tr>
                        )}
                        {!isLoading &&
                            areas.length > 0 &&
                            areas.map((row, index) => (
                                <tr key={index}>
                                    <td>
                                        <a
                                            href={`/${agencyAccount}/master/area/${row?.uuid}/edit`}
                                        >
                                            {row?.code}
                                        </a>
                                    </td>
                                    <td>{row?.name}</td>
                                    <td>{row?.name_en}</td>
                                    <td>{row?.v_direction?.name}</td>
                                    <td className="txtalc">
                                        {!row?.master && (
                                            <span
                                                className="material-icons js-modal-open"
                                                data-target="mdDeleteArea"
                                                onClick={() =>
                                                    setDeleteId(row?.uuid)
                                                }
                                            >
                                                delete
                                            </span>
                                        )}
                                    </td>
                                </tr>
                            ))}
                    </tbody>
                </table>

                {lastPage > 1 && (
                    <PageNation
                        currentPage={page}
                        lastPage={lastPage}
                        onClick={handlePagerClick}
                    />
                )}
            </div>
            <SmallDangerModal
                id="mdDeleteArea"
                title="この項目を削除しますか？"
                handleAction={handleDelete}
                isActioning={isDeleting}
            />
        </>
    );
};

const Element = document.getElementById("areaList");
if (Element) {
    const agencyAccount = Element.getAttribute("agencyAccount");
    const searchParam = Element.getAttribute("searchParam");
    const parsedSearchParam = searchParam && JSON.parse(searchParam);
    const formSelects = Element.getAttribute("formSelects");
    const parsedFormSelects = formSelects && JSON.parse(formSelects);
    render(
        <AreaList
            agencyAccount={agencyAccount}
            searchParam={parsedSearchParam}
            formSelects={parsedFormSelects}
        />,
        document.getElementById("areaList")
    );
}
