import React, { useCallback } from "react";

/**
 * 数字のみ入力可のinput
 *
 * @param {boolean} negativeValuePermit 負数の入力を許可する場合はtrue
 * @returns
 */
const OnlyNumberInput = ({
    name,
    value,
    handleChange,
    handleFocus = e => {},
    handleBlur = e => {},
    negativeValuePermit = true,
    maxLength = 10,
    placeholder = "",
    className = "",
    readOnly = false
} = {}) => {
    const changeValue = e => {
        e.target.value = e.target.value.replace(/[０-９]/g, function(s) {
            return String.fromCharCode(s.charCodeAt(0) - 0xfee0);
        }); // 全角数字→半角変換

        if (negativeValuePermit) {
            // 負数の入力OK
            if (
                e.target.value === "" ||
                e.target.value === "-" ||
                /[0-9]+$/.test(e.target.value)
            ) {
                handleChange(e);
            }
        } else {
            // 正数のみ
            if (e.target.value === "" || /[0-9]+$/.test(e.target.value)) {
                handleChange(e);
            }
        }
    };

    // フォーカス時に全選択状態に
    const focusFunc = useCallback(e => {
        e.target.select();
        handleFocus(e);
    }, []);

    // フォーカス外れた時
    const blurFunc = useCallback(e => {
        handleBlur(e);
    });

    return (
        <input
            type="text"
            name={name}
            value={value}
            onChange={changeValue}
            maxLength={maxLength}
            className={className}
            placeholder={placeholder}
            onFocus={focusFunc}
            onBlur={blurFunc}
            readOnly={readOnly}
        />
    );
};

export default OnlyNumberInput;
