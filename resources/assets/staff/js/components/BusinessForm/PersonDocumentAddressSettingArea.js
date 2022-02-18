import React from "react";
import OnlyNumberInput from "../OnlyNumberInput";

// 申込者入力欄（個人）
const PersonDocumentAddressSettingArea = ({
    documentAddress,
    honorifics,
    onChange
}) => {
    return (
        <>
            <li className="mt40">
                <h3>宛名(顧客情報)</h3>
            </li>
            <li>
                <span className="inputLabel">氏名</span>
                <div className="inputSelectSet">
                    <input
                        type="text"
                        name="name"
                        value={documentAddress.name ?? ""}
                        onChange={onChange}
                        className="wd70"
                    />
                    <div className="selectBox wd30">
                        <select
                            name="honorific"
                            value={documentAddress.honorific ?? ""}
                            onChange={onChange}
                        >
                            {Object.keys(honorifics).map((k, i) => (
                                <option key={i} value={k}>
                                    {honorifics[k]}
                                </option>
                            ))}
                        </select>
                    </div>
                </div>
            </li>
            <li>
                <span className="inputLabel">郵便番号</span>
                <OnlyNumberInput
                    name="zip_code"
                    value={documentAddress.zip_code ?? ""}
                    handleChange={onChange}
                    maxLength={7}
                />
            </li>
            <li>
                <span className="inputLabel">都道府県</span>
                <input
                    type="text"
                    name="prefecture"
                    value={documentAddress.prefecture ?? ""}
                    onChange={onChange}
                    maxLength={12}
                />
            </li>
            <li>
                <span className="inputLabel">住所</span>
                <input
                    type="text"
                    name="address1"
                    value={documentAddress.address1 ?? ""}
                    onChange={onChange}
                    maxLength={100}
                />
            </li>
            <li>
                <span className="inputLabel">ビル・建物名</span>
                <input
                    type="text"
                    name="address2"
                    value={documentAddress.address2 ?? ""}
                    onChange={onChange}
                    maxLength={100}
                />
            </li>
        </>
    );
};

export default PersonDocumentAddressSettingArea;
