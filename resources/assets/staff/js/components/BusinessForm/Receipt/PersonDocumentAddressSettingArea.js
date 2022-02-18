import React from "react";

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
                        className="wd70 mr10"
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
        </>
    );
};

export default PersonDocumentAddressSettingArea;
