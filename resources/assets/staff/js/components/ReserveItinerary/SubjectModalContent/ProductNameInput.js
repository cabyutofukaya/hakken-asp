import React, { useContext } from "react";
import { ConstContext } from "../../ConstApp";
import AsyncSelect from "react-select/async";

/**
 *
 * @param {*} readOnly 読み取り専用
 * @returns
 */
function ProductNameInput({
    handleChange,
    subject,
    value,
    name,
    defaultOptions,
    readOnly = false
}) {
    const { agencyAccount } = useContext(ConstContext);

    // const [selectedOption, setSelectedOption] = useState({});

    const onChange = val => {
        // setSelectedOption(val);
        const {
            // name,
            ad_gross_ex,
            ad_gross,
            ad_cost,
            ad_commission_rate,
            ad_net,
            ad_zei_kbn,
            ad_gross_profit,
            ch_gross_ex,
            ch_gross,
            ch_cost,
            ch_commission_rate,
            ch_net,
            ch_zei_kbn,
            ch_gross_profit,
            inf_gross_ex,
            inf_gross,
            inf_cost,
            inf_commission_rate,
            inf_net,
            inf_zei_kbn,
            inf_gross_profit,
            ...other
        } = val;

        /**
         * 各項目をサジェストで選んだ科目で初期化（料金以外）
         * 編集時に必要になるのでcode/name/id情報をjsonデータで保持。名前拡張パラメータとして
         */
        handleChange({
            type: "CHANGE_INPUT",
            payload: {
                ...other,
                ["name_ex"]: JSON.stringify({
                    code: other?.code ?? "",
                    name: other?.name ?? "",
                    id: other?.value ?? ""
                })
            }
        });

        // 各項目をサジェストで選んだ科目で初期化（料金関連）
        handleChange({
            type: "BULK_CHANGE_PRICE",
            payload: {
                ad_gross_ex,
                ad_gross,
                ad_cost,
                ad_commission_rate,
                ad_net,
                ad_zei_kbn,
                ad_gross_profit,
                ch_gross_ex,
                ch_gross,
                ch_cost,
                ch_commission_rate,
                ch_net,
                ch_zei_kbn,
                ch_gross_profit,
                inf_gross_ex,
                inf_gross,
                inf_cost,
                inf_commission_rate,
                inf_net,
                inf_zei_kbn,
                inf_gross_profit
            }
        });
    };

    const loadOptions = (inputValue, callback) => {
        if (!inputValue) return;

        // setTimeout(() => {
        axios
            .post(`/api/${agencyAccount}/subject/search`, {
                word: inputValue,
                subject_category: subject
            })
            .then(response => {
                callback(
                    response.data.data.map(({ id, code, name, ...other }) => {
                        return {
                            label: `${code} ${name}`,
                            value: id, // 科目マスターのID。valueとして渡す
                            code,
                            name,
                            ...other
                        };
                    })
                );
            });
        // }, 1000);
    };

    // 読み取り専用の場合は変更不可
    if (readOnly) {
        return (
            <input type="text" value={value?.label ?? ""} readOnly={readOnly} />
        );
    } else {
        return (
            <AsyncSelect
                cacheOptions
                name={name}
                value={value}
                onChange={onChange}
                defaultOptions={defaultOptions}
                loadOptions={loadOptions}
                placeholder=""
            />
        );
    }
}

export default ProductNameInput;
