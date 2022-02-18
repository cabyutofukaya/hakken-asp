import React from "react";

/**
 * 航空券情報プレビューエリア
 * airticketPricesは表示対象になっている参加者に絞ったデータが渡ってくる
 *
 * @param {*} param0
 * @returns
 */
const AirticketInfoPreviewArea = ({ reserveSetting, airticketPrices }) => {
    return (
        <>
            <h3>航空券情報</h3>
            <table>
                <thead>
                    <tr>
                        <th>氏名</th>
                        {reserveSetting.includes("座席・クラス") && (
                            <th>座席/クラス</th>
                        )}
                        {reserveSetting.includes("航空会社") && (
                            <th>航空会社</th>
                        )}
                        {reserveSetting.includes("REF番号") && <th>REF番号</th>}
                    </tr>
                </thead>
                <tbody>
                    {Object.keys(airticketPrices).map((key, index) => {
                        return (
                            <tr key={index}>
                                <td>
                                    {airticketPrices[key]?.["user_name"] ?? ""}
                                </td>
                                {reserveSetting.includes("座席・クラス") && (
                                    <td>
                                        {airticketPrices[key]?.["seat"] ?? "-"}/
                                        {airticketPrices[key]?.[
                                            "booking_class"
                                        ] ?? "-"}
                                    </td>
                                )}
                                {reserveSetting.includes("航空会社") && (
                                    <td>
                                        {airticketPrices[key]?.[
                                            "airline_company"
                                        ] ?? "-"}
                                    </td>
                                )}
                                {reserveSetting.includes("REF番号") && (
                                    <td>
                                        {airticketPrices[key]?.[
                                            "reference_number"
                                        ] ?? "-"}
                                    </td>
                                )}
                            </tr>
                        );
                    })}
                </tbody>
            </table>
        </>
    );
};

export default AirticketInfoPreviewArea;
