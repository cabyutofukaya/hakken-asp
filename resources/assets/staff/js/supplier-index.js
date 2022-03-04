import React, { useState, useEffect, useContext } from "react";
import { ConstContext } from "./components/ConstApp";
import ConstApp from "./components/ConstApp";
import { render } from "react-dom";
import PageNation from "./components/PageNation";
import SmallDangerModal from "./components/SmallDangerModal";
import DeclineMessage from "./components/DeclineMessage";
import { useMountedRef } from "../../hooks/useMountedRef";
import ReactLoading from "react-loading";

const SupplierList = ({ searchParam }) => {
    const { agencyAccount } = useContext(ConstContext);

    const mounted = useMountedRef(); // マウント・アンマウント制御

    // ページャー関連変数
    const [page, setPage] = useState(1);
    const [lastPage, setLastPage] = useState(0);
    const [sort, setSort] = useState("id");
    const [direction, setDirection] = useState("asc");
    const [total, setTotal] = useState(0);
    const perPage = 10; // 1ページ表示件数

    const [isLoading, setIsLoading] = useState(false); // 重複読み込み防止
    const [isDeleting, setIsDeleting] = useState(false); // 削除処理中か否か

    const [declineMessage, setDeclineMessage] = useState("");

    const [sortParam, setSortParam] = useState({
        code: "asc",
        name: "asc"
    });

    const [suppliers, setSuppliers] = useState([]); //一覧
    const [deleteId, setDeleteId] = useState(null); // 削除対象ID

    const fetch = async () => {
        if (!mounted.current) return;
        if (isLoading) return;

        setIsLoading(true);

        const response = await axios
            .get(`/api/${agencyAccount}/supplier/list`, {
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
            setSuppliers(response.data.data);
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
            .delete(`/api/${agencyAccount}/supplier/${deleteId}`)
            .finally(() => {
                $(".js-modal-close").trigger("click"); // 削除モーダルclose
                setTimeout(function() {
                    if (mounted.current) {
                        setIsDeleting(false);
                    }
                }, 3000);
            });

        if (response) {
            const newTotal = total - 1; // 削除処理後の件数
            const newPage = Math.ceil(newTotal / perPage);

            if (newPage < page) {
                // 現在のページ番号よりも総ページ数が少なくなった場合はページ番号を再設定。totalはapiにより更新される
                if (mounted.current) {
                    setPage(newPage);
                }
            } else {
                fetch(); // トータルのみの変更は手動fetch
            }

            if (mounted.current) {
                setDeclineMessage("項目の削除が完了しました");
            }
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
                                <span>仕入れ先コード</span>
                            </th>
                            <th
                                className="sort wd70"
                                onClick={e => handleSortClick("name")}
                            >
                                <span>仕入れ先名称</span>
                            </th>
                            <th className="wd10 txtalc">
                                <span>削除</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        {isLoading && (
                            <tr>
                                <td colSpan={3}>
                                    <ReactLoading
                                        type={"bubbles"}
                                        color={"#dddddd"}
                                    />
                                </td>
                            </tr>
                        )}
                        {!isLoading && suppliers.length === 0 && (
                            <tr>
                                <td colSpan={3}>仕入れ先データはありません</td>
                            </tr>
                        )}
                        {!isLoading &&
                            suppliers.length > 0 &&
                            suppliers.map((row, index) => (
                                <tr key={index}>
                                    <td>
                                        <a
                                            href={`/${agencyAccount}/master/supplier/${row?.id}/edit`}
                                        >
                                            {row?.code}
                                        </a>
                                    </td>
                                    <td>{row?.name}</td>

                                    <td className="txtalc">
                                        <span
                                            className="material-icons js-modal-open"
                                            data-target="mdDeleteSupplier"
                                            onClick={() => setDeleteId(row?.id)}
                                        >
                                            delete
                                        </span>
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
                id="mdDeleteSupplier"
                title="この項目を削除しますか？"
                handleAction={handleDelete}
                isActioning={isDeleting}
            />
        </>
    );
};

const Element = document.getElementById("supplierList");
if (Element) {
    const jsVars = Element.getAttribute("jsVars");
    const parsedJsVars = jsVars && JSON.parse(jsVars);
    const searchParam = Element.getAttribute("searchParam");
    const parsedSearchParam = searchParam && JSON.parse(searchParam);
    const formSelects = Element.getAttribute("formSelects");
    const parsedFormSelects = formSelects && JSON.parse(formSelects);
    render(
        <ConstApp jsVars={parsedJsVars}>
            <SupplierList
                searchParam={parsedSearchParam}
                formSelects={parsedFormSelects}
            />
        </ConstApp>,
        document.getElementById("supplierList")
    );
}
