import _ from "lodash";
import React, { useState, useContext } from "react";
import { ConstContext } from "./components/ConstApp";
import ConstApp from "./components/ConstApp";
import { render } from "react-dom";
import ConsultationArea from "./components/Reserve/ConsultationArea";
import EstimateBasicInfoArea from "./components/Reserve/EstimateBasicInfoArea";
import EstimateDetail from "./components/Reserve/EstimateDetail";
import StatusModal from "./components/Reserve/StatusModal";
import classNames from "classnames";
import { useMountedRef } from "../../hooks/useMountedRef";
import SmallDangerModal from "./components/SmallDangerModal";

/**
 *
 * @param {array} formSelects form選択値（配列はタブ毎に保持）
 * @param {aray} permission 認可情報
 * @returns
 */
const EstimateShowArea = ({
    defaultTab,
    targetConsultationNumber,
    reserve,
    formSelects,
    defaultValue,
    customFields,
    consts,
    permission,
    flashMessage
}) => {
    const { agencyAccount } = useContext(ConstContext);

    const mounted = useMountedRef(); // マウント・アンマウント制御

    const [currentTab, setCurrentTab] = useState(defaultTab); //選択中のタブ

    const [isCanceling, setIsCanceling] = useState(false); // キャンセル処理中か否か
    const [isDeleting, setIsDeleting] = useState(false); // 削除処理中か否か

    const [status, setStatus] = useState(
        defaultValue?.[consts.common.tabCodes?.tab_basic_info]?.status
    );

    // タブクリック
    const handleTabChange = (e, tab) => {
        e.preventDefault();
        setCurrentTab(tab);
    };

    // // キャンセル処理 -> 廃止
    // const handleCancel = async () => {
    // };

    // 削除処理
    const handleDelete = async () => {
        if (!mounted.current) return;
        if (isDeleting) return;

        setIsDeleting(true);

        const response = await axios
            .delete(
                `/api/${agencyAccount}/estimate/${reserve?.estimate_number}`,
                {
                    data: {
                        set_message: true // API処理完了後、flashメッセージセットを要求
                    }
                }
            )
            .finally(() => {
                $(".js-modal-close").trigger("click"); // 削除モーダルclose
                setTimeout(function() {
                    if (mounted.current) {
                        setIsDeleting(false);
                    }
                }, 3000);
            });

        if (response?.status == 200) {
            // 削除完了後は一覧ページへ遷移
            location.href = consts.common.afterDeletedUrl;
        }
    };

    return (
        <>
            <div id="pageHead">
                <h1>
                    <span className="material-icons">event_note</span>見積情報
                    {reserve?.estimate_number}
                    <span
                        className="status blue js-modal-open"
                        data-target="mdStatus"
                    >
                        {status}
                    </span>
                </h1>
                <ol className="breadCrumbs">
                    <li>
                        <a href={consts.common.estimateIndexUrl}>見積管理</a>
                    </li>
                    <li>
                        <span>見積情報 {reserve?.estimate_number}</span>
                    </li>
                </ol>
                {permission.basic?.reserve_delete && (
                    <div className="deleteControl">
                        <button
                            className={classNames("redBtn", {
                                "js-modal-open": !isDeleting
                            })}
                            data-target="mdDelete"
                        >
                            削除
                        </button>
                    </div>
                )}
            </div>

            {flashMessage?.success_message && (
                <div id="successMessage">
                    <p>
                        <span className="material-icons">check_circle</span>
                        {flashMessage.success_message}
                    </p>
                    <span className="material-icons closeIcon">cancel</span>
                </div>
            )}

            <div id="tabNavi" className="estimateNav">
                <ul>
                    <li>
                        {permission.basic.reserve_read && (
                            <span
                                className={
                                    currentTab ===
                                    consts.common.tabCodes.tab_basic_info
                                        ? "tab tabstay"
                                        : "tab"
                                }
                                onClick={e =>
                                    handleTabChange(
                                        e,
                                        consts.common.tabCodes.tab_basic_info
                                    )
                                }
                            >
                                見積基本情報
                            </span>
                        )}
                    </li>
                    <li>
                        {permission.detail.reserve_read && (
                            <span
                                className={
                                    currentTab ===
                                    consts.common.tabCodes.tab_reserve_detail
                                        ? "tab tabstay"
                                        : "tab"
                                }
                                onClick={e =>
                                    handleTabChange(
                                        e,
                                        consts.common.tabCodes
                                            .tab_reserve_detail
                                    )
                                }
                            >
                                見積詳細
                            </span>
                        )}
                    </li>
                    <li>
                        {permission.consultation.consultation_read && (
                            <span
                                className={
                                    currentTab ===
                                    consts.common.tabCodes.tab_consultation
                                        ? "tab tabstay"
                                        : "tab"
                                }
                                onClick={e =>
                                    handleTabChange(
                                        e,
                                        consts.common.tabCodes.tab_consultation
                                    )
                                }
                            >
                                相談一覧
                            </span>
                        )}
                    </li>
                </ul>
            </div>
            {permission.basic.reserve_read && (
                <EstimateBasicInfoArea
                    isShow={
                        currentTab === consts.common.tabCodes.tab_basic_info
                    }
                    estimateNumber={reserve?.estimate_number}
                    status={status}
                    setStatus={setStatus}
                    consts={consts?.[consts.common.tabCodes.tab_basic_info]}
                    customFields={
                        customFields?.[consts.common.tabCodes.tab_basic_info]
                    }
                    constsCommon={consts?.common}
                    permission={permission.basic}
                />
            )}
            {permission.detail.reserve_read && (
                <EstimateDetail
                    isShow={
                        currentTab === consts.common.tabCodes.tab_reserve_detail
                    }
                    applicationStep={reserve?.application_step}
                    applicationStepList={consts.common.application_step_list}
                    estimateNumber={reserve?.estimate_number}
                    reserveNumber={reserve?.control_number}
                    defaultValue={
                        defaultValue?.[
                            consts.common.tabCodes.tab_reserve_detail
                        ]
                    }
                    formSelects={
                        formSelects?.[consts.common.tabCodes.tab_reserve_detail]
                    }
                    consts={consts?.[consts.common.tabCodes.tab_reserve_detail]}
                    constsCommon={consts?.common}
                    permission={permission.detail}
                />
            )}
            {permission.consultation.consultation_read && (
                <ConsultationArea
                    isShow={
                        currentTab === consts.common.tabCodes.tab_consultation
                    }
                    targetConsultationNumber={targetConsultationNumber}
                    applicationStep={reserve?.application_step}
                    applicationStepList={consts.common.application_step_list}
                    estimateNumber={reserve?.estimate_number}
                    reserveNumber={reserve?.control_number}
                    formSelects={
                        formSelects?.[consts.common.tabCodes.tab_consultation]
                    }
                    defaultValue={
                        defaultValue?.[consts.common.tabCodes.tab_consultation]
                    }
                    consts={consts?.[consts.common.tabCodes.tab_consultation]}
                    permission={permission.consultation}
                />
            )}

            <StatusModal
                id="mdStatus"
                apiUrl={`/api/${agencyAccount}/estimate/${reserve?.estimate_number}/status`}
                status={status}
                changeStatus={setStatus}
                statuses={
                    formSelects?.[consts.common.tabCodes?.tab_basic_info]
                        ?.statuses
                }
            />
            {/* キャンセルモーダル */}
            {/* <SmallDangerModal
                id="mdCxl"
                title="この見積を取り消しますか？"
                actionLabel="取り消す"
                handleAction={handleCancel}
                isActioning={isCanceling}
            /> */}
            {/* 削除モーダル */}
            <SmallDangerModal
                id="mdDelete"
                title="この見積を削除しますか？"
                actionLabel="削除する"
                handleAction={handleDelete}
                isActioning={isDeleting}
            />
        </>
    );
};

const Element = document.getElementById("estimateShowArea");
if (Element) {
    const jsVars = Element.getAttribute("jsVars");
    const parsedJsVars = jsVars && JSON.parse(jsVars);
    const defaultTab = Element.getAttribute("defaultTab");
    const targetConsultationNumber = Element.getAttribute(
        "targetConsultationNumber"
    );
    const reserve = Element.getAttribute("reserve");
    const parsedReserve = reserve && JSON.parse(reserve);
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
    const flashMessage = Element.getAttribute("flashMessage");
    const parsedFlashMessage = flashMessage && JSON.parse(flashMessage);

    render(
        <ConstApp jsVars={parsedJsVars}>
            <EstimateShowArea
                defaultTab={defaultTab}
                targetConsultationNumber={targetConsultationNumber}
                reserve={parsedReserve}
                formSelects={parsedFormSelects}
                defaultValue={parsedDefaultValue}
                customFields={parsedCustomFields}
                consts={parsedConsts}
                permission={parsedPermission}
                flashMessage={parsedFlashMessage}
            />
        </ConstApp>,
        document.getElementById("estimateShowArea")
    );
}
