import React from "react";
import OnlyNumberInput from "../../OnlyNumberInput";

/**
 *
 * @param {*} participants 参加者情報
 * @returns
 */
const Price = ({ input, handleChange, zeiKbns }) => {
    return (
        <>
            <h3 className="subjectTit">AD(大人料金)</h3>
            <ul className="sideList">
                <li className="wd20">
                    <span className="inputLabel">税抜単価</span>
                    <OnlyNumberInput
                        value={input?.ad_gross_ex ?? 0}
                        handleChange={e =>
                            handleChange({
                                type: "CHANGE_PRICE",
                                payload: { ad_gross_ex: e.target.value }
                            })
                        }
                    />
                </li>
                <li className="wd20">
                    <span className="inputLabel">税区分</span>
                    <div className="selectBox">
                        <select
                            value={input?.ad_zei_kbn}
                            onChange={e =>
                                handleChange({
                                    type: "CHANGE_PRICE",
                                    payload: {
                                        ad_zei_kbn: e.target.value
                                    }
                                })
                            }
                        >
                            {zeiKbns &&
                                Object.keys(zeiKbns).map((val, index) => (
                                    <option key={index} value={val}>
                                        {zeiKbns[val]}
                                    </option>
                                ))}
                        </select>
                    </div>
                </li>
                <li className="wd20">
                    <span className="inputLabel">税込GROSS単価</span>
                    <OnlyNumberInput
                        value={input?.ad_gross ?? 0}
                        handleChange={e =>
                            handleChange({
                                type: "CHANGE_PRICE",
                                payload: { ad_gross: e.target.value }
                            })
                        }
                    />
                </li>
                <li className="wd20">
                    <span className="inputLabel">仕入れ額</span>
                    <OnlyNumberInput
                        value={input?.ad_cost ?? 0}
                        handleChange={e =>
                            handleChange({
                                type: "CHANGE_PRICE",
                                payload: { ad_cost: e.target.value }
                            })
                        }
                    />
                </li>
                <li className="wd10">
                    <span className="inputLabel">手数料率</span>
                    <div className="priceInput per">
                        <OnlyNumberInput
                            value={input?.ad_commission_rate ?? 0}
                            handleChange={e =>
                                handleChange({
                                    type: "CHANGE_PRICE",
                                    payload: {
                                        ad_commission_rate: e.target.value
                                    }
                                })
                            }
                        />
                    </div>
                </li>
                <li className="wd20">
                    <span className="inputLabel">NET単価</span>
                    <OnlyNumberInput
                        value={input?.ad_net ?? 0}
                        handleChange={e =>
                            handleChange({
                                type: "CHANGE_PRICE",
                                payload: {
                                    ad_net: e.target.value
                                }
                            })
                        }
                    />
                </li>
                <li className="wd20">
                    <span className="inputLabel">粗利</span>
                    <OnlyNumberInput
                        value={input?.ad_gross_profit ?? 0}
                        handleChange={e =>
                            handleChange({
                                type: "CHANGE_PRICE",
                                payload: { ad_gross_profit: e.target.value }
                            })
                        }
                    />
                </li>
            </ul>
            <hr className="sepBorder" />
            <h3 className="subjectTit">CH(子供料金)</h3>
            <ul className="sideList">
                <li className="wd20">
                    <span className="inputLabel">税抜単価</span>
                    <OnlyNumberInput
                        value={input?.ch_gross_ex ?? 0}
                        handleChange={e =>
                            handleChange({
                                type: "CHANGE_PRICE",
                                payload: { ch_gross_ex: e.target.value }
                            })
                        }
                    />
                </li>
                <li className="wd20">
                    <span className="inputLabel">税区分</span>
                    <div className="selectBox">
                        <select
                            value={input?.ch_zei_kbn}
                            onChange={e =>
                                handleChange({
                                    type: "CHANGE_PRICE",
                                    payload: {
                                        ch_zei_kbn: e.target.value
                                    }
                                })
                            }
                        >
                            {zeiKbns &&
                                Object.keys(zeiKbns).map((val, index) => (
                                    <option key={index} value={val}>
                                        {zeiKbns[val]}
                                    </option>
                                ))}
                        </select>
                    </div>
                </li>
                <li className="wd20">
                    <span className="inputLabel">税込GROSS単価</span>
                    <OnlyNumberInput
                        value={input?.ch_gross ?? 0}
                        handleChange={e =>
                            handleChange({
                                type: "CHANGE_PRICE",
                                payload: { ch_gross: e.target.value }
                            })
                        }
                    />
                </li>
                <li className="wd20">
                    <span className="inputLabel">仕入れ額</span>
                    <OnlyNumberInput
                        value={input?.ch_cost ?? 0}
                        handleChange={e =>
                            handleChange({
                                type: "CHANGE_PRICE",
                                payload: { ch_cost: e.target.value }
                            })
                        }
                    />
                </li>
                <li className="wd10">
                    <span className="inputLabel">手数料率</span>
                    <div className="priceInput per">
                        <OnlyNumberInput
                            value={input?.ch_commission_rate ?? 0}
                            handleChange={e =>
                                handleChange({
                                    type: "CHANGE_PRICE",
                                    payload: {
                                        ch_commission_rate: e.target.value
                                    }
                                })
                            }
                        />
                    </div>
                </li>
                <li className="wd20">
                    <span className="inputLabel">NET単価</span>
                    <OnlyNumberInput
                        value={input?.ch_net ?? 0}
                        handleChange={e =>
                            handleChange({
                                type: "CHANGE_PRICE",
                                payload: { ch_net: e.target.value }
                            })
                        }
                    />
                </li>
                <li className="wd20">
                    <span className="inputLabel">粗利</span>
                    <OnlyNumberInput
                        value={input?.ch_gross_profit ?? 0}
                        handleChange={e =>
                            handleChange({
                                type: "CHANGE_PRICE",
                                payload: { ch_gross_profit: e.target.value }
                            })
                        }
                    />
                </li>
            </ul>
            <hr className="sepBorder" />
            <h3 className="subjectTit">INF(幼児料金)</h3>
            <ul className="sideList">
                <li className="wd20">
                    <span className="inputLabel">税抜単価</span>
                    <OnlyNumberInput
                        value={input?.inf_gross_ex ?? 0}
                        handleChange={e =>
                            handleChange({
                                type: "CHANGE_PRICE",
                                payload: { inf_gross_ex: e.target.value }
                            })
                        }
                    />
                </li>
                <li className="wd20">
                    <span className="inputLabel">税区分</span>
                    <div className="selectBox">
                        <select
                            value={input?.inf_zei_kbn}
                            onChange={e =>
                                handleChange({
                                    type: "CHANGE_PRICE",
                                    payload: {
                                        inf_zei_kbn: e.target.value
                                    }
                                })
                            }
                        >
                            {zeiKbns &&
                                Object.keys(zeiKbns).map((val, index) => (
                                    <option key={index} value={val}>
                                        {zeiKbns[val]}
                                    </option>
                                ))}
                        </select>
                    </div>
                </li>
                <li className="wd20">
                    <span className="inputLabel">税込GROSS単価</span>
                    <OnlyNumberInput
                        value={input?.inf_gross ?? 0}
                        handleChange={e =>
                            handleChange({
                                type: "CHANGE_PRICE",
                                payload: { inf_gross: e.target.value }
                            })
                        }
                    />
                </li>
                <li className="wd20">
                    <span className="inputLabel">仕入れ額</span>
                    <OnlyNumberInput
                        value={input?.inf_cost ?? 0}
                        handleChange={e =>
                            handleChange({
                                type: "CHANGE_PRICE",
                                payload: { inf_cost: e.target.value }
                            })
                        }
                    />
                </li>
                <li className="wd10">
                    <span className="inputLabel">手数料率</span>
                    <div className="priceInput per">
                        <OnlyNumberInput
                            value={input?.inf_commission_rate ?? 0}
                            handleChange={e =>
                                handleChange({
                                    type: "CHANGE_PRICE",
                                    payload: {
                                        inf_commission_rate: e.target.value
                                    }
                                })
                            }
                        />
                    </div>
                </li>
                <li className="wd20">
                    <span className="inputLabel">NET単価</span>
                    <OnlyNumberInput
                        value={input?.inf_net ?? 0}
                        handleChange={e =>
                            handleChange({
                                type: "CHANGE_PRICE",
                                payload: {
                                    inf_net: e.target.value
                                }
                            })
                        }
                    />
                </li>
                <li className="wd20">
                    <span className="inputLabel">粗利</span>
                    <OnlyNumberInput
                        value={input?.inf_gross_profit ?? 0}
                        handleChange={e =>
                            handleChange({
                                type: "CHANGE_PRICE",
                                payload: { inf_gross_profit: e.target.value }
                            })
                        }
                    />
                </li>
            </ul>
        </>
    );
};

export default Price;
