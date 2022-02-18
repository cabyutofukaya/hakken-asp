import React from "react";

// 申込者入力欄（法人）
const BusinessDocumentAddressSettingArea = ({
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
                <span className="inputLabel">法人名</span>
                <input
                    type="text"
                    name="company_name"
                    value={documentAddress?.company_name ?? ""}
                    onChange={onChange}
                />
            </li>
            <li>
                <span className="inputLabel">部署</span>
                <input
                    type="text"
                    name="department_name"
                    value={documentAddress?.department_name ?? ""}
                    onChange={onChange}
                />
            </li>
            <li>
                <span className="inputLabel">担当者名</span>
                <div className="inputSelectSet">
                    <div className="wd70">
                        <input
                            type="text"
                            name="name"
                            value={documentAddress?.name ?? ""}
                            onChange={onChange}
                            maxLength={50}
                        />
                    </div>
                    <div className="selectBox wd30">
                        <select
                            name="honorific"
                            value={documentAddress?.honorific}
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

export default BusinessDocumentAddressSettingArea;
