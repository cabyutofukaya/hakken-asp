import React, { useContext } from "react";
import { ConstContext } from "./ConstApp";
import classNames from "classnames";
// flatpickr
import "flatpickr/dist/themes/airbnb.css";
import { Japanese } from "flatpickr/dist/l10n/ja.js";
import Flatpickr from "react-flatpickr";

const CustomField = ({
    customCategoryCode,
    type,
    inputType,
    label,
    name,
    value = "",
    handleChange,
    list,
    uneditItem,
    liClass = "",
    disabled = false
} = {}) => {
    const {
        agencyAccount,
        customFieldTypes: fieldTypes,
        customFieldInputTypes: inputTypes
    } = useContext(ConstContext);

    if (type === fieldTypes?.text && inputType === inputTypes?.oneline) {
        return (
            <li className={liClass}>
                <span className="inputLabel">
                    {label}
                    {!uneditItem && (
                        <a
                            href={`/${agencyAccount}/system/custom/?tab=${customCategoryCode}`}
                        >
                            <span className="material-icons">settings</span>
                        </a>
                    )}
                </span>
                <input
                    type="text"
                    name={name}
                    value={value ?? ""}
                    onChange={handleChange}
                    maxLength={100}
                    disabled={disabled}
                />
            </li>
        );
    }
    if (type === fieldTypes?.text && inputType === inputTypes?.multiple) {
        return (
            <li className={liClass}>
                <span className="inputLabel">
                    {label}
                    {!uneditItem && (
                        <a
                            href={`/${agencyAccount}/system/custom/?tab=${customCategoryCode}`}
                        >
                            <span className="material-icons">settings</span>
                        </a>
                    )}
                </span>
                <textarea
                    type="text"
                    name={name}
                    value={value ?? ""}
                    onChange={handleChange}
                    disabled={disabled}
                />
            </li>
        );
    }
    if (type === fieldTypes?.list) {
        return (
            <li className={liClass}>
                <span className="inputLabel">
                    {label}
                    {!uneditItem && (
                        <a
                            href={`/${agencyAccount}/system/custom/?tab=${customCategoryCode}`}
                        >
                            <span className="material-icons">settings</span>
                        </a>
                    )}
                </span>
                <div
                    className={classNames("selectBox", {
                        disabled: disabled
                    })}
                >
                    <select
                        name={name}
                        value={value ?? ""}
                        onChange={handleChange}
                        disabled={disabled}
                    >
                        {/** selectメニュー先頭に「---」を追加し値を空に設定する。値がない場合や未選択に使用する項目とする */}
                        {list &&
                            ["---", ...list].map((val, index) => (
                                <option
                                    key={index}
                                    value={index === 0 ? "" : val}
                                >
                                    {val}
                                </option>
                            ))}
                    </select>
                </div>
            </li>
        );
    }

    if (type === fieldTypes?.date && inputType === inputTypes?.calendar) {
        return (
            <li className={liClass}>
                <span className="inputLabel">
                    {label}
                    {!uneditItem && (
                        <a
                            href={`/${agencyAccount}/system/custom/?tab=${customCategoryCode}`}
                        >
                            <span className="material-icons">settings</span>
                        </a>
                    )}
                </span>
                <div className="calendar">
                    <Flatpickr
                        theme="airbnb"
                        value={value ?? ""}
                        onChange={date => {
                            handleChange({
                                target: {
                                    name: name,
                                    value: date
                                }
                            });
                        }}
                        options={{
                            dateFormat: "Y/m/d",
                            locale: {
                                ...Japanese
                            }
                        }}
                        render={({ defaultValue, value, ...props }, ref) => {
                            return (
                                <input
                                    name={name}
                                    defaultValue={value ?? ""}
                                    ref={ref}
                                    disabled={disabled}
                                />
                            );
                        }}
                    />
                </div>
            </li>
        );
    }

    if (type === fieldTypes?.date && inputType === inputTypes?.time) {
        return (
            <li className={liClass}>
                <span className="inputLabel">
                    {label}
                    {!uneditItem && (
                        <a
                            href={`/${agencyAccount}/system/custom/?tab=${customCategoryCode}`}
                        >
                            <span className="material-icons">settings</span>
                        </a>
                    )}
                </span>
                <input
                    type="text"
                    name={name}
                    value={value ?? ""}
                    onChange={handleChange}
                    maxLength={100}
                    disabled={disabled}
                />
            </li>
        );
    }
    return null;
};

export default CustomField;
