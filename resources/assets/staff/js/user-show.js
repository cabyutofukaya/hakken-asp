import React, { useState, useContext } from "react";
import { ConstContext } from "./components/ConstApp";
import { render } from "react-dom";
import ConstApp from "./components/ConstApp";
import AccountControl from "./components/User/AccountControl";
import ConsultationArea from "./components/User/ConsultationArea.js";
import CustomerArea from "./components/User/CustomerArea";
import HistoryArea from "./components/User/HistoryArea";

const UserShowArea = ({
    defaultTab,
    tabCodes,
    customCategoryCode,
    user,
    formSelects,
    defaultValue,
    customFields,
    consts,
    permission
}) => {
    const { agencyAccount } = useContext(ConstContext);

    const [currentTab, setCurrentTab] = useState(defaultTab); //選択中のタブ
    // タブクリック
    const handleTabChange = (e, tab) => {
        e.preventDefault();
        setCurrentTab(tab);
    };

    return (
        <>
            <div id="pageHead">
                <h1>
                    <span className="material-icons">person</span>
                    {user.userable.name ?? ""}
                    {user.userable.name_kana && (
                        <>({user.userable.name_kana})</>
                    )}
                </h1>
                <AccountControl
                    userNumber={user.user_number ?? ""}
                    value={user.status ?? ""}
                    permission={permission.customer}
                />
                <ol className="breadCrumbs">
                    <li>
                        <a href={`/${agencyAccount}/client/person/index`}>
                            顧客管理
                        </a>
                    </li>
                    <li>
                        <span>
                            {user.userable.name ?? "-"}
                            {user.userable.name_kana && (
                                <>({user.userable.name_kana})</>
                            )}
                        </span>
                    </li>
                </ol>
            </div>

            <div id="tabNavi" className="userNav">
                <ul>
                    {permission.customer.read && (
                        <li>
                            <span
                                className={
                                    currentTab === tabCodes.tab_customer_info
                                        ? "tab tabstay"
                                        : "tab"
                                }
                                onClick={e =>
                                    handleTabChange(
                                        e,
                                        tabCodes.tab_customer_info
                                    )
                                }
                            >
                                顧客情報
                            </span>
                        </li>
                    )}

                    {permission.history.read && (
                        <li>
                            <span
                                className={
                                    currentTab === tabCodes.tab_usage_history
                                        ? "tab tabstay"
                                        : "tab"
                                }
                                onClick={e =>
                                    handleTabChange(
                                        e,
                                        tabCodes.tab_usage_history
                                    )
                                }
                            >
                                利用履歴
                            </span>
                        </li>
                    )}

                    {permission.consultation.read && (
                        <li>
                            <span
                                className={
                                    currentTab === tabCodes.tab_consultation
                                        ? "tab tabstay"
                                        : "tab"
                                }
                                onClick={e =>
                                    handleTabChange(
                                        e,
                                        tabCodes.tab_consultation
                                    )
                                }
                            >
                                相談一覧
                            </span>
                        </li>
                    )}
                </ul>
            </div>
            <CustomerArea
                isShow={currentTab === tabCodes.tab_customer_info}
                customCategoryCode={customCategoryCode}
                user={user}
                formSelects={formSelects[tabCodes.tab_customer_info]}
                customFields={customFields?.[tabCodes.tab_customer_info]}
                consts={consts[tabCodes.tab_customer_info]}
                permission={permission.customer}
            />
            <HistoryArea
                isShow={currentTab === tabCodes.tab_usage_history}
                userNumber={user.user_number}
                permission={permission.history}
                consts={consts[tabCodes.tab_usage_history]}
            />
            {permission.consultation.read && (
                <ConsultationArea
                    isShow={currentTab === tabCodes.tab_consultation}
                    agencyAccount={agencyAccount}
                    userNumber={user.user_number}
                    formSelects={formSelects[tabCodes.tab_consultation]}
                    defaultValue={defaultValue?.[tabCodes.tab_consultation]}
                    consts={consts[tabCodes.tab_consultation]}
                    permission={permission.consultation}
                />
            )}
        </>
    );
};

const Element = document.getElementById("userShowArea");
if (Element) {
    const jsVars = Element.getAttribute("jsVars");
    const parsedJsVars = jsVars && JSON.parse(jsVars);
    const defaultTab = Element.getAttribute("defaultTab");
    const tabCodes = Element.getAttribute("tabCodes");
    const customCategoryCode = Element.getAttribute("customCategoryCode");
    const parsedTabCodes = tabCodes && JSON.parse(tabCodes);
    const user = Element.getAttribute("user");
    const parsedUser = user && JSON.parse(user);
    const formSelects = Element.getAttribute("formSelects");
    const parsedFormSelects = formSelects && JSON.parse(formSelects);
    const defaultValue = Element.getAttribute("defaultValue");
    const parsedDefaultValue = defaultValue && JSON.parse(defaultValue);
    const customFields = Element.getAttribute("customFields");
    const parsedCustomFields = customFields && JSON.parse(customFields);
    const consts = Element.getAttribute("consts");
    const parsedConsts = consts && JSON.parse(consts);
    const permission = Element.getAttribute("permission");
    const parsedPermission = permission && JSON.parse(permission);

    render(
        <ConstApp jsVars={parsedJsVars}>
            <UserShowArea
                defaultTab={defaultTab}
                tabCodes={parsedTabCodes}
                customCategoryCode={customCategoryCode}
                user={parsedUser}
                formSelects={parsedFormSelects}
                defaultValue={parsedDefaultValue}
                customFields={parsedCustomFields}
                consts={parsedConsts}
                permission={parsedPermission}
            />
        </ConstApp>,
        document.getElementById("userShowArea")
    );
}
