import React from "react";
import classNames from "classnames";

// 項目の表示・非表示を設定するチェックボックス
const SettingCheckRow = ({
    category,
    setting,
    handleChange,
    row,
    index,
    prefix
}) => {
    if (!setting?.[category]) return null;
    return (
        <li
            className={classNames("checkBox", {
                ml30: row.parent
            })}
            key={index}
        >
            <input
                type="checkbox"
                name={`category[${row.val}]`}
                id={`${prefix}${index}`}
                value={row.val}
                onChange={e => {
                    let settingRow = setting[category];
                    if (row.parent) {
                        // 子要素。「親値_子値」という形式で選択値を管理
                        if (
                            settingRow.indexOf(`${row.parent}_${row.val}`) ===
                            -1
                        ) {
                            {
                                /**チェックON */
                            }
                            settingRow.push(`${row.parent}_${row.val}`);
                            if (settingRow.indexOf(row.parent) === -1) {
                                // 親要素もチェックon対象に
                                settingRow.push(row.parent);
                            }
                        } else {
                            const filtered = settingRow.filter(v => {
                                return v != `${row.parent}_${row.val}`;
                            });
                            setting[category] = filtered;
                        }
                    } else {
                        // 親要素
                        if (settingRow.indexOf(row.val) === -1) {
                            {
                                /**チェックON */
                            }
                            settingRow.push(row.val);
                        } else {
                            const filtered = settingRow.filter(v => {
                                return v.indexOf(row.val) !== 0; // 当該要素を親要素とする子要素も削除
                            });
                            setting[category] = filtered;
                        }
                    }
                    handleChange(setting);
                }}
                checked={
                    row.parent
                        ? setting[category].indexOf(
                              `${row.parent}_${row.val}`
                          ) !== -1
                        : setting[category].indexOf(row.val) !== -1
                }
            />
            <label htmlFor={`${prefix}${index}`}>{row.val}</label>
        </li>
    );
};

export default SettingCheckRow;
