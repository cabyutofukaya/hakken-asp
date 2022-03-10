import React, { useState, useEffect, useContext } from "react";
import { ConstContext } from "../ConstApp";
import { useMountedRef } from "../../../../hooks/useMountedRef";
import ReactLoading from "react-loading";

// 一覧取得API URL
const getListApiUrl = (
    reception,
    step,
    types,
    agencyAccount,
    estimateNumber,
    reserveNumber,
    itineraryNumber
) => {
    switch (step) {
        case types.application_step_draft: // 見積
            return `/api/${agencyAccount}/${reception}/${step}/${estimateNumber}/itinerary/${itineraryNumber}/payable/list`;
        case types.application_step_reserve: // 予約
            return `/api/${agencyAccount}/${reception}/${step}/${reserveNumber}/itinerary/${itineraryNumber}/payable/list`;
        default:
            return null;
    }
};

const AccountPayableArea = ({
    isShow,
    reception,
    applicationStep,
    applicationStepList,
    estimateNumber,
    reserveNumber,
    currentItineraryNumber
}) => {
    const { agencyAccount } = useContext(ConstContext);

    const mounted = useMountedRef(); // マウント・アンマウント制御

    const [lists, setLists] = useState([]);

    const [isLoading, setIsLoading] = useState(false); // リスト取得中

    const fetch = async () => {
        if (!mounted.current) return;
        if (isLoading) return;

        setIsLoading(true); // 二重読み込み禁止

        const response = await axios
            .get(
                getListApiUrl(
                    reception,
                    applicationStep,
                    applicationStepList,
                    agencyAccount,
                    estimateNumber,
                    reserveNumber,
                    currentItineraryNumber
                ),
                {
                    params: {
                        sort: "created_at",
                        direction: "desc"
                    }
                }
            )
            .finally(() => {
                if (mounted.current) {
                    setIsLoading(false);
                }
            });

        if (mounted.current && response?.data?.data) {
            const rows = response.data.data;
            setLists([...rows]);
        }
    };

    useEffect(() => {
        if (isShow) {
            if (!currentItineraryNumber) {
                //無効な行程IDの場合はリストを空に
                setLists([]);
            } else {
                // 表示に切り替わったらリスト取得
                fetch();
            }
        }
    }, [isShow, currentItineraryNumber]);

    return (
        <>
            <h2 className="optTit">仕入れ先買掛金</h2>
            <div className="tableWrap dragTable">
                <div className="tableCont">
                    <table>
                        <thead>
                            <tr>
                                <th>
                                    <span>仕入れ先</span>
                                </th>
                                <th>
                                    <span>請求金額</span>
                                </th>
                                <th>
                                    <span>支払期限</span>
                                </th>
                                <th>
                                    <span>出金額</span>
                                </th>
                                <th>
                                    <span>出金日</span>
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
                            {!isLoading && lists.length === 0 && (
                                <tr>
                                    <td colSpan={5}>データがありません。</td>
                                </tr>
                            )}
                            {!isLoading &&
                                lists.length > 0 &&
                                lists.map((row, index) => (
                                    <tr key={index}>
                                        <td>
                                            {/**予約状態の場合は支払管理ページへ遷移可能 */}
                                            {applicationStep ==
                                                applicationStepList.application_step_reserve && (
                                                <a
                                                    href={`/${agencyAccount}/management/payment/index?payable_number=${row?.payable_number}`}
                                                >
                                                    {row?.supplier_name ?? "-"}
                                                </a>
                                            )}
                                            {applicationStep !=
                                                applicationStepList.application_step_reserve &&
                                                (row?.supplier_name ?? "-")}
                                        </td>
                                        <td>
                                            ￥{row.sum_net.toLocaleString()}
                                        </td>
                                        <td>{row?.payment_deadline ?? "-"}</td>
                                        <td>
                                            ￥
                                            {row.sum_withdrawal.toLocaleString()}
                                        </td>
                                        <td>
                                            {row?.latest_withdrawal_date ?? "-"}
                                        </td>
                                    </tr>
                                ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </>
    );
};

export default AccountPayableArea;
