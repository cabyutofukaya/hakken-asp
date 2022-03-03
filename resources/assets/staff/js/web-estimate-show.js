import _ from "lodash";
import React, { useState, useContext } from "react";
import { ConstContext } from "./components/ConstApp";
import ConstApp from "./components/ConstApp";
import { render } from "react-dom";
import WebConsultationArea from "./components/Reserve/WebConsultationArea";
import WebEstimateBasicInfoArea from "./components/Reserve/WebEstimateBasicInfoArea";
import WebEstimateDetail from "./components/Reserve/WebEstimateDetail";
import StatusModal from "./components/Reserve/StatusModal";
import classNames from "classnames";
import { useMountedRef } from "../../hooks/useMountedRef";
import SmallDangerModal from "./components/SmallDangerModal";
import InvalidMessage from "./components/Reserve/InvalidMessage";
import OnlineRequestModal from "./portal/OnlineRequestModal";
import VideoTitArea from "./components/Reserve/VideoTitArea";
import VideoTitAreaLarge from "./components/Reserve/VideoTitAreaLarge";
import TopDeleteBox from "./components/Reserve/TopDeleteBox";

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
    roomKey,
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

    // const [isCanceling, setIsCanceling] = useState(false); // キャンセル処理中か否か
    const [isDeleting, setIsDeleting] = useState(false); // 削除処理中か否か

    const [status, setStatus] = useState(
        defaultValue?.[consts.common.tabCodes?.tab_basic_info]?.status
    ); // 見積ステータス
    const [reserveUpdatedAt, setReserveUpdatedAt] = useState(
        defaultValue?.[consts.common.tabCodes?.tab_basic_info]?.updatedAt
    ); // 見積情報更新日時

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
            .delete(`/api/${agencyAccount}/web/estimate/${reserve?.hash_id}`, {
                data: {
                    set_message: true // API処理完了後、flashメッセージセットを要求
                }
            })
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

    // TODO 以下のオンライン関連処理はindexページと同じなので、メソッドを共通化できるようにした方が良いかも
    // オンライン相談モーダル関連
    const handleOnlineRequestClick = row => {
        setOnlineRequestValues(row);
        setOnlineRequestInputValues({
            is_change: false, // 日時変更切り替えフラグ
            web_reserve_ext_id: row.web_reserve_ext_id,
            reserve_id: row.reserve_id,
            consult_date: moment(row.consult_date, "YYYY/MM/DD HH:mm").format(
                "YYYY/MM/DD"
            ),
            hour: moment(row.consult_date, "YYYY/MM/DD HH:mm").format("HH"),
            minute: moment(row.consult_date, "YYYY/MM/DD HH:mm").format("mm")
        });
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
                        <a href={consts.common.estimateIndexUrl}>WEB見積管理</a>
                    </li>
                    <li>
                        <span>見積情報 {reserve?.estimate_number}</span>
                    </li>
                </ol>
                {permission.basic?.reserve_delete && (
                    <TopDeleteBox isDeleting={isDeleting} />
                )}

                {reserve.web_reserve_ext.web_online_schedule?.consult_date && (
                    <VideoTitAreaLarge
                        webReserveExt={reserve.web_reserve_ext}
                        senderTypes={consts.common.senderTypes}
                        onlineRequestStatuses={
                            consts.common.onlineRequestStatuses
                        }
                        handleClick={handleOnlineRequestClick}
                    />
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

            <InvalidMessage webReserveExt={reserve?.web_reserve_ext} />

            <div id="tabNavi" className="estimateNav">
                <ul>
                    {permission.basic.reserve_read && (
                        <li>
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
                        </li>
                    )}
                    {permission.detail.reserve_read && (
                        <li>
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
                        </li>
                    )}
                    {permission.consultation.consultation_read && (
                        <li>
                            <span
                                id="consultationTab"
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
                        </li>
                    )}
                </ul>
            </div>
            {permission.basic.reserve_read && (
                <WebEstimateBasicInfoArea
                    isShow={
                        currentTab === consts.common.tabCodes.tab_basic_info
                    }
                    estimateNumber={reserve?.estimate_number}
                    status={status}
                    setStatus={setStatus}
                    updatedAt={reserveUpdatedAt}
                    setUpdatedAt={setReserveUpdatedAt}
                    consts={consts?.[consts.common.tabCodes.tab_basic_info]}
                    customFields={
                        customFields?.[consts.common.tabCodes.tab_basic_info]
                    }
                    constsCommon={consts?.common}
                    permission={permission.basic}
                />
            )}
            {permission.detail.reserve_read && (
                <WebEstimateDetail
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
            {/**相談エリア */}
            {permission.consultation.consultation_read && (
                <WebConsultationArea
                    isShow={
                        permission.consultation.consultation_read &&
                        currentTab === consts.common.tabCodes.tab_consultation
                    }
                    reserve={reserve}
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
                    roomKey={roomKey}
                />
            )}

            <StatusModal
                id="mdStatus"
                apiUrl={`/api/${agencyAccount}/web/estimate/${reserve?.estimate_number}/status`}
                status={status ?? ""}
                changeStatus={setStatus}
                updatedAt={reserveUpdatedAt ?? ""}
                setUpdatedAt={setReserveUpdatedAt}
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
    const roomKey = Element.getAttribute("roomKey");
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
                roomKey={roomKey}
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
