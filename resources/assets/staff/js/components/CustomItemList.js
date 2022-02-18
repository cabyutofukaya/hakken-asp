import _ from "lodash";
import React, { useState } from "react";

/**
 * カスタム項目（リストタイプ）のリスト部分を
 * 制御するコンポーネント
 *
 * @param {*} protectList 削除不可リスト
 * @returns
 */
const CustomItemList = ({ defaultList, protectList }) => {
    const [list, setList] = useState(defaultList);

    // リスト項目追加
    const handleListAdd = e => {
        setList([...list, ""]);
    };

    // リスト項目削除
    const handleDeleteRow = index => {
        setList(list.filter((v, i) => index != i));
    };

    // リスト項目名変更
    const handleRowChange = (e, index) => {
        list[index] = e.target.value;
        setList([...list]);
    };

    // 並び替え（up）
    const handleSortUp = index => {
        if (index <= 0) return;
        const arr = [...list];
        [arr[index], arr[index - 1]] = [list[index - 1], list[index]];
        setList(arr);
    };

    // 並び替え（down）
    const handleSortDown = index => {
        if (index >= list.length - 1) return;
        const arr = [...list];
        [arr[index], arr[index + 1]] = [list[index + 1], list[index]];
        setList(arr);
    };
    return (
        <>
            <table>
                <thead>
                    <tr>
                        <th className="wd80">項目名</th>
                        <th className="wd10 txtalc">並び替え</th>
                        <th className="wd10">削除</th>
                    </tr>
                </thead>
                <tbody>
                    {list &&
                        list.map((val, index) => (
                            <tr key={index}>
                                <td>
                                    {_.indexOf(protectList, val) !== -1 && (
                                        <>
                                            {val}
                                            <input
                                                type="hidden"
                                                name="list[]"
                                                value={val}
                                            />
                                        </>
                                    )}
                                    {_.indexOf(protectList, val) == -1 && (
                                        <input
                                            type="text"
                                            name="list[]"
                                            value={val}
                                            onChange={e =>
                                                handleRowChange(e, index)
                                            }
                                        />
                                    )}
                                </td>
                                <td>
                                    <ul>
                                        <li
                                            className="material-icons"
                                            onClick={() => handleSortUp(index)}
                                        >
                                            expand_less
                                        </li>
                                        <li
                                            className="material-icons"
                                            onClick={() =>
                                                handleSortDown(index)
                                            }
                                        >
                                            expand_more
                                        </li>
                                    </ul>
                                </td>
                                <td>
                                    {_.indexOf(protectList, val) !== -1 && (
                                        <>-</>
                                    )}
                                    {_.indexOf(protectList, val) == -1 && (
                                        <span
                                            className="material-icons"
                                            onClick={() =>
                                                handleDeleteRow(index)
                                            }
                                        >
                                            delete
                                        </span>
                                    )}
                                </td>
                            </tr>
                        ))}
                </tbody>
            </table>

            <p className="addList" onClick={handleListAdd}>
                <span className="material-icons">add_circle</span>
                新しい値を追加
            </p>
        </>
    );
};

export default CustomItemList;
