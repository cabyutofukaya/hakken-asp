import React, { useState, useRef, useContext } from "react";
import { ConstContext } from "../ConstApp";

const SupplierAccountPayable = ({
    rowCount,
    defaultValue,
    bankAccountTypes,
    bankSelectItems
}) => {
    const { agencyAccount } = useContext(ConstContext);

    const [data, setData] = useState(defaultValue);
    const [kinyus, setKinyus] = useState(bankSelectItems?.kinyu_names ?? {});
    const [tenpos, setTenpos] = useState(bankSelectItems?.tenpo_names ?? {});

    const kinyuCodeInput = useRef(null); // 金融期間コード入力フィールド
    const tenpoCodeInput = useRef(null); // 店舗コード入力フィールド

    const handleChange = async e => {
        if (
            ["kinyu_code", "tenpo_code"].includes(e.target.name) &&
            e.target.value !== "" &&
            !/[0-9]+$/.test(e.target.value)
        ) {
            // 金融機関コードと店舗コードは数字のみ入力許可
            return;
        }

        let obj = {
            [e.target.name]: e.target.value
        };

        // 金融機関コードが4桁入力されたら金融機関名をAPI取得
        if (e.target.name === "kinyu_code") {
            if (e.target.value.length === 4) {
                kinyuCodeInput.current.readOnly = true; // 入力制御

                let response = await axios
                    .get(`/api/${agencyAccount}/bank/find/tenponame`, {
                        params: {
                            kinyu_code: e.target.value,
                            tenpo_code: data?.tenpo_code
                        }
                    })
                    .finally(() => {
                        kinyuCodeInput.current.readOnly = false; // 入力制御解除
                    });

                if (response?.data?.data) {
                    setKinyus({ ...response.data.data.kinyu_names });
                    setTenpos({ ...response.data.data.tenpo_names });

                    // obj.kinyu_name = response.data.data?.kinyu_name;
                    // obj.tenpo_name = response.data.data?.tenpo_name;
                }
            } else {
                setKinyus({});
                setTenpos({});
                // obj.kinyu_name = "";
                // obj.tenpo_name = "";
            }

            // 金融機関名、支店名の選択状態を初期化
            obj.kinyu_name = "";
            obj.tenpo_name = "";
        }

        // 店舗コードが3桁入力されたら支店名をAPI取得
        if (e.target.name === "tenpo_code") {
            if (e.target.value.length === 3) {
                tenpoCodeInput.current.readOnly = true; // 入力制御
                let response = await axios
                    .get(`/api/${agencyAccount}/bank/find/tenponame/`, {
                        params: {
                            kinyu_code: data?.kinyu_code,
                            tenpo_code: e.target.value
                        }
                    })
                    .finally(() => {
                        tenpoCodeInput.current.readOnly = false; // 入力制御解除
                    });
                if (response?.data?.data) {
                    setKinyus({ ...response.data.data.kinyu_names });
                    setTenpos({ ...response.data.data.tenpo_names });

                    // obj.kinyu_name = response.data.data?.kinyu_name;
                    // obj.tenpo_name = response.data.data?.tenpo_name;
                }
            } else {
                setTenpos({});
                // obj.tenpo_name = "";
            }

            // 支店名の選択状態を初期化
            obj.tenpo_name = "";
        }

        setData({ ...data, ...obj });
    };

    return (
        <>
            <input
                type="hidden"
                name={`supplier_account_payables[${rowCount}][id]`}
                value={data?.id}
            />
            <ul className="sideList half">
                <li>
                    <span className="inputLabel">振込先銀行コード(4桁)</span>
                    <div className="bankBlock">
                        <input
                            type="text"
                            maxLength="4"
                            ref={kinyuCodeInput}
                            name={`supplier_account_payables[${rowCount}][kinyu_code]`}
                            value={data?.kinyu_code}
                            onChange={e =>
                                handleChange({
                                    target: {
                                        name: "kinyu_code",
                                        value: e.target.value
                                    }
                                })
                            }
                        />
                        <div className="selectBox">
                            {/* 選択肢がない場合の初期値 */}
                            <input
                                type="hidden"
                                name={`supplier_account_payables[${rowCount}][kinyu_name]`}
                            />
                            <select
                                name={`supplier_account_payables[${rowCount}][kinyu_name]`}
                                value={data?.kinyu_name}
                                onChange={e =>
                                    handleChange({
                                        target: {
                                            name: "kinyu_name",
                                            value: e.target.value
                                        }
                                    })
                                }
                            >
                                {kinyus &&
                                    Object.keys(kinyus).map((k, index) => (
                                        <option value={k} key={index}>
                                            {kinyus[k]}
                                        </option>
                                    ))}
                            </select>
                        </div>
                    </div>
                </li>
                <li>
                    <span className="inputLabel">振込先支店コード(3桁)</span>
                    <div className="bankBlock">
                        <input
                            type="tel"
                            maxLength="3"
                            ref={tenpoCodeInput}
                            name={`supplier_account_payables[${rowCount}][tenpo_code]`}
                            value={data?.tenpo_code ?? ""}
                            onChange={e =>
                                handleChange({
                                    target: {
                                        name: "tenpo_code",
                                        value: e.target.value
                                    }
                                })
                            }
                        />
                        <div className="selectBox">
                            {/* 選択肢がない場合の初期値 */}
                            <input
                                type="hidden"
                                name={`supplier_account_payables[${rowCount}][tenpo_name]`}
                            />
                            <select
                                name={`supplier_account_payables[${rowCount}][tenpo_name]`}
                                value={data?.tenpo_name ?? ""}
                                onChange={e =>
                                    handleChange({
                                        target: {
                                            name: "tenpo_name",
                                            value: e.target.value
                                        }
                                    })
                                }
                            >
                                {tenpos &&
                                    Object.keys(tenpos).map((k, index) => (
                                        <option value={k} key={index}>
                                            {tenpos[k]}
                                        </option>
                                    ))}
                            </select>
                        </div>
                    </div>
                </li>
            </ul>
            <ul className="sideList half">
                <li>
                    <span className="inputLabel">口座番号</span>
                    <div className="selectSet">
                        <div className="selectBox wd30">
                            <select
                                name={`supplier_account_payables[${rowCount}][account_type]`}
                                value={data?.account_type ?? ""}
                                onChange={e =>
                                    handleChange({
                                        target: {
                                            name: "account_type",
                                            value: e.target.value
                                        }
                                    })
                                }
                            >
                                {bankAccountTypes &&
                                    Object.keys(bankAccountTypes).map(v => (
                                        <option key={v} value={v}>
                                            {bankAccountTypes[v]}
                                        </option>
                                    ))}
                            </select>
                        </div>
                        <input
                            type="text"
                            className="wd70"
                            name={`supplier_account_payables[${rowCount}][account_number]`}
                            maxLength={16}
                            value={data?.account_number ?? ""}
                            onChange={e =>
                                handleChange({
                                    target: {
                                        name: "account_number",
                                        value: e.target.value
                                    }
                                })
                            }
                        />
                    </div>
                </li>
                <li>
                    <span className="inputLabel">口座名</span>
                    <input
                        type="text"
                        name={`supplier_account_payables[${rowCount}][account_name]`}
                        maxLength="32"
                        value={data?.account_name ?? ""}
                        onChange={e =>
                            handleChange({
                                target: {
                                    name: "account_name",
                                    value: e.target.value
                                }
                            })
                        }
                    />
                </li>
            </ul>
            <hr className="sepBorder" />
        </>
    );
};

export default SupplierAccountPayable;
