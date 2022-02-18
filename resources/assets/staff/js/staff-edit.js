import React, { useState } from "react";
import { render } from "react-dom";
import useFetch from "../../hooks/useFetch";
import _ from "lodash";
import SmallDangerModal from "./components/SmallDangerModal";
import { useMountedRef } from "../../hooks/useMountedRef";
import ReactLoading from "react-loading";

/**
 * 編集エリア
 *
 * @param {int} isMaster マスター権限か否か
 * @returns
 */
const InputArea = ({
    isMaster,
    agencyAccount,
    defaultValue,
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

    const isLoading = !agencyRoles || !customItemDatas; // 編集データ読み込み中

    const handleInputChange = e => {
        setData({ ...data, [e.target.name]: e.target.value });
    };

    return (
        <>
            {isLoading ? (
                <ReactLoading type={"bubbles"} color={"#dddddd"} />
            ) : (
                <>
                    {/**マスター判定パラメータ */}
                    <input type="hidden" name="is_master" value={isMaster} />
                    <ul className="sideList half">
                        <li>
                            <span className="inputLabel">アカウントID</span>
                            <div className="buttonSet">
                                <div>
                                    <input
                                        type="text"
                                        name="account"
                                        value={data.account ?? ""}
                                        disabled={true}
                                    />
                                </div>
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
                                {/**スーパーマスターの場合は権限変更不可 */}
                                <select
                                    name="agency_role_id"
                                    defaultValue={
                                        _.head(Object.keys(agencyRoles)) ?? ""
                                    }
                                    value={data.agency_role_id ?? ""}
                                    onChange={handleInputChange}
                                    disabled={isMaster == 1}
                                >
                                    {Object.keys(agencyRoles).map(
                                        (val, index) => (
                                            <option key={index} value={val}>
                                                {agencyRoles[val]}
                                            </option>
                                        )
                                    )}
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
                                placeholder="更新しない場合は未入力"
                            />
                        </li>
                        <li className="wd40">
                            <span className="inputLabel">パスワード再確認</span>
                            <input
                                type="text"
                                name="password_confirmation"
                                value={data.password_confirmation ?? ""}
                                onChange={handleInputChange}
                                placeholder="更新しない場合は未入力"
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
                            <React.Fragment key={index}>
                                <li className="wd30">
                                    <span className="inputLabel">
                                        {row?.name}
                                        <a
                                            href={`/${agencyAccount}/system/custom/?tab=${customCategoryCode}`}
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
                                                {Object.keys(
                                                    row?.select_item
                                                ).map((key, index) => (
                                                    <option
                                                        key={index}
                                                        value={key}
                                                    >
                                                        {row.select_item[key]}
                                                    </option>
                                                ))}
                                            </select>
                                        </div>
                                    )}
                                </li>
                            </React.Fragment>
                        ))}
                    </ul>
                </>
            )}
        </>
    );
};

/**
 * アカウントの有効無、削除
 *
 * @param {*} param0
 * @returns
 */
const AcountControl = ({
    agencyAccount,
    staffAccount,
    formSelects,
    defaultValue
}) => {
    const mounted = useMountedRef(); // マウント・アンマウント制御

    const [status, setStatus] = useState(defaultValue?.status);
    const [isChanging, setIsChanging] = useState(false); // ステータス変更処理中か否か
    const [isDeleting, setIsDeleting] = useState(false); // 削除処理中か否か

    const handleDelete = async e => {
        if (!mounted.current) return;
        if (isDeleting) return;

        setIsDeleting(true);

        const response = await axios
            .delete(`/api/${agencyAccount}/user/${staffAccount}`, {
                data: {
                    set_message: true // API処理完了後、flashメッセージセットを要求
                }
            })
            .finally(() => {
                if (mounted.current) {
                    setIsDeleting(false);
                }
            });

        if (response?.status == 200) {
            location.href = `/${agencyAccount}/system/user/index`;
        }
    };

    const handleInputChange = async e => {
        if (!mounted.current) return;
        if (isChanging) return;

        setIsChanging(true);

        const st = e.target.value;

        const response = await axios
            .post(`/api/${agencyAccount}/user/${staffAccount}/status`, {
                status: st,
                _method: "put"
            })
            .finally(() => {
                if (mounted.current) {
                    setIsChanging(false);
                }
            });
        if (mounted.current && response?.status == 200) {
            // const data = response.data;
            setStatus(st);
        }
    };

    return (
        <>
            <span className="inputLabel">アカウント制御</span>
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
                                    value={v}
                                    onChange={handleInputChange}
                                    checked={v == status}
                                    disabled={isChanging}
                                />
                                <label htmlFor={`status_${v}`}>
                                    {formSelects.statuses[v]}
                                </label>
                            </li>
                        ))}
                <li>
                    <button
                        className="redBtn js-modal-open"
                        data-target="mdDeleteStaff"
                    >
                        削除
                    </button>
                </li>
            </ul>
            <SmallDangerModal
                id="mdDeleteStaff"
                title="ユーザーを削除しますか？"
                handleAction={handleDelete}
                isActioning={isDeleting}
            />
        </>
    );
};

const Element1 = document.getElementById("inputArea");
if (Element1) {
    const isMaster = Element1.getAttribute("isMaster");
    const agencyAccount = Element1.getAttribute("agencyAccount");
    const customCategoryCode = Element1.getAttribute("customCategoryCode");
    const defaultValue = Element1.getAttribute("defaultValue");
    const parsedDefaultValue = defaultValue && JSON.parse(defaultValue);
    const formSelects = Element1.getAttribute("formSelects");
    const parsedFormSelects = formSelects && JSON.parse(formSelects);
    const userCustomItemTypes = Element1.getAttribute("userCustomItemTypes");
    const parsedUserCustomItemTypes =
        userCustomItemTypes && JSON.parse(userCustomItemTypes);
    render(
        <InputArea
            isMaster={isMaster}
            agencyAccount={agencyAccount}
            customCategoryCode={customCategoryCode}
            defaultValue={parsedDefaultValue}
            formSelects={parsedFormSelects}
            userCustomItemTypes={parsedUserCustomItemTypes}
        />,
        document.getElementById("inputArea")
    );
}

const Element2 = document.getElementById("acountControl");
if (Element2) {
    const agencyAccount = Element2.getAttribute("agencyAccount");
    const staffAccount = Element2.getAttribute("staffAccount");
    const customCategoryCode = Element2.getAttribute("customCategoryCode");
    const defaultValue = Element2.getAttribute("defaultValue");
    const parsedDefaultValue = defaultValue && JSON.parse(defaultValue);
    const formSelects = Element2.getAttribute("formSelects");
    const parsedFormSelects = formSelects && JSON.parse(formSelects);

    render(
        <AcountControl
            agencyAccount={agencyAccount}
            staffAccount={staffAccount}
            customCategoryCode={customCategoryCode}
            defaultValue={parsedDefaultValue}
            formSelects={parsedFormSelects}
        />,
        document.getElementById("acountControl")
    );
}
