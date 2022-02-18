import React, { useContext } from "react";
import { ConstContext } from "./ConstApp";
// flatpickr
import "flatpickr/dist/themes/airbnb.css";
import { Japanese } from "flatpickr/dist/l10n/ja.js";
import Flatpickr from "react-flatpickr";

/**
 * カスタム項目の検索フォールド
 *
 * @returns
 */
const SearchOptionItem = ({
    agencyAccount,
    customCategoryId,
    item,
    input,
    customItemTypes,
    handleChange,
    className = ""
} = {}) => {
    const { customFieldInputTypes } = useContext(ConstContext);

    return (
        <li className={className}>
            <span className="inputLabel">
                {item?.name}
                <a
                    href={`/${agencyAccount}/system/custom?tab=${customCategoryId}`}
                >
                    <span className="material-icons">settings</span>
                </a>
            </span>
            {/**一行タイプ */}
            {item?.type === customItemTypes.custom_item_type_text &&
                item?.input_type === customFieldInputTypes.oneline && (
                    <input
                        type="text"
                        name={item?.key}
                        value={input?.[item?.key] ?? ""}
                        onChange={handleChange}
                    />
                )}
            {/**複数行タイプ。検索についてはinputフィールドで良いと思う */}
            {item?.type === customItemTypes.custom_item_type_text &&
                item?.input_type === customFieldInputTypes.multiple && (
                    <input
                        type="text"
                        name={item?.key}
                        value={input?.[item?.key] ?? ""}
                        onChange={handleChange}
                    />
                )}
            {/**リスト */}
            {item?.type === customItemTypes.custom_item_type_list && (
                <div className="selectBox">
                    <select
                        name={item?.key}
                        value={input?.[item?.key] ?? ""}
                        onChange={handleChange}
                    >
                        {item?.select_item &&
                            Object.keys(item.select_item).map((v, index) => (
                                <option value={v} key={index}>
                                    {item.select_item[v]}
                                </option>
                            ))}
                    </select>
                </div>
            )}
            {/**カレンダー */}
            {item?.type === customItemTypes.custom_item_type_date &&
                item?.input_type === customFieldInputTypes.calendar && (
                    <Flatpickr
                        theme="airbnb"
                        value={input?.[item?.key] ?? ""}
                        onChange={(selectedDates, dateStr, instance) => {
                            handleChange({
                                target: {
                                    name: item?.key,
                                    value: dateStr
                                }
                            });
                        }}
                        options={{
                            dateFormat: "Y/m/d",
                            locale: {
                                ...Japanese
                            }
                        }}
                    />
                )}
            {/**時間 */}
            {item?.type === customItemTypes.custom_item_type_date &&
                item?.input_type === customFieldInputTypes.time && (
                    <input
                        type="text"
                        name={item?.key}
                        value={input?.[item?.key] ?? ""}
                        onChange={handleChange}
                    />
                )}
        </li>
    );
};

export default SearchOptionItem;
