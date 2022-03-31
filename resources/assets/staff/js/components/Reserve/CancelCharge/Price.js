import React from "react";
import OnlyNumberInput from "../../OnlyNumberInput";
import { SUBJECT_INFO_PROPERTY } from "./CancelChargeArea";

// 粗利を計算
const calcCancelProfit = (row, profitName, chargeName, netName) => {
    row[profitName] =
        (parseInt(row[chargeName]) || 0) - (parseInt(row[netName]) || 0);
};

/**
 * 料金エリア
 *
 * @returns
 */
const Price = ({
    priceSetting,
    setPriceSetting,
    subjectInfo,
    handleBulkChange
}) => {
    // 入力値制御
    const handleChange = (name, value) => {
        const val = parseInt(value ?? 0, 10) || 0; // 念の為、数値化
        priceSetting[name] = val;

        // 同一料金区分の料金データを一括変更
        let m = null;
        if ((m = name.match(/^ad_(.+)/))) {
            // 大人区分

            // 粗利計算
            if (name != "ad_cancel_charge_profit") {
                calcCancelProfit(
                    priceSetting,
                    "ad_cancel_charge_profit",
                    "ad_cancel_charge",
                    "ad_cancel_charge_net"
                );
            }

            // 一括計算処理
            handleBulkChange("ad", m[1], val);
        } else if ((m = name.match(/^ch_(.+)/))) {
            // 子供区分

            // 粗利計算
            if (name != "ch_cancel_charge_profit") {
                calcCancelProfit(
                    priceSetting,
                    "ch_cancel_charge_profit",
                    "ch_cancel_charge",
                    "ch_cancel_charge_net"
                );
            }

            // 一括計算処理
            handleBulkChange("ch", m[1], val);
        } else if ((m = name.match(/^inf_(.+)/))) {
            // 幼児区分

            // 粗利計算
            if (name != "inf_cancel_charge_profit") {
                calcCancelProfit(
                    priceSetting,
                    "inf_cancel_charge_profit",
                    "inf_cancel_charge",
                    "inf_cancel_charge_net"
                );
            }

            // 一括計算処理
            handleBulkChange("inf", m[1], val);
        }

        setPriceSetting({ ...priceSetting });
    };
    return (
        <>
            <h3 className="subjectTit">AD(大人料金)</h3>
            <ul className="sideList">
                <li className="wd20">
                    <span className="inputLabel">キャンセル料金</span>
                    <OnlyNumberInput
                        value={priceSetting.ad_cancel_charge ?? 0}
                        handleChange={e =>
                            handleChange("ad_cancel_charge", e.target.value)
                        }
                    />
                </li>
                <li className="wd20">
                    <span className="inputLabel">仕入先支払料金</span>
                    <OnlyNumberInput
                        value={priceSetting.ad_cancel_charge_net ?? 0}
                        handleChange={e =>
                            handleChange("ad_cancel_charge_net", e.target.value)
                        }
                    />
                </li>
                <li className="wd20">
                    <span className="inputLabel">粗利</span>
                    <OnlyNumberInput
                        value={priceSetting.ad_cancel_charge_profit ?? 0}
                        handleChange={e =>
                            handleChange(
                                "ad_cancel_charge_profit",
                                e.target.value
                            )
                        }
                    />
                </li>
                <li className="wd20">
                    <span className="inputLabel">仕入額</span>
                    <p className="cancelPrice">
                        {subjectInfo?.ad_cost ? (
                            <>￥{subjectInfo.ad_cost.toLocaleString()}</>
                        ) : (
                            "-"
                        )}
                    </p>
                </li>
                <li className="wd10">
                    <span className="inputLabel">手数料率</span>
                    <p className="cancelPrice">
                        {subjectInfo?.ad_commission_rate ? (
                            <>
                                {subjectInfo.ad_commission_rate.toLocaleString()}
                            </>
                        ) : (
                            "-"
                        )}
                        %
                    </p>
                </li>
                <li className="wd20">
                    <span className="inputLabel">NET単価</span>
                    <p className="cancelPrice">
                        {subjectInfo?.ad_net ? (
                            <>￥{subjectInfo.ad_net.toLocaleString()}</>
                        ) : (
                            "-"
                        )}
                    </p>
                </li>
            </ul>
            <hr className="sepBorder" />
            <h3 className="subjectTit">CH(子供料金)</h3>
            <ul className="sideList">
                <li className="wd20">
                    <span className="inputLabel">キャンセル料金</span>
                    <OnlyNumberInput
                        value={priceSetting.ch_cancel_charge ?? 0}
                        handleChange={e =>
                            handleChange("ch_cancel_charge", e.target.value)
                        }
                    />
                </li>
                <li className="wd20">
                    <span className="inputLabel">仕入先支払料金</span>
                    <OnlyNumberInput
                        value={priceSetting.ch_cancel_charge_net ?? 0}
                        handleChange={e =>
                            handleChange("ch_cancel_charge_net", e.target.value)
                        }
                    />
                </li>
                <li className="wd20">
                    <span className="inputLabel">粗利</span>
                    <OnlyNumberInput
                        value={priceSetting.ch_cancel_charge_profit ?? 0}
                        handleChange={e =>
                            handleChange(
                                "ch_cancel_charge_profit",
                                e.target.value
                            )
                        }
                    />
                </li>
                <li className="wd20">
                    <span className="inputLabel">仕入額</span>
                    <p className="cancelPrice">
                        {subjectInfo?.ch_cost ? (
                            <>￥{subjectInfo.ch_cost.toLocaleString()}</>
                        ) : (
                            "-"
                        )}
                    </p>
                </li>
                <li className="wd10">
                    <span className="inputLabel">手数料率</span>
                    <p className="cancelPrice">
                        {subjectInfo?.ch_commission_rate ? (
                            <>
                                {subjectInfo.ch_commission_rate.toLocaleString()}
                            </>
                        ) : (
                            "-"
                        )}
                        %
                    </p>
                </li>
                <li className="wd20">
                    <span className="inputLabel">NET単価</span>
                    <p className="cancelPrice">
                        {subjectInfo?.ch_net ? (
                            <>￥{subjectInfo.ch_net.toLocaleString()}</>
                        ) : (
                            "-"
                        )}
                    </p>
                </li>
            </ul>
            <hr className="sepBorder" />
            <h3 className="subjectTit">INF(幼児料金)</h3>
            <ul className="sideList">
                <li className="wd20">
                    <span className="inputLabel">キャンセル料金</span>
                    <OnlyNumberInput
                        value={priceSetting.inf_cancel_charge ?? 0}
                        handleChange={e =>
                            handleChange("inf_cancel_charge", e.target.value)
                        }
                    />
                </li>
                <li className="wd20">
                    <span className="inputLabel">仕入先支払料金</span>
                    <OnlyNumberInput
                        value={priceSetting.inf_cancel_charge_net ?? 0}
                        handleChange={e =>
                            handleChange(
                                "inf_cancel_charge_net",
                                e.target.value
                            )
                        }
                    />
                </li>
                <li className="wd20">
                    <span className="inputLabel">粗利</span>
                    <OnlyNumberInput
                        value={priceSetting.inf_cancel_charge_profit ?? 0}
                        handleChange={e =>
                            handleChange(
                                "inf_cancel_charge_profit",
                                e.target.value
                            )
                        }
                    />
                </li>
                <li className="wd20">
                    <span className="inputLabel">仕入額</span>
                    <p className="cancelPrice">
                        {subjectInfo?.inf_cost ? (
                            <>￥{subjectInfo.inf_cost.toLocaleString()}</>
                        ) : (
                            "-"
                        )}
                    </p>
                </li>
                <li className="wd10">
                    <span className="inputLabel">手数料率</span>
                    <p className="cancelPrice">
                        {subjectInfo?.inf_commission_rate ? (
                            <>
                                {subjectInfo.inf_commission_rate.toLocaleString()}
                            </>
                        ) : (
                            "-"
                        )}
                        %
                    </p>
                </li>
                <li className="wd20">
                    <span className="inputLabel">NET単価</span>
                    <p className="cancelPrice">
                        {subjectInfo?.inf_net ? (
                            <>￥{subjectInfo.inf_net.toLocaleString()}</>
                        ) : (
                            "-"
                        )}
                    </p>
                </li>
            </ul>
        </>
    );
};

export default Price;
