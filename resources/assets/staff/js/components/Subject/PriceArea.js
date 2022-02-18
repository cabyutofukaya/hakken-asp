import React, { useState } from "react";
import OnlyNumberInput from "../OnlyNumberInput";
import { calcTaxInclud, calcNet, calcGrossProfit } from "../../libs";

// 料金計算エリア
const PriceArea = ({ defaultValue, zeiKbns, defaultZeiKbn }) => {
    const [input, setInput] = useState({
        ad_gross_ex: defaultValue?.ad_gross_ex ?? 0,
        ad_zei_kbn: defaultValue?.ad_zei_kbn ?? defaultZeiKbn,
        ad_gross: defaultValue?.ad_gross ?? 0,
        ad_cost: defaultValue?.ad_cost ?? 0,
        ad_commission_rate: defaultValue?.ad_commission_rate ?? 0,
        ad_net: defaultValue?.ad_net ?? 0,
        ad_gross_profit: defaultValue?.ad_gross_profit ?? 0,
        ch_gross_ex: defaultValue?.ch_gross_ex ?? 0,
        ch_zei_kbn: defaultValue?.ch_zei_kbn ?? defaultZeiKbn,
        ch_gross: defaultValue?.ch_gross ?? 0,
        ch_cost: defaultValue?.ch_cost ?? 0,
        ch_commission_rate: defaultValue?.ch_commission_rate ?? 0,
        ch_net: defaultValue?.ch_net ?? 0,
        ch_gross_profit: defaultValue?.ch_gross_profit ?? 0,
        inf_gross_ex: defaultValue?.inf_gross_ex ?? 0,
        inf_zei_kbn: defaultValue?.inf_zei_kbn ?? defaultZeiKbn,
        inf_gross: defaultValue?.inf_gross ?? 0,
        inf_cost: defaultValue?.inf_cost ?? 0,
        inf_commission_rate: defaultValue?.inf_commission_rate ?? 0,
        inf_net: defaultValue?.inf_net ?? 0,
        inf_gross_profit: defaultValue?.inf_gross_profit ?? 0
    });

    const handleInputChange = e => {
        const [kbn] = e.target.name.split("_");

        input[e.target.name] = e.target.value;

        if (/gross_ex$/.test(e.target.name) || /zei_kbn$/.test(e.target.name)) {
            // 税金
            input[`${kbn}_gross`] = calcTaxInclud(
                input[`${kbn}_gross_ex`],
                input[`${kbn}_zei_kbn`]
            );
            // 粗利
            input[`${kbn}_gross_profit`] = calcGrossProfit(
                input[`${kbn}_gross`],
                input[`${kbn}_net`]
            );
            setInput({
                ...input
            });
        } else if (
            /cost$/.test(e.target.name) ||
            /commission_rate$/.test(e.target.name)
        ) {
            // NET単価
            input[`${kbn}_net`] = calcNet(
                input[`${kbn}_cost`],
                input[`${kbn}_commission_rate`]
            );
            // 粗利
            input[`${kbn}_gross_profit`] = calcGrossProfit(
                input[`${kbn}_gross`],
                input[`${kbn}_net`]
            );
            setInput({
                ...input
            });
        } else if (/gross$/.test(e.target.name) || /net$/.test(e.target.name)) {
            // 粗利
            input[`${kbn}_gross_profit`] = calcGrossProfit(
                input[`${kbn}_gross`],
                input[`${kbn}_net`]
            );
            setInput({
                ...input
            });
        } else {
            setInput({ ...input });
        }
    };

    return (
        <>
            <h3 className="subjectTit">AD(大人料金)</h3>
            <ul className="sideList">
                <li className="wd20">
                    <span className="inputLabel">税抜単価</span>
                    <OnlyNumberInput
                        name="ad_gross_ex"
                        value={input.ad_gross_ex}
                        handleChange={handleInputChange}
                    />
                </li>
                <li className="wd20">
                    <span className="inputLabel">税区分</span>
                    <div className="selectBox">
                        <select
                            name="ad_zei_kbn"
                            value={input.ad_zei_kbn}
                            onChange={handleInputChange}
                        >
                            {Object.keys(zeiKbns).map((k, index) => (
                                <option key={index} value={k}>
                                    {zeiKbns[k]}
                                </option>
                            ))}
                        </select>
                    </div>
                </li>
                <li className="wd20">
                    <span className="inputLabel">税込GROSS単価</span>
                    <OnlyNumberInput
                        name="ad_gross"
                        value={input.ad_gross}
                        handleChange={handleInputChange}
                    />
                </li>
                <li className="wd20">
                    <span className="inputLabel">仕入れ額</span>
                    <OnlyNumberInput
                        name="ad_cost"
                        value={input.ad_cost}
                        handleChange={handleInputChange}
                    />
                </li>
                <li className="wd10">
                    <span className="inputLabel">手数料率</span>
                    <div className="priceInput per">
                        <OnlyNumberInput
                            name="ad_commission_rate"
                            value={input.ad_commission_rate}
                            handleChange={handleInputChange}
                        />
                    </div>
                </li>
                <li className="wd20">
                    <span className="inputLabel">NET単価</span>
                    <OnlyNumberInput
                        name="ad_net"
                        value={input.ad_net}
                        handleChange={handleInputChange}
                    />
                </li>
                <li className="wd20">
                    <span className="inputLabel">粗利</span>
                    <OnlyNumberInput
                        name="ad_gross_profit"
                        value={input.ad_gross_profit}
                        handleChange={handleInputChange}
                    />
                </li>
            </ul>
            <hr className="sepBorder" />
            <h3 className="subjectTit">CH(子供料金)</h3>
            <ul className="sideList">
                <li className="wd20">
                    <span className="inputLabel">税抜単価</span>
                    <OnlyNumberInput
                        name="ch_gross_ex"
                        value={input.ch_gross_ex}
                        handleChange={handleInputChange}
                    />
                </li>
                <li className="wd20">
                    <span className="inputLabel">税区分</span>
                    <div className="selectBox">
                        <select
                            name="ch_zei_kbn"
                            value={input.ch_zei_kbn}
                            onChange={handleInputChange}
                        >
                            {Object.keys(zeiKbns).map((k, index) => (
                                <option key={index} value={k}>
                                    {zeiKbns[k]}
                                </option>
                            ))}
                        </select>
                    </div>
                </li>
                <li className="wd20">
                    <span className="inputLabel">税込GROSS単価</span>
                    <OnlyNumberInput
                        name="ch_gross"
                        value={input.ch_gross}
                        handleChange={handleInputChange}
                    />
                </li>
                <li className="wd20">
                    <span className="inputLabel">仕入れ額</span>
                    <OnlyNumberInput
                        name="ch_cost"
                        value={input.ch_cost}
                        handleChange={handleInputChange}
                    />
                </li>
                <li className="wd10">
                    <span className="inputLabel">手数料率</span>
                    <div className="priceInput per">
                        <OnlyNumberInput
                            name="ch_commission_rate"
                            value={input.ch_commission_rate}
                            handleChange={handleInputChange}
                        />
                    </div>
                </li>
                <li className="wd20">
                    <span className="inputLabel">NET単価</span>
                    <OnlyNumberInput
                        name="ch_net"
                        value={input.ch_net}
                        handleChange={handleInputChange}
                    />
                </li>
                <li className="wd20">
                    <span className="inputLabel">粗利</span>
                    <OnlyNumberInput
                        name="ch_gross_profit"
                        value={input.ch_gross_profit}
                        handleChange={handleInputChange}
                    />
                </li>
            </ul>
            <hr className="sepBorder" />
            <h3 className="subjectTit">INF(幼児料金)</h3>
            <ul className="sideList">
                <li className="wd20">
                    <span className="inputLabel">税抜単価</span>
                    <OnlyNumberInput
                        name="inf_gross_ex"
                        value={input.inf_gross_ex}
                        handleChange={handleInputChange}
                    />
                </li>
                <li className="wd20">
                    <span className="inputLabel">税区分</span>
                    <div className="selectBox">
                        <select
                            name="inf_zei_kbn"
                            value={input.inf_zei_kbn}
                            onChange={handleInputChange}
                        >
                            {Object.keys(zeiKbns).map((k, index) => (
                                <option key={index} value={k}>
                                    {zeiKbns[k]}
                                </option>
                            ))}
                        </select>
                    </div>
                </li>
                <li className="wd20">
                    <span className="inputLabel">税込GROSS単価</span>
                    <OnlyNumberInput
                        name="inf_gross"
                        value={input.inf_gross}
                        handleChange={handleInputChange}
                    />
                </li>
                <li className="wd20">
                    <span className="inputLabel">仕入れ額</span>
                    <OnlyNumberInput
                        name="inf_cost"
                        value={input.inf_cost}
                        handleChange={handleInputChange}
                    />
                </li>
                <li className="wd10">
                    <span className="inputLabel">手数料率</span>
                    <div className="priceInput per">
                        <OnlyNumberInput
                            name="inf_commission_rate"
                            value={input.inf_commission_rate}
                            handleChange={handleInputChange}
                        />
                    </div>
                </li>
                <li className="wd20">
                    <span className="inputLabel">NET単価</span>
                    <OnlyNumberInput
                        name="inf_net"
                        value={input.inf_net}
                        handleChange={handleInputChange}
                    />
                </li>
                <li className="wd20">
                    <span className="inputLabel">粗利</span>
                    <OnlyNumberInput
                        name="inf_gross_profit"
                        value={input.inf_gross_profit}
                        handleChange={handleInputChange}
                    />
                </li>
            </ul>
        </>
    );
};

export default PriceArea;
