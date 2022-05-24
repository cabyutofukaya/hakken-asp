import React, { useState, useEffect, useReducer, useContext } from "react";
import { ConstContext } from "./components/ConstApp";
import ConstApp from "./components/ConstApp";
import { render } from "react-dom";
import PageNation from "./components/PageNation";
import DeclineMessage from "./components/DeclineMessage";
import { useMountedRef } from "../../hooks/useMountedRef";
import ReactLoading from "react-loading";
import classNames from "classnames";
import _ from "lodash";
import { RESERVE } from "./constants";
import StaffTd from "./components/StaffTd";

const PaymentList = ({ searchParam, formSelects, consts }) => {
    const { agencyAccount } = useContext(ConstContext);

    const mounted = useMountedRef(); // マウント・アンマウント制御

    // ページャー関連変数
    const [page, setPage] = useState(1);
    const [lastPage, setLastPage] = useState(0);
    const [sort, setSort] = useState("reserve.control_number");
    const [direction, setDirection] = useState("desc");
    const [total, setTotal] = useState(0);
    const perPage = 10; // 1ページ表示件数

    const [isLoading, setIsLoading] = useState(false); // 重複読み込み防止
    const [paymentLists, setPaymentLists] = useState([]);

    const [declineMessage, setDeclineMessage] = useState("");

    const [sortParam, setSortParam] = useState({
        "reserve.control_number": "desc",
        "reserve.departure_date": "desc",
        status: "desc",
        total_amount_paid: "asc",
        total_amount_accrued: "asc",
        reserve_manager: "asc",
        "reserve.note": "asc"
    });

    const fetch = async () => {
        if (!mounted.current) return;
        if (isLoading) return;

        setIsLoading(true);

        const response = await axios
            .get(`/api/${agencyAccount}/management/payment/list/reserve`, {
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
            setPaymentLists([...response.data.data]);
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

    return (
        <>
            <div className="tableWrap dragTable">
                <DeclineMessage message={declineMessage} />
                <div className="tableCont managemnetTable">
                    <table>
                        <thead>
                            <tr>
                                <th
                                    className="sort"
                                    onClick={e =>
                                        handleSortClick(
                                            "reserve.control_number"
                                        )
                                    }
                                >
                                    <span>予約番号</span>
                                </th>
                                <th
                                    className="sort txtalc"
                                    onClick={e => handleSortClick("status")}
                                >
                                    <span>ステータス</span>
                                </th>
                                <th
                                    className="sort txtalc"
                                    onClick={e =>
                                        handleSortClick(
                                            "reserve.departure_date"
                                        )
                                    }
                                >
                                    <span>出発日</span>
                                </th>
                                <th
                                    className="sort txtalc"
                                    onClick={e =>
                                        handleSortClick("total_amount_paid")
                                    }
                                >
                                    <span>支払総額</span>
                                </th>
                                <th
                                    className="sort txtalc"
                                    onClick={e =>
                                        handleSortClick("total_amount_accrued")
                                    }
                                >
                                    <span>未払総額</span>
                                </th>
                                <th className="txtalc">
                                    <span>予約詳細</span>
                                </th>
                                <th
                                    className="sort"
                                    onClick={e =>
                                        handleSortClick("reserve_manager")
                                    }
                                >
                                    <span>自社担当</span>
                                </th>
                                <th
                                    className="sort"
                                    onClick={e =>
                                        handleSortClick("reserve.note")
                                    }
                                >
                                    <span>備考</span>
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
                            {!isLoading && paymentLists.length === 0 && (
                                <tr>
                                    <td colSpan={8}>支払データはありません</td>
                                </tr>
                            )}
                            {!isLoading &&
                                paymentLists.length > 0 &&
                                paymentLists.map((row, index) => (
                                    <tr
                                        key={index}
                                        className={classNames({
                                            done:
                                                row?.status ==
                                                consts.statusVals.status_paid
                                        })}
                                    >
                                        {/**支払済or無効仕入の場合はグレー行に */}
                                        <td>
                                            {!row.reserve?.is_deleted && (
                                                <>
                                                    <a href={row.url}>
                                                        {row.reserve
                                                            ?.control_number ??
                                                            "-"}
                                                    </a>
                                                    {row.reserve?.is_canceled &&
                                                        RESERVE.CANCEL_LABEL}
                                                </>
                                            )}
                                            {row.reserve?.is_deleted && (
                                                <>
                                                    {row?.reserve
                                                        ?.control_number ?? "-"}
                                                    {RESERVE.DELETE_LABEL}
                                                </>
                                            )}
                                        </td>
                                        <td className="txtalc">
                                            {row?.status_label && (
                                                <span
                                                    className={classNames(
                                                        "status",
                                                        {
                                                            red:
                                                                row.status ==
                                                                consts
                                                                    .statusVals
                                                                    .status_unpaid,
                                                            gray:
                                                                row.status ==
                                                                consts
                                                                    .statusVals
                                                                    .status_paid
                                                        }
                                                    )}
                                                >
                                                    {row.status_label}
                                                </span>
                                            )}
                                            {!row?.status_label && "-"}
                                        </td>
                                        <td>
                                            {row?.reserve?.departure_date ??
                                                "-"}
                                        </td>
                                        <td className="txtalc">
                                            ￥
                                            {row.total_amount_paid.toLocaleString()}
                                        </td>
                                        <td className="txtalc">
                                            <span
                                                className={classNames({
                                                    red:
                                                        row.total_amount_accrued >
                                                        0,
                                                    payPeriod:
                                                        row.total_amount_accrued >
                                                        0
                                                })}
                                            >
                                                ￥
                                                {row.total_amount_accrued.toLocaleString()}
                                            </span>
                                        </td>
                                        <td className="txtalc">
                                            {!row.reserve?.is_deleted && (
                                                <a href={row.reserve_url ?? ""}>
                                                    <span className="material-icons">
                                                        event_note
                                                    </span>
                                                </a>
                                            )}
                                            {row.reserve?.is_deleted && "-"}
                                        </td>
                                        <StaffTd
                                            name={row?.reserve?.manager?.name}
                                            isDeleted={
                                                row?.reserve?.manager
                                                    ?.is_deleted
                                            }
                                        />
                                        <td>{row?.reserve?.note ?? "-"}</td>
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

const Element = document.getElementById("paymentList");
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
            <PaymentList
                searchParam={parsedSearchParam}
                formSelects={parsedFormSelects}
                consts={parsedConsts}
            />
        </ConstApp>,
        document.getElementById("paymentList")
    );
}
