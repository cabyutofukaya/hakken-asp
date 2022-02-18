import React, { useState, useEffect } from "react";
import { render } from "react-dom";
import PageNation from "./components/PageNation";
import DeclineMessage from "./components/DeclineMessage";
import classNames from "classnames";
import { useMountedRef } from "../../hooks/useMountedRef";
import ReactLoading from "react-loading";

const BusinessUserList = ({ agencyAccount, searchParam, statusList }) => {
    const mounted = useMountedRef(); // マウント・アンマウント制御

    // ページャー関連変数
    const [page, setPage] = useState(1);
    const [lastPage, setLastPage] = useState(0);
    const [sort, setSort] = useState("id");
    const [direction, setDirection] = useState("desc");
    const [total, setTotal] = useState(0);
    const perPage = 10; // 1ページ表示件数

    const [isLoading, setIsLoading] = useState(false); // 重複読み込み防止

    const [declineMessage, setDeclineMessage] = useState("");

    // TODO kbnを実装する
    const [sortParam, setSortParam] = useState({
        id: "desc",
        name: "asc",
        tel: "asc",
        // kbn: "asc",
        prefecture_code: "asc",
        address: "asc",
        status: "asc"
    });

    const [lists, setLists] = useState([]); //一覧

    // 一覧取得
    const fetch = async () => {
        if (!mounted.current) return;
        if (isLoading) return;

        setIsLoading(true);

        const response = await axios
            .get(`/api/${agencyAccount}/client/business/list`, {
                params: {
                    ...searchParam,
                    page: page,
                    sort: sort,
                    direction: direction,
                    per_page: perPage
                }
            })
            .finally(() => {
                setIsLoading(false);
            });
        if (mounted.current && response?.data?.data) {
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

    // 並び替えリンクをクリックした挙動（StaffListTable用）
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
                                className="sort"
                                onClick={e => handleSortClick("id")}
                            >
                                <span>顧客番号</span>
                            </th>
                            <th
                                className="sort"
                                onClick={e => handleSortClick("name")}
                            >
                                <span>法人名</span>
                            </th>
                            <th
                                className="sort"
                                onClick={e => handleSortClick("tel")}
                            >
                                <span>電話番号</span>
                            </th>
                            <th
                            // className="sort"
                            // onClick={e => handleSortClick("kbn")}
                            >
                                <span>顧客区分</span>
                            </th>
                            <th
                                className="sort"
                                onClick={e =>
                                    handleSortClick("prefecture_code")
                                }
                            >
                                <span>都道府県</span>
                            </th>
                            <th
                                className="sort"
                                onClick={e => handleSortClick("address")}
                            >
                                <span>住所</span>
                            </th>
                            <th
                                className="sort"
                                onClick={e => handleSortClick("status")}
                            >
                                <span>アカウント状態</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        {isLoading && (
                            <tr>
                                <td colSpan={7}>
                                    <ReactLoading
                                        type={"bubbles"}
                                        color={"#dddddd"}
                                    />
                                </td>
                            </tr>
                        )}
                        {!isLoading && lists.length === 0 && (
                            <tr>
                                <td colSpan={7}>法人顧客データはありません</td>
                            </tr>
                        )}
                        {!isLoading &&
                            lists.length > 0 &&
                            lists.map((row, index) => (
                                <tr key={index}>
                                    <td>
                                        <a
                                            href={`/${agencyAccount}/client/business/${row?.user_number}`}
                                        >
                                            {row.user_number ?? "-"}
                                        </a>
                                    </td>
                                    <td>{row.name ?? "-"}</td>
                                    <td>{row.tel ?? "-"}</td>
                                    <td>{row.kbn.val ?? "-"}</td>
                                    <td>{row.prefecture.name ?? "-"}</td>
                                    <td>{row.address ?? "-"}</td>
                                    <td className="txtalc">
                                        <span
                                            className={classNames("status", {
                                                green:
                                                    row.status ==
                                                    statusList.status_valid,
                                                gray:
                                                    row.status ==
                                                    statusList.status_suspend
                                            })}
                                        >
                                            {row.status_label}
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
        </>
    );
};

const Element = document.getElementById("businessUserList");
if (Element) {
    const agencyAccount = Element.getAttribute("agencyAccount");
    const searchParam = Element.getAttribute("searchParam");
    const parsedSearchParam = searchParam && JSON.parse(searchParam);
    const formSelects = Element.getAttribute("formSelects");
    const parsedFormSelects = formSelects && JSON.parse(formSelects);
    const statusList = Element.getAttribute("statusList");
    const parsedStatusList = statusList && JSON.parse(statusList);

    render(
        <BusinessUserList
            agencyAccount={agencyAccount}
            searchParam={parsedSearchParam}
            formSelects={parsedFormSelects}
            statusList={parsedStatusList}
        />,
        document.getElementById("businessUserList")
    );
}
