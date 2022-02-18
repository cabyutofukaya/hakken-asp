import React, { useState, useEffect, useContext } from "react";
import { ConstContext } from "./components/ConstApp";
import { render } from "react-dom";
import ConstApp from "./components/ConstApp";
import PageNation from "./components/PageNation";
import DeclineMessage from "./components/DeclineMessage";
import { useMountedRef } from "../../hooks/useMountedRef";
import ReactLoading from "react-loading";
import StaffTd from "./components/StaffTd";
import classNames from "classnames";

const MessageList = ({ searchParam, consts, formSelects }) => {
    const { agencyAccount } = useContext(ConstContext);

    const mounted = useMountedRef(); // マウント・アンマウント制御

    // ページャー関連変数
    const [page, setPage] = useState(1);
    const [lastPage, setLastPage] = useState(0);
    const [sort, setSort] = useState("reserve.latest_number_issue_at");
    const [direction, setDirection] = useState("desc");
    const [total, setTotal] = useState(0);
    const perPage = 10; // 1ページ表示件数

    const [isLoading, setIsLoading] = useState(false); // 重複読み込み防止

    const [declineMessage, setDeclineMessage] = useState("");

    // TODO 申込者のソートはつけなくて良いか相談
    const [sortParam, setSortParam] = useState({
        "reserve.latest_number_issue_at": "desc",
        application_date: "desc",
        manager: "desc",
        last_received_at: "desc",
        reserve_status: "desc"
    });

    const [lists, setLists] = useState([]); //一覧

    // 一覧取得
    const fetch = async () => {
        if (!mounted.current) return;
        if (isLoading) return;

        setIsLoading(true);

        const response = await axios
            .get(`/api/${agencyAccount}/consultation/message/list`, {
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
            <div className="tableWrap dragTable">
                <DeclineMessage message={declineMessage} />
                <div className="tableCont">
                    <table>
                        <thead>
                            <tr>
                                <th
                                    className="sort"
                                    onClick={e =>
                                        handleSortClick(
                                            "reserve.latest_number_issue_at"
                                        )
                                    }
                                >
                                    <span>見積/予約/依頼番号</span>
                                </th>
                                <th
                                    className="sort"
                                    onClick={e =>
                                        handleSortClick("last_received_at")
                                    }
                                >
                                    <span>最新受信日</span>
                                </th>
                                <th
                                    className="sort"
                                    onClick={e =>
                                        handleSortClick("application_date")
                                    }
                                >
                                    <span>申込日</span>
                                </th>
                                <th>
                                    <span>申込者</span>
                                </th>
                                <th
                                    className="sort"
                                    onClick={e => handleSortClick("manager")}
                                >
                                    <span>自社担当</span>
                                </th>
                                <th
                                    className="sort"
                                    onClick={e =>
                                        handleSortClick("reserve_status")
                                    }
                                >
                                    <span>ステータス</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {isLoading && (
                                <tr>
                                    <td colSpan={6}>
                                        <ReactLoading
                                            type={"bubbles"}
                                            color={"#dddddd"}
                                        />
                                    </td>
                                </tr>
                            )}
                            {!isLoading && lists.length === 0 && (
                                <tr>
                                    <td colSpan={6}>
                                        メッセージ履歴はありません
                                    </td>
                                </tr>
                            )}
                            {!isLoading &&
                                lists.length > 0 &&
                                lists.map((row, index) => (
                                    <tr key={index}>
                                        <td>
                                            {lists[index]?.reserve
                                                ?.record_number && (
                                                <a
                                                    href={
                                                        lists[index]?.info_url
                                                    }
                                                >
                                                    {
                                                        lists[index].reserve
                                                            .record_number
                                                    }
                                                </a>
                                            )}
                                            {!lists[index]?.reserve
                                                ?.record_number && "-"}
                                        </td>
                                        <td>
                                            {lists[index].last_received_at ??
                                                "-"}
                                        </td>
                                        <td>
                                            {lists[index]?.reserve
                                                ?.application_date?.val ?? "-"}
                                        </td>
                                        <td>
                                            {lists[index]?.reserve?.applicant
                                                ?.name ?? "-"}
                                        </td>
                                        <StaffTd
                                            name={
                                                lists[index]?.reserve?.manager
                                                    ?.name
                                            }
                                            isDeleted={
                                                lists[index]?.reserve?.manager
                                                    ?.is_deleted
                                            }
                                        />
                                        <td className="txtalc">
                                            {lists[index]?.reserve_status ??
                                                "-"}
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
        </>
    );
};

const Element = document.getElementById("messageList");
if (Element) {
    const jsVars = Element.getAttribute("jsVars");
    const parsedJsVars = jsVars && JSON.parse(jsVars);
    const searchParam = Element.getAttribute("searchParam");
    const parsedSearchParam = searchParam && JSON.parse(searchParam);
    const formSelects = Element.getAttribute("formSelects");
    const parsedFormSelects = formSelects && JSON.parse(formSelects);
    const consts = Element.getAttribute("consts");
    const parsedConsts = consts && JSON.parse(consts);

    render(
        <ConstApp jsVars={parsedJsVars}>
            <MessageList
                searchParam={parsedSearchParam}
                formSelects={parsedFormSelects}
                consts={parsedConsts}
            />
        </ConstApp>,
        document.getElementById("messageList")
    );
}
