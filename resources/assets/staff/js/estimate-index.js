import React, { useState, useEffect, useContext } from "react";
import { render } from "react-dom";
import { ConstContext } from "./components/ConstApp";
import ConstApp from "./components/ConstApp";
import PageNation from "./components/PageNation";
import DeclineMessage from "./components/DeclineMessage";
import { useMountedRef } from "../../hooks/useMountedRef";
import ReactLoading from "react-loading";
import StaffTd from "./components/StaffTd";
import SmallDangerModal from "./components/SmallDangerModal";
import classNames from "classnames";
import StatusTd from "./components/Reserve/StatusTd";

const EstimateList = ({ searchParam }) => {
    const { agencyAccount } = useContext(ConstContext);

    const mounted = useMountedRef(); // マウント・アンマウント制御

    // ページャー関連変数
    const [page, setPage] = useState(1);
    const [lastPage, setLastPage] = useState(0);
    const [sort, setSort] = useState("estimate_number");
    const [direction, setDirection] = useState("desc");
    const [total, setTotal] = useState(0);
    const perPage = 10; // 1ページ表示件数

    const [isLoading, setIsLoading] = useState(false); // 重複読み込み防止
    const [isDeleting, setIsDeleting] = useState(false); // 削除処理中か否か

    const [declineMessage, setDeclineMessage] = useState("");

    // TODO 申込者ソートはつけなくて良いか相談
    const [sortParam, setSortParam] = useState({
        estimate_number: "desc",
        estimate_status: "desc",
        "manager.name": "desc",
        departure_date: "desc",
        return_date: "desc",
        "departure.name": "desc",
        "destination.name": "desc",
        name: "desc",
        representative_name: "desc",
        travel_type: "desc",
        application_date: "desc"
    });

    const [lists, setLists] = useState([]); //一覧
    const [deleteNumber, setDeleteNumber] = useState(null); // 削除対象番号

    // 一覧取得
    const fetch = async () => {
        if (!mounted.current) return;
        if (isLoading) return;

        setIsLoading(true);

        const response = await axios
            .get(`/api/${agencyAccount}/estimate/list`, {
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
            setDeleteNumber(null);
            setLists(response.data.data);
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

    // 並び替えリンクをクリックした挙動
    const handleSortClick = column => {
        const direction = sortParam[column] === "asc" ? "desc" : "asc";
        setDirection(direction);
        setSort(column);

        setSortParam({ ...sortParam, [column]: direction });
    };

    // 削除を押した時の挙動
    const handleDelete = async e => {
        if (!mounted.current) return;
        if (isDeleting) return;

        setIsDeleting(true);

        const response = await axios
            .delete(`/api/${agencyAccount}/estimate/${deleteNumber}`)
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
                setPage(newPage);
            } else {
                fetch(); // トータルのみの変更は手動fetch
            }

            setDeclineMessage("見積データの削除が完了しました");
        }
    };

    return (
        <>
            <DeclineMessage message={declineMessage} />
            <div className="tableCont">
                <table>
                    <thead>
                        <tr>
                            <th
                                className="sort"
                                onClick={e =>
                                    handleSortClick("estimate_number")
                                }
                            >
                                <span>見積番号</span>
                            </th>
                            <th
                                className="sort"
                                onClick={e =>
                                    handleSortClick("estimate_status")
                                }
                            >
                                <span>ステータス</span>
                            </th>
                            <th
                                className="sort"
                                onClick={e => handleSortClick("departure_date")}
                            >
                                <span>出発日</span>
                            </th>
                            <th
                                className="sort"
                                onClick={e => handleSortClick("return_date")}
                            >
                                <span>帰着日</span>
                            </th>
                            <th
                                className="sort"
                                onClick={e => handleSortClick("departure.name")}
                            >
                                <span>出発地</span>
                            </th>
                            <th
                                className="sort"
                                onClick={e =>
                                    handleSortClick("destination.name")
                                }
                            >
                                <span>目的地</span>
                            </th>
                            <th className="sort">
                                <span>申込者</span>
                            </th>
                            <th
                                className="sort"
                                onClick={e =>
                                    handleSortClick("representative_name")
                                }
                            >
                                <span>代表参加者</span>
                            </th>
                            <th
                                className="sort"
                                onClick={e => handleSortClick("name")}
                            >
                                <span>旅行名</span>
                            </th>
                            <th
                                className="sort"
                                onClick={e => handleSortClick("travel_type")}
                            >
                                <span>旅行種別</span>
                            </th>
                            <th className="sort">
                                <span>申込種別</span>
                            </th>
                            <th
                                className="sort"
                                onClick={e => handleSortClick("manager.name")}
                            >
                                <span>自社担当</span>
                            </th>
                            <th
                                className="sort"
                                onClick={e =>
                                    handleSortClick("application_date")
                                }
                            >
                                <span>申込日</span>
                            </th>
                            <th className="txtalc wd10">
                                <span>削除</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        {isLoading && (
                            <tr>
                                <td colSpan={14}>
                                    <ReactLoading
                                        type={"bubbles"}
                                        color={"#dddddd"}
                                    />
                                </td>
                            </tr>
                        )}
                        {!isLoading && lists.length === 0 && (
                            <tr>
                                <td colSpan={14}>見積データはありません</td>
                            </tr>
                        )}
                        {!isLoading &&
                            lists.length > 0 &&
                            lists.map((row, index) => (
                                <tr key={index}>
                                    <td>
                                        <a href={row.detailUrl}>
                                            {row.estimate_number ?? "-"}
                                        </a>
                                    </td>
                                    <td className="txtalc">
                                        <StatusTd status={row?.status?.val} />
                                    </td>
                                    <td>{row.departure_date ?? "-"}</td>
                                    <td>{row.return_date ?? "-"}</td>
                                    <td>{row.departure.name ?? "-"}</td>
                                    <td>{row.destination.name ?? "-"}</td>
                                    <td
                                        className={classNames({
                                            txcGray: row.applicant.is_deleted
                                        })}
                                    >
                                        {row.applicant.name ?? "-"}
                                    </td>
                                    <td
                                        className={classNames({
                                            txcGray:
                                                row.representative.is_deleted
                                        })}
                                    >
                                        {row.representative.state_inc_name ??
                                            "-"}
                                    </td>
                                    <td>{row.name ?? "-"}</td>
                                    <td>{row.travel_type.val ?? "-"}</td>
                                    <td>{row.application_type.val ?? "-"}</td>
                                    <StaffTd
                                        name={row?.manager?.name}
                                        isDeleted={row?.manager?.is_deleted}
                                    />
                                    <td>{row.application_date.val ?? "-"}</td>
                                    <td className="txtalc">
                                        <span
                                            className={classNames(
                                                "material-icons",
                                                {
                                                    "js-modal-open": !isDeleting
                                                }
                                            )}
                                            data-target="mdDelete"
                                            onClick={() => {
                                                setDeleteNumber(
                                                    row?.estimate_number
                                                );
                                            }}
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
                id="mdDelete"
                title="この見積を削除しますか？"
                handleAction={handleDelete}
                isActioning={isDeleting}
            />
        </>
    );
};

const Element = document.getElementById("estimateList");
if (Element) {
    const jsVars = Element.getAttribute("jsVars");
    const parsedJsVars = jsVars && JSON.parse(jsVars);
    const searchParam = Element.getAttribute("searchParam");
    const parsedSearchParam = searchParam && JSON.parse(searchParam);
    const formSelects = Element.getAttribute("formSelects");
    const parsedFormSelects = formSelects && JSON.parse(formSelects);

    render(
        <ConstApp jsVars={parsedJsVars}>
            <EstimateList
                searchParam={parsedSearchParam}
                formSelects={parsedFormSelects}
            />
        </ConstApp>,
        document.getElementById("estimateList")
    );
}
