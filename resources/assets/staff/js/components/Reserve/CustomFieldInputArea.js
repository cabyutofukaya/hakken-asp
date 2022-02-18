import React from "react";
import CustomField from "../CustomField";

const CustomFieldInputArea = ({
    input,
    handleChange,
    customFields,
    customFieldPositions,
    staffs,
    customCategoryCode
}) => {
    return (
        <div className="inputSubArea">
            <ul className="baseList">
                <li>
                    <span className="inputLabel">自社担当</span>
                    <div className="selectBox wd40">
                        <select
                            name="manager_id"
                            value={input?.manager_id}
                            onChange={handleChange}
                        >
                            {staffs &&
                                Object.keys(staffs)
                                    .sort()
                                    .map((k, index) => (
                                        <option key={k} value={k}>
                                            {staffs[k]}
                                        </option>
                                    ))}
                        </select>
                    </div>
                </li>
            </ul>
            <ul className="sideList half">
                {_.has(customFields, customFieldPositions.custom) &&
                    _.map(customFields[customFieldPositions.custom], function(
                        row
                    ) {
                        return (
                            <CustomField
                                key={row?.id}
                                customCategoryCode={customCategoryCode}
                                type={row?.type}
                                inputType={row?.input_type}
                                label={row?.name}
                                name={row?.key}
                                list={row?.list}
                                value={input?.[row?.key]}
                                handleChange={handleChange}
                                uneditItem={row?.unedit_item}
                            />
                        );
                    })}
            </ul>
        </div>
    );
};

export default CustomFieldInputArea;
