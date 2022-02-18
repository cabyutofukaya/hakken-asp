import React, { useState } from "react";
import { render } from "react-dom";
import classNames from "classnames";
import useFetch from "../../hooks/useFetch";
import _ from "lodash";

/**
 * 日時項目追加
 *
 * @param {*} param0
 * @returns
 */
const InputArea = ({
    agencyAccount,
    defaultValue,
    formSelects,
    customCategoryCode,
    userCustomItemTypes
}) => {
    // ユーザー権限リスト
    const { response: agencyRoles, error: err1 } = useFetch(
        `/api/${agencyAccount}/agency-role/list`,
        "get"
    );
    // カスタム項目
    const { response: customItemDatas, error: err2 } = useFetch(
        `/api/${agencyAccount}/custom/list/category-code/${customCategoryCode}`,
        "get"
    );
    const [data, setData] = useState(defaultValue);

    const isLoading = !agencyRoles || !customItemDatas;

    const [accountCheckBtnDisabled, setAccountCheckBtnDisabled] = useState(
        false
    );

    const handleInputChange = e => {
        setData({ ...data, [e.target.name]: e.target.value });
    };

    // アカウントの重複状態。ng/ok/NULL
    const [accountDuplicateState, setAccountDuplicateState] = useState(null);
    // アカウント重複チェックclass
    const accountCheckClass = classNames("wd60", "check", {
        ok: accountDuplicateState === "ok",
        ng: accountDuplicateState === "ng"
    });

    // アカウント重複チェック
    const handleAccountDuplicateCheck = e => {
        e.preventDefault();

        if (!data?.account) {
            setAccountDuplicateState("ng");
            return;
        }
        setAccountDuplicateState(null);
        setAccountCheckBtnDisabled(true); // 重複チェックボタンdisabled

        axios
            .post(`/api/${agencyAccount}/is-account-exists`, {
                account: data.account
            })
            .then(response => {
                if (response?.status === 200) {
                    setAccountDuplicateState("ok");
                } else {
                    setAccountDuplicateState("ng");
                }
            })
            .catch(error => {
                if (error?.response) {
                    if (error?.response?.status === 422) {
                        setAccountDuplicateState("ng");
                        let msg = [];
                        for (let key in error.response.data.errors) {
                            msg.push(error.response.data.errors[key][0]);
                        }
                        alert(msg.join("\n"));
                    }
                } else if (error.request) {
                    alert(error.request);
                } else {
                    alert("Error", error.message);
                }
            })
            .finally(() => {
                setAccountCheckBtnDisabled(false);
            });
    };

    return (
        <>
            {isLoading ? (
                "loading ... "
            ) : (
                <>
                    <ul className="sideList half">
                        <li>
                            <span className="inputLabel">アカウントID</span>
                            <div className="buttonSet">
                                <div className={accountCheckClass}>
                                    <input
                                        type="text"
                                        name="account"
                                        value={data.account ?? ""}
                                        onChange={handleInputChange}
                                        placeholder="半角英数"
                                    />
                                </div>
                                <button
                                    className="orangeBtn wd40"
                                    onClick={handleAccountDuplicateCheck}
                                    disabled={accountCheckBtnDisabled}
                                >
                                    重複チェック
                                </button>
                            </div>
                        </li>
                    </ul>
                    <ul className="sideList">
                        <li className="wd40">
                            <span className="inputLabel">ユーザー名</span>
                            <input
                                type="text"
                                name="name"
                                value={data.name ?? ""}
                                onChange={handleInputChange}
                            />
                        </li>
                        <li className="wd30">
                            <span className="inputLabel">
                                ユーザー権限
                                <a href={`/${agencyAccount}/system/role`}>
                                    <span className="material-icons">
                                        settings
                                    </span>
                                </a>
                            </span>
                            <div className="selectBox">
                                <select
                                    name="agency_role_id"
                                    value={data.agency_role_id ?? ""}
                                    onChange={handleInputChange}
                                >
                                    {agencyRoles &&
                                        Object.keys(agencyRoles).map(val => (
                                            <option key={val} value={val}>
                                                {agencyRoles[val]}
                                            </option>
                                        ))}
                                </select>
                            </div>
                        </li>
                    </ul>
                    <ul className="baseList">
                        <li className="wd40">
                            <span className="inputLabel">パスワード</span>
                            <input
                                type="text"
                                name="password"
                                value={data.password ?? ""}
                                onChange={handleInputChange}
                            />
                        </li>
                        <li className="wd40">
                            <span className="inputLabel">パスワード再確認</span>
                            <input
                                type="text"
                                name="password_confirmation"
                                value={data.password_confirmation ?? ""}
                                onChange={handleInputChange}
                            />
                        </li>
                    </ul>
                    <hr className="sepBorder" />
                    <ul className="baseList">
                        <li className="wd40">
                            <span className="inputLabel">メールアドレス</span>
                            <input
                                type="email"
                                name="email"
                                value={data.email ?? ""}
                                onChange={handleInputChange}
                            />
                        </li>
                        {customItemDatas.map((row, index) => (
                            <li className="wd30" key={index}>
                                <span className="inputLabel">
                                    {row?.name}
                                    <a
                                        href={`/${agencyAccount}/system/custom/?tab=${row?.user_custom_category_id}`}
                                    >
                                        <span className="material-icons">
                                            settings
                                        </span>
                                    </a>
                                </span>
                                {row?.type ===
                                    userCustomItemTypes.custom_item_type_list && (
                                    <div className="selectBox">
                                        <select
                                            name={row?.key}
                                            defaultValue={
                                                Object.keys(
                                                    row?.select_item
                                                )?.[0] ?? ""
                                            }
                                            value={data?.[row?.key] ?? ""}
                                            onChange={handleInputChange}
                                        >
                                            {Object.keys(row?.select_item).map(
                                                (key, index) => (
                                                    <option
                                                        key={index}
                                                        value={key}
                                                    >
                                                        {row.select_item[key]}
                                                    </option>
                                                )
                                            )}
                                        </select>
                                    </div>
                                )}
                            </li>
                        ))}
                        <li className="wd20">
                            <span className="inputLabel">アカウント状態</span>
                            <ul className="slideRadio">
                                {formSelects?.statuses &&
                                    Object.keys(formSelects.statuses)
                                        .sort((a, b) => b - a)
                                        .map(v => (
                                            <li key={v}>
                                                <input
                                                    type="radio"
                                                    id={`status_${v}`}
                                                    name="status"
                                                    value={v ?? ""}
                                                    onChange={handleInputChange}
                                                    checked={v == data?.status}
                                                />
                                                <label htmlFor={`status_${v}`}>
                                                    {formSelects.statuses[v]}
                                                </label>
                                            </li>
                                        ))}
                            </ul>
                        </li>
                    </ul>
                </>
            )}
        </>
    );
};

const Element = document.getElementById("inputArea");
if (Element) {
    const agencyAccount = Element.getAttribute("agencyAccount");
    const customCategoryCode = Element.getAttribute("customCategoryCode");
    const defaultValue = Element.getAttribute("defaultValue");
    const parsedDefaultValue = defaultValue && JSON.parse(defaultValue);
    const formSelects = Element.getAttribute("formSelects");
    const parsedFormSelects = formSelects && JSON.parse(formSelects);
    const userCustomItemTypes = Element.getAttribute("userCustomItemTypes");
    const parsedUserCustomItemTypes =
        userCustomItemTypes && JSON.parse(userCustomItemTypes);

    render(
        <InputArea
            agencyAccount={agencyAccount}
            customCategoryCode={customCategoryCode}
            defaultValue={parsedDefaultValue}
            formSelects={parsedFormSelects}
            userCustomItemTypes={parsedUserCustomItemTypes}
        />,
        document.getElementById("inputArea")
    );
}
