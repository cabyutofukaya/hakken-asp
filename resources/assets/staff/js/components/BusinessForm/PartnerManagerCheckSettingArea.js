import React from "react";
import { PARTNER_MANAGER } from "../../constants";

// 担当者表示・非表示のチェックボックス
const PartnerManagerCheckSettingArea = ({
    partnerManagers,
    checkIds,
    handleChange
}) => {
    return (
        <>
            <span className="inputLabel">御社担当</span>
            <ul className="checkList">
                {partnerManagers &&
                    Object.keys(partnerManagers).map((k, i) => (
                        <li className="checkBox" key={i}>
                            <input
                                type="checkbox"
                                id={`partner_manager${i}`}
                                value={partnerManagers[k]["id"]}
                                onChange={handleChange}
                                checked={checkIds.includes(
                                    partnerManagers[k]["id"]
                                )}
                            />
                            <label htmlFor={`partner_manager${i}`}>
                                {partnerManagers[k]["org_name"]}
                                {partnerManagers[k]?.["deleted_at"] &&
                                    PARTNER_MANAGER.DELETED_SUFFIX}
                            </label>
                        </li>
                    ))}
            </ul>
        </>
    );
};

export default PartnerManagerCheckSettingArea;
