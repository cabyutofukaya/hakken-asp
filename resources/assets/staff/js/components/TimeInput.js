import React, { useCallback } from "react";

/**
 * 数字とコロンのみ入力可のinput
 * 時刻入力フィールド用
 *
 * @returns
 */
const TimeInput = ({
    name,
    value,
    handleChange,
    handleFocus = e => {},
    handleBlur = e => {},
    maxLength = 10,
    placeholder = "",
    className = "",
    disabled = false
} = {}) => {
    const changeValue = e => {
        e.target.value = e.target.value.replace(/[０-９：]/g, function(s) {
            return String.fromCharCode(s.charCodeAt(0) - 0xfee0);
        }); // 全角数字→半角変換

        // 数字とコロンが入力OK
        if (e.target.value === "" || /[0-9:]+$/.test(e.target.value)) {
            handleChange(e);
        }
    };

    // フォーカス時
    const focusFunc = useCallback(e => {
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
            disabled={disabled}
        />
    );
};

export default TimeInput;
