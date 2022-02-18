import React, { useCallback } from "react";

// 数字のみ入力可のinput
const OnlyNumberInput = ({
    name,
    value,
    handleChange,
    maxLength = 10,
    placeholder = "",
    className = ""
} = {}) => {
    const changeValue = e => {
        e.target.value = e.target.value.replace(/[０-９]/g, function(s) {
            return String.fromCharCode(s.charCodeAt(0) - 0xfee0);
        }); // 全角数字→半角変換
        if (
            e.target.value === "" ||
            e.target.value === "-" ||
            /[0-9]+$/.test(e.target.value)
        ) {
            handleChange(e);
        }
    };

    // フォーカス時に全選択状態に
    const handleForcus = useCallback(e => {
        e.target.select();
    }, []);

    return (
        <input
            type="text"
            name={name}
            value={value}
            onChange={changeValue}
            maxLength={maxLength}
            className={className}
            placeholder={placeholder}
            onFocus={handleForcus}
        />
    );
};

export default OnlyNumberInput;
