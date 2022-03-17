import React, { useEffect, useContext, useState } from "react";
import { ConstContext } from "../ConstApp";
import { RESERVE } from "../../constants";
const md5 = require("md5");

/**
 * 代金内訳プレビュー
 *
 * 表示、非表示はisShowで判定。合計金額はAPIで必ず保存するので表示、非表示にかかわらず計算する
 *
 * @param {bool} isShow 代金内訳枠を表示するか否か
 * @returns
 */
const BreakdownPricePreviewArea = ({
    isShow,
    isCanceled,
    optionPrices,
    airticketPrices,
    hotelPrices,
    showSetting,
    setAmountTotal,
    amountTotal
}) => {
    const { documentZeiKbns } = useContext(ConstContext);

    const zeiKbns = showSetting.includes("消費税_非課税/不課税")
        ? documentZeiKbns
        : _.omit(documentZeiKbns, ["tf", "nt"]); // 「非課税/不課税」非表示なら表示用ラベルから当該ラベルを除去

    useEffect(() => {
        // 合計
        let grossSum = 0;
        if (isCanceled) {
            // キャンセル予約(→キャンセルチャージの合計を計算)
            grossSum =
                Object.keys(optionPrices).reduce(
                    (sum, key) =>
                        sum + parseInt(optionPrices[key].cancel_charge || 0),
                    0
                ) +
                Object.keys(airticketPrices).reduce(
                    (sum, key) =>
                        sum + parseInt(airticketPrices[key].cancel_charge || 0),
                    0
                ) +
                Object.keys(hotelPrices).reduce(
                    (sum, key) =>
                        sum + parseInt(hotelPrices[key].cancel_charge || 0),
                    0
                );
        } else {
            grossSum =
                Object.keys(optionPrices).reduce(
                    (sum, key) => sum + parseInt(optionPrices[key].gross || 0),
                    0
                ) +
                Object.keys(airticketPrices).reduce(
                    (sum, key) =>
                        sum + parseInt(airticketPrices[key].gross || 0),
                    0
                ) +
                Object.keys(hotelPrices).reduce(
                    (sum, key) => sum + parseInt(hotelPrices[key].gross || 0),
                    0
                );
        }
        setAmountTotal(grossSum); //合計金額
    }, [optionPrices, airticketPrices, hotelPrices]);

    // 内訳用のオプション科目データ
    const [optionPriceBreakdown, setOptionPriceBreakdown] = useState([]);
    // 内訳用の航空券科目データ
    const [airticketPriceBreakdown, setAirticketPriceBreakdown] = useState([]);
    // 内訳用のホテル科目データ
    const [hotelPriceBreakdown, setHotelPriceBreakdown] = useState([]);

    // 内訳出力用に数量項目をまとめる
    useEffect(() => {
        ///////////// オプション科目
        let optionTemp = {};
        Object.keys(optionPrices).map((k, i) => {
            if (
                !optionPrices[k]?.gross &&
                !optionPrices[k]?.gross_ex &&
                !optionPrices[k]?.cancel_charge
            ) {
                return; //単価・税込単価・キャンセルチャージのいずれも0円の場合は表示ナシ
            }
            // 名前・単価・キャンセルチャージ・税区分で同一性をチェック ※asp/app/Traits/BusinessFormTrait.phpのgetOptionPriceBreakdownと同じ処理
            const key = md5(
                `${optionPrices[k]?.name}_${optionPrices[k]?.gross_ex}_${optionPrices[k]?.cancel_charge}_${optionPrices[k]?.zei_kbn}`
            ); // プロパティに日本語が混じると動作が不安なので一応MD5化
            if (!optionTemp.hasOwnProperty(key)) {
                // 参加者情報は不要
                optionTemp[key] = _.omit(optionPrices[k], [
                    "participant_id",
                    "user_name"
                ]);
            } else {
                optionTemp[key]["quantity"] =
                    parseInt(optionTemp[key].quantity || 0) + 1;
                optionTemp[key]["gross"] = parseInt(optionTemp[key].gross || 0);
                optionTemp[key]["cancel_charge"] = parseInt(
                    optionTemp[key].cancel_charge || 0
                ); // 計算に使うので念の為数字にしておく
            }
        });
        setOptionPriceBreakdown({ ...optionTemp });

        ///////////// 航空券科目
        let airticketTemp = {};
        Object.keys(airticketPrices).map((k, i) => {
            if (
                !airticketPrices[k]?.gross &&
                !airticketPrices[k]?.gross_ex &&
                !optionPrices[k]?.cancel_charge
            ) {
                return; //単価・税込単価・キャンセルチャージのいずれも0円の場合は表示ナシ
            }
            // 名前・座席・単価・キャンセルチャージ・税区分で同一性をチェック ※asp/app/Traits/BusinessFormTrait.phpのgetAirticketPriceBreakdownと同じ処理
            const key = md5(
                `${airticketPrices[k]?.name}_${airticketPrices[k]?.seat}_${airticketPrices[k]?.gross_ex}_${airticketPrices[k]?.cancel_charge}_${airticketPrices[k]?.zei_kbn}`
            ); // プロパティに日本語が混じると動作が不安なので一応MD5化
            if (!airticketTemp.hasOwnProperty(key)) {
                // 参加者情報は不要
                airticketTemp[key] = _.omit(airticketPrices[k], [
                    "participant_id",
                    "user_name"
                ]);
            } else {
                airticketTemp[key]["quantity"] =
                    parseInt(airticketTemp[key].quantity || 0) + 1;
                airticketTemp[key]["gross"] = parseInt(
                    airticketTemp[key].gross || 0
                );
                airticketTemp[key]["cancel_charge"] = parseInt(
                    airticketTemp[key].cancel_charge || 0
                ); // 計算に使うので念の為数字にしておく
            }
        });
        setAirticketPriceBreakdown({ ...airticketTemp });

        ///////////// ホテル科目
        let hotelTemp = {};
        Object.keys(hotelPrices).map((k, i) => {
            if (
                !hotelPrices[k]?.gross &&
                !hotelPrices[k]?.gross_ex &&
                !optionPrices[k]?.cancel_charge
            ) {
                return; //単価・税込単価・キャンセルチャージのいずれも0円の場合は表示ナシ
            }
            // 名前・ルームタイプ・単価・キャンセルチャージ・税区分で同一性をチェック ※asp/app/Traits/BusinessFormTrait.phpのgetHotelPriceBreakdownと同じ処理
            const key = md5(
                `${hotelPrices[k]?.name}_${hotelPrices[k]?.room_type}_${hotelPrices[k]?.gross_ex}_${hotelPrices[k]?.cancel_charge}_${hotelPrices[k]?.zei_kbn}`
            ); // プロパティに日本語が混じると動作が不安なので一応MD5化
            if (!hotelTemp.hasOwnProperty(key)) {
                // 参加者情報は不要
                hotelTemp[key] = _.omit(hotelPrices[k], [
                    "participant_id",
                    "user_name"
                ]);
            } else {
                hotelTemp[key]["quantity"] =
                    parseInt(hotelTemp[key].quantity || 0) + 1;
                hotelTemp[key]["gross"] = parseInt(hotelTemp[key].gross || 0);
                hotelTemp[key]["cancel_charge"] = parseInt(
                    hotelTemp[key].cancel_charge || 0
                ); // 計算に使うので念の為数字にしておく
            }
        });
        setHotelPriceBreakdown({ ...hotelTemp });
    }, [optionPrices, airticketPrices, hotelPrices]);

    if (isShow) {
        return (
            <>
                <h3>代金内訳</h3>
                <table>
                    <thead>
                        <tr>
                            <th>内容</th>
                            {showSetting.includes("単価・金額") && (
                                <th>単価</th>
                            )}
                            <th>数量</th>
                            {showSetting.includes("消費税") && <th>消費税</th>}
                            {showSetting.includes("単価・金額") && (
                                <th>金額</th>
                            )}
                        </tr>
                    </thead>
                    <tbody>
                        {/**オプション科目 */}
                        {Object.keys(optionPriceBreakdown).map((k, i) => (
                            <tr key={`option${i}`}>
                                {/**キャンセル予約か否かで出し分け */}
                                {isCanceled && (
                                    <>
                                        <td>
                                            {optionPriceBreakdown[k].name}{" "}
                                            {RESERVE.CANCEL_LABEL}
                                        </td>
                                        {showSetting.includes("単価・金額") && (
                                            <td>
                                                ￥
                                                {optionPriceBreakdown[
                                                    k
                                                ].cancel_charge.toLocaleString()}
                                            </td>
                                        )}
                                        <td>
                                            {optionPriceBreakdown[
                                                k
                                            ].quantity.toLocaleString()}
                                        </td>
                                        {showSetting.includes("消費税") && (
                                            <td>-</td>
                                        )}
                                        {showSetting.includes("単価・金額") && (
                                            <td>
                                                ￥
                                                {(
                                                    optionPriceBreakdown[k]
                                                        .cancel_charge *
                                                    optionPriceBreakdown[k]
                                                        .quantity
                                                ).toLocaleString()}
                                            </td>
                                        )}
                                    </>
                                )}
                                {!isCanceled && (
                                    <>
                                        <td>{optionPriceBreakdown[k].name}</td>
                                        {showSetting.includes("単価・金額") && (
                                            <td>
                                                ￥
                                                {optionPriceBreakdown[
                                                    k
                                                ].gross_ex.toLocaleString()}
                                            </td>
                                        )}
                                        <td>
                                            {optionPriceBreakdown[
                                                k
                                            ].quantity.toLocaleString()}
                                        </td>
                                        {showSetting.includes("消費税") && (
                                            <td>
                                                {zeiKbns?.[
                                                    optionPriceBreakdown[k]
                                                        .zei_kbn
                                                ] ?? "-"}
                                            </td>
                                        )}
                                        {showSetting.includes("単価・金額") && (
                                            <td>
                                                ￥
                                                {(
                                                    optionPriceBreakdown[k]
                                                        .gross *
                                                    optionPriceBreakdown[k]
                                                        .quantity
                                                ).toLocaleString()}
                                            </td>
                                        )}
                                    </>
                                )}
                            </tr>
                        ))}
                        {/**航空券 */}
                        {Object.keys(airticketPriceBreakdown).map((k, i) => (
                            <tr key={`airticket${i}`}>
                                {/** キャンセルか否かで出し分け */}
                                {isCanceled && (
                                    <>
                                        <td>
                                            {airticketPriceBreakdown[k].name}{" "}
                                            {airticketPriceBreakdown[k].seat}{" "}
                                            {RESERVE.CANCEL_LABEL}
                                        </td>
                                        {showSetting.includes("単価・金額") && (
                                            <td>
                                                ￥
                                                {airticketPriceBreakdown[
                                                    k
                                                ].cancel_charge.toLocaleString()}
                                            </td>
                                        )}
                                        <td>
                                            {airticketPriceBreakdown[
                                                k
                                            ].quantity.toLocaleString()}
                                        </td>
                                        {showSetting.includes("消費税") && (
                                            <td>-</td>
                                        )}
                                        {showSetting.includes("単価・金額") && (
                                            <td>
                                                ￥
                                                {(
                                                    airticketPriceBreakdown[k]
                                                        .cancel_charge *
                                                    airticketPriceBreakdown[k]
                                                        .quantity
                                                ).toLocaleString()}
                                            </td>
                                        )}
                                    </>
                                )}
                                {!isCanceled && (
                                    <>
                                        <td>
                                            {airticketPriceBreakdown[k].name}{" "}
                                            {airticketPriceBreakdown[k].seat}
                                        </td>
                                        {showSetting.includes("単価・金額") && (
                                            <td>
                                                ￥
                                                {airticketPriceBreakdown[
                                                    k
                                                ].gross_ex.toLocaleString()}
                                            </td>
                                        )}
                                        <td>
                                            {airticketPriceBreakdown[
                                                k
                                            ].quantity.toLocaleString()}
                                        </td>
                                        {showSetting.includes("消費税") && (
                                            <td>
                                                {zeiKbns?.[
                                                    airticketPriceBreakdown[k]
                                                        .zei_kbn
                                                ] ?? "-"}
                                            </td>
                                        )}
                                        {showSetting.includes("単価・金額") && (
                                            <td>
                                                ￥
                                                {(
                                                    airticketPriceBreakdown[k]
                                                        .gross *
                                                    airticketPriceBreakdown[k]
                                                        .quantity
                                                ).toLocaleString()}
                                            </td>
                                        )}
                                    </>
                                )}
                            </tr>
                        ))}
                        {/**ホテル */}
                        {Object.keys(hotelPriceBreakdown).map((k, i) => (
                            <tr key={`hotel${i}`}>
                                {/**キャンセルか否かで出し分け */}
                                {isCanceled && (
                                    <>
                                        <td>
                                            {hotelPriceBreakdown[k].name}{" "}
                                            {hotelPriceBreakdown[k].room_type}{" "}
                                            {hotelPriceBreakdown[k].quantity}名{" "}
                                            {RESERVE.CANCEL_LABEL}
                                        </td>
                                        {showSetting.includes("単価・金額") && (
                                            <td>
                                                ￥
                                                {hotelPriceBreakdown[
                                                    k
                                                ].cancel_charge.toLocaleString()}
                                            </td>
                                        )}
                                        <td>
                                            {hotelPriceBreakdown[
                                                k
                                            ].quantity.toLocaleString()}
                                        </td>
                                        {showSetting.includes("消費税") && (
                                            <td>-</td>
                                        )}
                                        {showSetting.includes("単価・金額") && (
                                            <td>
                                                ￥
                                                {(
                                                    hotelPriceBreakdown[k]
                                                        .cancel_charge *
                                                    hotelPriceBreakdown[k]
                                                        .quantity
                                                ).toLocaleString()}
                                            </td>
                                        )}
                                    </>
                                )}
                                {!isCanceled && (
                                    <>
                                        <td>
                                            {hotelPriceBreakdown[k].name}{" "}
                                            {hotelPriceBreakdown[k].room_type}{" "}
                                            {hotelPriceBreakdown[k].quantity}名
                                        </td>
                                        {showSetting.includes("単価・金額") && (
                                            <td>
                                                ￥
                                                {hotelPriceBreakdown[
                                                    k
                                                ].gross_ex.toLocaleString()}
                                            </td>
                                        )}
                                        <td>
                                            {hotelPriceBreakdown[
                                                k
                                            ].quantity.toLocaleString()}
                                        </td>
                                        {showSetting.includes("消費税") && (
                                            <td>
                                                {zeiKbns?.[
                                                    hotelPriceBreakdown[k]
                                                        .zei_kbn
                                                ] ?? "-"}
                                            </td>
                                        )}
                                        {showSetting.includes("単価・金額") && (
                                            <td>
                                                ￥
                                                {(
                                                    hotelPriceBreakdown[k]
                                                        .gross *
                                                    hotelPriceBreakdown[k]
                                                        .quantity
                                                ).toLocaleString()}
                                            </td>
                                        )}
                                    </>
                                )}
                            </tr>
                        ))}
                        <tr className="total">
                            <td
                                colSpan={
                                    4 -
                                    (showSetting.includes("消費税") ? 0 : 1) -
                                    (showSetting.includes("単価・金額") ? 0 : 2)
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
    } else {
        return null;
    }
};

export default BreakdownPricePreviewArea;
