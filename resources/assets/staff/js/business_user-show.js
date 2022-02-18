import _ from "lodash";
import React, { useState, useContext } from "react";
import { ConstContext } from "./components/ConstApp";
import ConstApp from "./components/ConstApp";
import { render } from "react-dom";
import CustomerArea from "./components/BusinessUser/CustomerArea";
import HistoryArea from "./components/BusinessUser/HistoryArea";
import ConsultationArea from "./components/BusinessUser/ConsultationArea";
import AccountControl from "./components/BusinessUser/AccountControl";

/**
 *
 * @param {array} formSelects form選択値（配列はタブ毎に保持）
 * @param {aray} permission 認可情報
 * @returns
 */
const BusinessUserShowArea = ({
    defaultTab,
    tabCodes,
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
                    <span className="material-icons">business</span>
                    {user?.name}
                </h1>

                <AccountControl
                    userNumber={user?.user_number}
                    value={user?.status}
                    permission={permission?.customer}
                    statuses={
                        formSelects?.[tabCodes.tab_customer_info]?.statuses
                    }
                />

                <ol className="breadCrumbs">
                    <li>
                        <a href={`/${agencyAccount}/client/business/index`}>
                            顧客管理
                        </a>
                    </li>
                    <li>
                        <span>{user?.name}</span>
                    </li>
                </ol>
            </div>
            {/* <div id="errorMessage">
                <p>エラーメッセージが入ります。</p>
            </div> */}
            <div id="tabNavi" className="userNav">
                <ul>
                    <li>
                        {permission.customer.read && (
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
                        )}
                    </li>
                    <li>
                        {permission.history.read && (
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
                        )}
                    </li>
                    <li>
                        {permission.consultation.read && (
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
                        )}
                    </li>
                </ul>
            </div>
            <CustomerArea
                isShow={currentTab === tabCodes.tab_customer_info}
                user={user}
                formSelects={formSelects?.[tabCodes.tab_customer_info]}
                customFields={customFields?.[tabCodes.tab_customer_info]}
                consts={consts?.[tabCodes.tab_customer_info]}
                permission={permission.customer}
            />
            <HistoryArea
                isShow={currentTab === tabCodes.tab_usage_history}
                userNumber={user?.user_number}
                permission={permission?.history}
                consts={consts[tabCodes.tab_usage_history]}
            />
            {permission.consultation.read && (
                <ConsultationArea
                    isShow={currentTab === tabCodes.tab_consultation}
                    userNumber={user?.user_number}
                    formSelects={formSelects?.[tabCodes.tab_consultation]}
                    defaultValue={defaultValue?.[tabCodes.tab_consultation]}
                    consts={consts?.[tabCodes.tab_consultation]}
                    permission={permission?.consultation}
                />
            )}
        </>
    );
};

const Element = document.getElementById("businessUserShowArea");
if (Element) {
    const jsVars = Element.getAttribute("jsVars");
    const parsedJsVars = jsVars && JSON.parse(jsVars);
    const defaultTab = Element.getAttribute("defaultTab");
    const tabCodes = Element.getAttribute("tabCodes");
    const parsedTabCodes = tabCodes && JSON.parse(tabCodes);
    const user = Element.getAttribute("businessUser");
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
            <BusinessUserShowArea
                defaultTab={defaultTab}
                tabCodes={parsedTabCodes}
                user={parsedUser}
                formSelects={parsedFormSelects}
                defaultValue={parsedDefaultValue}
                customFields={parsedCustomFields}
                consts={parsedConsts}
                permission={parsedPermission}
            />
        </ConstApp>,
        document.getElementById("businessUserShowArea")
    );
}
