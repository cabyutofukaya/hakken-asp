import React from "react";

/**
 * 検印欄設定
 *
 * @param {*} param0
 * @returns
 */
const SealSettingArea = ({
    sealNumber,
    sealMaxNum,
    sealItems,
    handleSelectChange,
    handleInputChange,
    disabled
}) => {
    return (
        <>
            <li className="wd40">
                <span className="inputLabel">検印欄表示数</span>
                <div className="selectBox">
                    <select
                        name="seal_number"
                        value={sealNumber}
                        onChange={handleSelectChange}
                        disabled={disabled}
                    >
                        {/** sealMaxNum - 0 は「文字列→数字」変換 */}
                        {[...Array(sealMaxNum - 0 + 1).keys()].map((n, i) => (
                            <option value={n} key={i}>
                                {n}
                            </option>
                        ))}
                    </select>
                </div>
            </li>
            {/** sealNumber - 0 は「文字列→数字」変換 */}
            {sealNumber > 0 &&
                [...Array(sealNumber - 0).keys()].map((n, i) => (
                    <li key={i}>
                        <span className="inputLabel">
                            検印欄項目{n + 1}の名称
                        </span>
                        <input
                            type="text"
                            value={sealItems?.[i] ?? ""}
                            onChange={e =>
                                handleInputChange({
                                    index: n,
                                    value: e.target.value
                                })
                            }
                            disabled={disabled}
                        />
                    </li>
                ))}
        </>
    );
};

export default SealSettingArea;
