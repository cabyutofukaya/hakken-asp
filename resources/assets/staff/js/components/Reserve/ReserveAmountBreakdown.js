import React from "react";
import { calcProfitRate } from "../../libs";

const ReserveAmountBreakdown = ({ reserveData }) => {
    return (
        <table className="baseTable">
            <tbody>
                <tr>
                    <th>GRS合計</th>
                    <td>
                        ￥
                        {(
                            reserveData.enabled_reserve_itinerary?.sum_gross ??
                            0
                        ).toLocaleString()}
                    </td>
                </tr>
                <tr>
                    <th>NET合計</th>
                    <td>
                        ￥
                        {(
                            reserveData.enabled_reserve_itinerary?.sum_net ?? 0
                        ).toLocaleString()}
                    </td>
                </tr>
                <tr>
                    <th>利益(利益率)</th>
                    <td>
                        ￥
                        {(
                            reserveData.enabled_reserve_itinerary
                                ?.sum_gross_profit ?? 0
                        ).toLocaleString()}
                        (
                        {calcProfitRate(
                            reserveData.enabled_reserve_itinerary
                                ?.sum_gross_profit ?? 0,
                            reserveData.enabled_reserve_itinerary?.sum_gross ??
                                0
                        ).toFixed(1)}
                        %)
                    </td>
                </tr>
                <tr>
                    <th>請求合計</th>
                    <td>
                        ￥
                        {(reserveData.sum_invoice_amount ?? 0).toLocaleString()}
                    </td>
                </tr>
                <tr>
                    <th>入金合計</th>
                    <td>￥{(reserveData.sum_deposit ?? 0).toLocaleString()}</td>
                </tr>
                <tr>
                    <th>出金合計</th>
                    <td>
                        ￥{(reserveData.sum_withdrawal ?? 0).toLocaleString()}
                    </td>
                </tr>
                <tr>
                    <th>未入金合計</th>
                    <td>
                        ￥{(reserveData.sum_not_deposit ?? 0).toLocaleString()}
                    </td>
                </tr>
                <tr>
                    <th>未出金合計</th>
                    <td>￥{(reserveData.sum_unpaid ?? 0).toLocaleString()}</td>
                </tr>
            </tbody>
        </table>
    );
};

export default ReserveAmountBreakdown;
