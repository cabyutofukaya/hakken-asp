import React, { useState, useEffect, useContext } from "react";
import { ConstContext } from "../ConstApp";
import { useMountedRef } from "../../../../hooks/useMountedRef";
import classNames from "classnames";
import ReactLoading from "react-loading";
import PageNation from "../PageNation";
import { RESERVE_ESTIMATE_STATUS_LABEL_CLASS } from "../../constants";

const HistoryArea = ({ isShow, userNumber, consts, permission }) => {
    const { agencyAccount } = useContext(ConstContext);

    const mounted = useMountedRef(); // マウント・アンマウント制御

    // ページャー関連変数
    const [page, setPage] = useState(1);
    const [lastPage, setLastPage] = useState(0);
    const [sort, setSort] = useState("reserve_estimate_number");
    const [direction, setDirection] = useState("desc");
    const [total, setTotal] = useState(0);
    const perPage = 10; // 1ページ表示件数

    const [sortParam, setSortParam] = useState({
        reserve_estimate_number: "desc",
        departure_date: "desc",
        name: "desc",
        "destination.name": "desc",
        representative_name: "desc",
        headcount: "desc",
        sum_gross: "desc"
    });

    const [lists, setLists] = useState([]);

    const [isLoading, setIsLoading] = useState(false); // リスト取得中

    // 一覧取得
    const fetch = async () => {
        if (!mounted.current) return;
        if (isLoading) return;

        setIsLoading(true); // 二重読み込み禁止

        const response = await axios
            .get(
                `/api/${agencyAccount}/client/business/${userNumber}/usage_history/list`,
                {
                    params: {
                        page: page,
                        sort: sort,
                        direction: direction,
                        per_page: perPage
                    }
                }
            )
            .finally(() => {
                if (mounted.current) {
                    setIsLoading(false);
                }
            });
        if (mounted.current && response?.data?.data) {
            setLists([...response.data.data]);
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

    // 並び替えリンクをクリックした挙動（StaffListTable用）
    const handleSortClick = column => {
        const direction = sortParam[column] === "asc" ? "desc" : "asc";
        setDirection(direction);
        setSort(column);

        setSortParam({ ...sortParam, [column]: direction });
    };
    // 見積もり作成ボタン押下。見積もり作成ページへ遷移
    const handleCreateButton = e => {
        e.preventDefault();
        location.href =
            consts.estimateNormalCreateUrl +
            `?business_user_number=${userNumber}`;
    };

    return (
        <div
            className={classNames("userList", {
                show: isShow
            })}
        >
            <h2>
                <span className="material-icons">collections_bookmark</span>
                利用履歴
            </h2>
            {permission.create && (
                <ul className="clientContNav">
                    <li>
                        <button onClick={handleCreateButton} className="addBtn">
                            <span className="material-icons">add</span>
                            新規見積作成
                        </button>
                    </li>
                </ul>
            )}
            <div className="tableWrap dragTable">
                <div className="tableCont pt00">
                    <table>
                        <thead>
                            <tr>
                                <th
                                    className="sort"
                                    onClick={e =>
                                        handleSortClick(
                                            "reserve_estimate_number"
                                        )
                                    }
                                >
                                    <span>見積/予約番号</span>
                                </th>
                                <th
                                    className="sort"
                                    onClick={e =>
                                        handleSortClick("departure_date")
                                    }
                                >
                                    <span>出発日</span>
                                </th>
                                <th
                                    className="sort"
                                    onClick={e => handleSortClick("name")}
                                >
                                    <span>案件名</span>
                                </th>
                                <th
                                    className="sort"
                                    onClick={e =>
                                        handleSortClick("destination.name")
                                    }
                                >
                                    <span>目的地</span>
                                </th>
                                <th
                                    className="sort"
                                    onClick={e =>
                                        handleSortClick("representative_name")
                                    }
                                >
                                    <span>代表者</span>
                                </th>
                                <th
                                    className="sort"
                                    onClick={e => handleSortClick("headcount")}
                                >
                                    <span>人数</span>
                                </th>
                                <th
                                    className="sort"
                                    onClick={e => handleSortClick("sum_gross")}
                                >
                                    <span>旅行金額</span>
                                </th>
                                <th className="txtalc">
                                    <span>状況</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {isLoading && (
                                <tr>
                                    <td colSpan={8}>
                                        <ReactLoading
                                            type={"bubbles"}
                                            color={"#dddddd"}
                                        />
                                    </td>
                                </tr>
                            )}
                            {!isLoading && lists.length === 0 && (
                                <tr>
                                    <td colSpan={8}>履歴データはありません</td>
                                </tr>
                            )}
                            {!isLoading &&
                                lists.length > 0 &&
                                lists.map((row, index) => (
                                    <tr key={index}>
                                        <td>
                                            <a href={row.detail_url}>
                                                {row.row_number}
                                            </a>
                                        </td>
                                        <td>{row.departure_date ?? "-"}</td>
                                        <td>{row.name ?? "-"}</td>
                                        <td>{row.destination.name ?? "-"}</td>
                                        <td>
                                            {row.representative_name ?? "-"}
                                        </td>
                                        <td>
                                            {row.headcount.toLocaleString()}
                                        </td>
                                        <td>
                                            ￥{row.sum_gross.toLocaleString()}
                                        </td>
                                        <td className="txtalc">
                                            <span
                                                className={`status ${RESERVE_ESTIMATE_STATUS_LABEL_CLASS[
                                                    row.row_status
                                                ] ?? ""}`}
                                            >
                                                {row.row_status ?? "-"}
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
            </div>
        </div>
    );
};

export default HistoryArea;
