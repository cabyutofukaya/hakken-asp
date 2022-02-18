import React, { useState } from "react";

const SealRow = ({ formSelects, defaultValue }) => {
    //初期値
    const initialValue = {
        seal: defaultValue?.seal ?? 0,
        seal_number: defaultValue?.seal_number ?? 0,
        seal_items: defaultValue?.seal_items ?? []
    };

    const [isChecked, setIsChecked] = useState(initialValue.seal == 1); // 検印欄 表示On<->Off
    const [sealNumber, setSealNumber] = useState(initialValue.seal_number); // 表示数
    const [sealItems, setSealItems] = useState(initialValue.seal_items); // 項目

    return (
        <>
            <li className="wd100 mr00">
                {/* sealのcheckboxが未選択の場合の初期値 */}
                <input type="hidden" name="seal" value="0" />
                {/* seal_itemsがoの場合の初期値 */}
                <input type="hidden" name="seal_items" />
                <h3>検印欄</h3>
                <ul className="sideList">
                    <li className="wd10">
                        <span className="inputLabel">検印欄</span>
                        <div className="checkBox mt15">
                            <input
                                type="checkbox"
                                id="seal"
                                name="seal"
                                value="1"
                                checked={isChecked}
                                onChange={e => {
                                    setIsChecked(!isChecked);
                                }}
                            />
                            <label htmlFor="seal">表示</label>
                        </div>
                    </li>
                    <li className="wd10">
                        <span className="inputLabel">表示数</span>
                        <div className="selectBox">
                            <select
                                name="seal_number"
                                value={sealNumber}
                                onChange={e => setSealNumber(e.target.value)}
                            >
                                {formSelects?.sealNumbers &&
                                    formSelects.sealNumbers.map(n => (
                                        <option key={n} value={n}>
                                            {n}
                                        </option>
                                    ))}
                            </select>
                        </div>
                    </li>

                    {[...Array(parseInt(sealNumber)).keys()].map(i => (
                        <li
                            className={
                                "wd20 " +
                                (parseInt(sealNumber) === i ? "mr00" : "")
                            }
                            key={i}
                        >
                            <span className="inputLabel">
                                項目{i + 1}の名称
                            </span>
                            <input
                                type="text"
                                name="seal_items[]"
                                value={sealItems?.[i] ?? ""}
                                onChange={e =>
                                    setSealItems({
                                        ...sealItems,
                                        [i]: e.target.value
                                    })
                                }
                            />
                        </li>
                    ))}
                </ul>
            </li>
        </>
    );
};

export default SealRow;
