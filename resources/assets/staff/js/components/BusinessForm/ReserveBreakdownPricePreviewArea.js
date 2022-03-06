import _ from "lodash";
import React, { useEffect, useContext, useState } from "react";
import { ConstContext } from "../ConstApp";
import { RESERVE } from "../../constants";
const md5 = require("md5");

/**
 * 代金内訳プレビュー
 *
 * @param {object} reserveCancelInfo キャンセル予約情報
 * @returns
 */
const ReserveBreakdownPricePreviewArea = ({
    reservePrices,
    reserveCancelInfo,
    showSetting,
    setAmountTotal,
    amountTotal,
    partnerManagers
}) => {
    const { documentZeiKbns } = useContext(ConstContext);

    const zeiKbns = showSetting.includes("消費税_非課税/不課税")
        ? documentZeiKbns
        : _.omit(documentZeiKbns, ["tf", "nt"]); // 「非課税/不課税」非表示なら表示用ラベルから当該ラベルを除去

    useEffect(() => {
        // 合計
        let grossSum = 0;
        Object.keys(reservePrices).map((managerId, i) => {
            Object.keys(reservePrices[managerId]).map((reserveNumber, j) => {
                Object.keys(reservePrices[managerId][reserveNumber]).map(
                    (zeiKbn, k) => {
                        reservePrices[managerId][reserveNumber][zeiKbn].map(
                            (row, l) => {
                                // キャンセル予約の場合はcancel_chargeの金額を計算
                                if (reserveCancelInfo?.[reserveNumber]) {
                                    grossSum += parseInt(
                                        row.cancel_charge || 0
                                    );
                                } else {
                                    grossSum += parseInt(row.gross || 0);
                                }
                            }
                        );
                    }
                );
            });
        });

        setAmountTotal(grossSum); //合計金額
    }, [reservePrices]);

    // 予約金額データ
    const [reservePriceBreakdown, setReservePriceBreakdown] = useState([]);

    // 内訳出力用に数量項目をまとめる
    useEffect(() => {
        let temp = {};
        Object.keys(reservePrices).map((managerId, i) => {
            Object.keys(reservePrices[managerId]).map((reserveNumber, j) => {
                temp[reserveNumber] = [];
                Object.keys(reservePrices[managerId][reserveNumber]).map(
                    (zeiKbn, k) => {
                        let t = {
                            partner_manager_id: managerId,
                            gross_ex: 0,
                            quantity: 1,
                            zei_kbn: zeiKbn,
                            gross: 0,
                            cancel_charge: 0
                        };
                        reservePrices[managerId][reserveNumber][zeiKbn].map(
                            (row, l) => {
                                t["gross_ex"] += parseInt(row.gross_ex || 0);
                                t["gross"] += parseInt(row.gross || 0);
                                t["cancel_charge"] += parseInt(
                                    row.cancel_charge || 0
                                );
                            }
                        );
                        temp[reserveNumber].push(t);
                    }
                );
            });
        });
        setReservePriceBreakdown({ ...temp });
    }, [reservePrices]);

    return (
        <>
            <h3>代金内訳</h3>
            <table>
                <thead>
                    <tr>
                        <th>予約番号</th>
                        {showSetting.includes("御社担当") && <th>御社担当</th>}
                        {showSetting.includes("単価・金額") && <th>単価</th>}
                        <th>数量</th>
                        {showSetting.includes("消費税") && <th>消費税</th>}
                        {showSetting.includes("単価・金額") && <th>金額</th>}
                    </tr>
                </thead>
                <tbody>
                    {Object.keys(reservePriceBreakdown).map(
                        (reserveNumber, i) => {
                            return reservePriceBreakdown[reserveNumber].map(
                                (row, j) => {
                                    return (
                                        <tr key={`row${i}_${j}`}>
                                            <td>
                                                {reserveNumber}
                                                {reserveCancelInfo?.[
                                                    reserveNumber
                                                ] && RESERVE.CANCEL_LABEL}
                                            </td>
                                            {showSetting.includes(
                                                "御社担当"
                                            ) && (
                                                <td>
                                                    {_.find(
                                                        partnerManagers,
                                                        function(o) {
                                                            return (
                                                                o.id ==
                                                                row?.partner_manager_id
                                                            );
                                                        }
                                                    )?.org_name ?? "-"}
                                                    {showSetting.includes(
                                                        "御社担当_御社担当(敬称)"
                                                    ) && " 様"}
                                                </td>
                                            )}
                                            {showSetting.includes(
                                                "単価・金額"
                                            ) && (
                                                <td>
                                                    {/**キャンセルの場合はキャンセルチャージ料金を出力 */}
                                                    ￥
                                                    {reserveCancelInfo?.[
                                                        reserveNumber
                                                    ] &&
                                                        row.cancel_charge.toLocaleString()}
                                                    {!reserveCancelInfo?.[
                                                        reserveNumber
                                                    ] &&
                                                        row.gross_ex.toLocaleString()}
                                                </td>
                                            )}
                                            <td>
                                                {row.quantity.toLocaleString()}
                                            </td>
                                            {showSetting.includes("消費税") && (
                                                <td>
                                                    {/**キャンセル予約の場合は消費税表示ナシ */}
                                                    {reserveCancelInfo?.[
                                                        reserveNumber
                                                    ] && "-"}
                                                    {!reserveCancelInfo?.[
                                                        reserveNumber
                                                    ] &&
                                                        (zeiKbns?.[
                                                            row.zei_kbn
                                                        ] ??
                                                            "-")}
                                                </td>
                                            )}
                                            {showSetting.includes(
                                                "単価・金額"
                                            ) && (
                                                <td>
                                                    {/**キャンセル予約の場合はキャンセルチャージの合計がgross */}
                                                    ￥
                                                    {reserveCancelInfo?.[
                                                        reserveNumber
                                                    ] &&
                                                        row.cancel_charge.toLocaleString()}
                                                    {!reserveCancelInfo?.[
                                                        reserveNumber
                                                    ] &&
                                                        row.gross.toLocaleString()}
                                                </td>
                                            )}
                                        </tr>
                                    );
                                }
                            );
                        }
                    )}
                    <tr className="total">
                        <td
                            colSpan={
                                5 -
                                (showSetting.includes("消費税") ? 0 : 1) -
                                (showSetting.includes("単価・金額") ? 0 : 2) -
                                (showSetting.includes("御社担当") ? 0 : 1)
                            }
                        >
                            合計金額
                        </td>
                        <td>￥{amountTotal.toLocaleString()}</td>
                    </tr>
                </tbody>
            </table>
        </>
    );
};

export default ReserveBreakdownPricePreviewArea;
