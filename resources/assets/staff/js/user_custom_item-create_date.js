import React, { useState } from "react";
import { render } from "react-dom";

/**
 * 日時項目追加
 *
 * @param {*} param0
 * @returns
 */
const InputArea = ({
    errors,
    defaultUserCustomCategoryId,
    defaultValue,
    formSelects
}) => {
    const [userCustomCategoryId, setUserCustomCategoryId] = useState(
        defaultValue?.user_custom_category_id
            ? defaultValue?.user_custom_category_id
            : defaultUserCustomCategoryId
    );
    const [displayPositions, setDisplayPositions] = useState(
        formSelects?.positions?.[userCustomCategoryId]
    );

    // 入力タイプ
    const [inputType, setInputTpye] = useState(
        defaultValue?.input_type ? defaultValue.input_type : "calendar"
    );

    const handleCategoryChange = e => {
        setUserCustomCategoryId(e.target.value);

        // 設置対応箇所を取得
        setDisplayPositions(formSelects?.positions?.[e.target.value]);
    };

    return (
        <>
            {!Array.isArray(errors) && errors && (
                <div id="errorMessage">
                    <p>
                        {Object.keys(errors).map(key => (
                            <span key={key}>
                                {errors[key]}
                                <br />
                            </span>
                        ))}
                    </p>
                </div>
            )}
            <ul className="baseList">
                <li className="wd40">
                    <span className="inputLabel">カテゴリ</span>
                    <div className="selectBox">
                        <select
                            name="user_custom_category_id"
                            value={userCustomCategoryId}
                            onChange={handleCategoryChange}
                        >
                            {formSelects?.userCustomCategories &&
                                Object.keys(
                                    formSelects.userCustomCategories
                                ).map(id => (
                                    <option key={id} value={id}>
                                        {formSelects.userCustomCategories[id]}
                                    </option>
                                ))}
                        </select>
                    </div>
                </li>
            </ul>

            <ul className="sideList">
                <li className="wd60">
                    <span className="inputLabel">項目名</span>
                    <input type="text" name="name" />
                </li>
                {displayPositions && (
                    <li className="wd40 mr00">
                        <span className="inputLabel">
                            {
                                formSelects?.positionLabels?.[
                                    userCustomCategoryId
                                ]
                            }
                        </span>
                        <div className="selectBox">
                            <select name="display_position">
                                {Object.keys(displayPositions).map(val => (
                                    <option key={val} value={val}>
                                        {displayPositions[val]}
                                    </option>
                                ))}
                            </select>
                        </div>
                    </li>
                )}
            </ul>
            <hr className="sepBorder" />
            <ul className="baseList">
                <li>
                    <span className="inputLabel">入力形式</span>
                    <ul className="baseRadio sideList mt10">
                        {formSelects?.inputTypes &&
                            Object.keys(formSelects.inputTypes).map(v => (
                                <li key={v}>
                                    <input
                                        type="radio"
                                        id={`input_type_${v}`}
                                        name="input_type"
                                        value={v}
                                        onChange={() => setInputTpye(v)}
                                        checked={inputType === v}
                                    />
                                    <label htmlFor={`input_type_${v}`}>
                                        {formSelects.inputTypes?.[v]}
                                    </label>
                                </li>
                            ))}
                    </ul>
                </li>
            </ul>
        </>
    );
};

const Element = document.getElementById("inputArea");
if (Element) {
    const errors = Element.getAttribute("errors");
    const parsedErrors = errors && JSON.parse(errors);
    const defaultValue = Element.getAttribute("defaultValue");
    const parsedDefaultValue = defaultValue && JSON.parse(defaultValue);
    const defaultUserCustomCategoryId = Element.getAttribute(
        "defaultUserCustomCategoryId"
    );
    const formSelects = Element.getAttribute("formSelects");
    const parsedFormSelects = formSelects && JSON.parse(formSelects);
    render(
        <InputArea
            defaultUserCustomCategoryId={defaultUserCustomCategoryId}
            errors={parsedErrors}
            defaultValue={parsedDefaultValue}
            formSelects={parsedFormSelects}
        />,
        document.getElementById("inputArea")
    );
}
