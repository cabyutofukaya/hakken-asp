import _ from "lodash";
import React, { useState, useContext } from "react";
import { render } from "react-dom";
import ConstApp from "./components/ConstApp";
import { ConstContext } from "./components/ConstApp";
import ConsultationArea from "./components/Reserve/ConsultationArea";
import ReserveBasicInfoArea from "./components/Reserve/ReserveBasicInfoArea";
import ReserveDetail from "./components/Reserve/ReserveDetail";
import StatusModal from "./components/Reserve/StatusModal";
import { useMountedRef } from "../../hooks/useMountedRef";
import SmallDangerModal from "./components/SmallDangerModal";
import TopControlBox from "./components/Reserve/TopControlBox";
import CancelChargeModal from "./components/Reserve/CancelChargeModal";
import CancelModal from "./components/Reserve/CancelModal";
import TopStatus from "./components/Reserve/TopStatus";
import SuccessMessage from "./components/SuccessMessage";
import ErrorMessage from "./components/ErrorMessage";
import BaseErrorMessage from "./components/Reserve/Errors/BaseErrorMessage";
import DetailErrorMessage from "./components/Reserve/Errors/DetailErrorMessage";

/**
 *
 * @param {array} formSelects form選択値（配列はタブ毎に保持）
 * @param {aray} permission 認可情報
 * @returns
 */
const ReserveShowArea = ({
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
    const [tabBadgeCount, setTabBadgeCount] = useState({}); // タブバッジカウント

    const [isCanceling, setIsCanceling] = useState(false); // キャンセル処理中か否か
    const [isDeleting, setIsDeleting] = useState(false); // 削除処理中か否か

    const [status, setStatus] = useState(
        defaultValue?.[consts.common.tabCodes?.tab_basic_info]?.status
    ); // 予約ステータス
    const [reserveUpdatedAt, setReserveUpdatedAt] = useState(
        defaultValue?.[consts.common.tabCodes?.tab_basic_info]?.updatedAt
    ); // 予約情報更新日時

    const [successMessage, setSuccessMessage] = useState(
        flashMessage?.success_message ?? ""
    ); // 成功メッセージ。ページ遷移時のフラッシュメッセージがあれば初期状態でセット
    const [baseErrorMessage, setBaseErrorMessage] = useState(""); // 基本情報用エラーメッセージ
    const [itineraryErrorMessage, setItineraryErrorMessage] = useState(""); // 行程用エラーメッセージ
    const [documentErrorMessage, setDocumentErrorMessage] = useState(""); // 帳票用エラーメッセージ

    // タブクリック
    const handleTabChange = (e, tab) => {
        e.preventDefault();
        setCurrentTab(tab);
        //メッセージ初期化
        setSuccessMessage("");
        setBaseErrorMessage("");
        setItineraryErrorMessage("");
        setDocumentErrorMessage("");
    };

    // キャンセル処理(チャージあり→チャージ設定ページへ遷移)
    const handleCharge = () => {
        if (!mounted.current) return;
        if (isCanceling) return;
        setIsCanceling(false); // 一応、処理フラグを無効にしておく
        $(".js-modal-close").trigger("click"); // モーダルクローズ
        location.href = consts?.common?.cancelChargeUrl;
    };
    // キャンセル処理(ノンチャージ)
    const handleNonCharge = async () => {
        if (!mounted.current) return;
        if (isCanceling) return;

        setIsCanceling(true);

        const response = await axios
            .post(
                `/api/${agencyAccount}/reserve/${reserve?.control_number}/no-cancel-charge/cancel`,
                {
                    set_message: true, // API処理完了後、flashメッセージセットを要求
                    updated_at: reserveUpdatedAt,
                    _method: "put"
                }
            )
            .finally(() => {
                $(".js-modal-close").trigger("click"); // 削除モーダルclose
                setTimeout(function() {
                    if (mounted.current) {
                        setIsCanceling(false);
                    }
                }, 3000);
            });

        if (response?.data?.result == "ok") {
            location.href = consts.common.afterCancelUrl;
        } else {
            alert("キャンセル処理に失敗しました。");
        }
    };

    // 削除処理
    const handleDelete = async () => {
        if (!mounted.current) return;
        if (isDeleting) return;

        setIsDeleting(true);

        const response = await axios
            .delete(
                `/api/${agencyAccount}/reserve/${reserve?.control_number}`,
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

    // パンクズリストindex部
    const IndexBreadcrumb = ({
        isDeparted,
        reserveIndexUrl,
        departedIndexUrl
    }) => {
        if (isDeparted == 1) {
            return (
                <li>
                    <a href={departedIndexUrl}>催行済み一覧</a>
                </li>
            );
        } else {
            return (
                <li>
                    <a href={reserveIndexUrl}>予約管理</a>
                </li>
            );
        }
    };

    return (
        <>
            <div id="pageHead">
                <h1>
                    <span className="material-icons">event_note</span>予約情報
                    {reserve?.control_number}
                    <TopStatus
                        reserve={reserve}
                        status={status}
                        reserveStatus={
                            defaultValue?.[
                                consts.common.tabCodes?.tab_basic_info
                            ]?.reserveStatus
                        }
                    />
                </h1>
                <ol className="breadCrumbs">
                    {/**催行済か否かで出し分け */}
                    <IndexBreadcrumb
                        isDeparted={reserve?.is_departed}
                        reserveIndexUrl={consts?.common?.reserveIndexUrl}
                        departedIndexUrl={consts?.common?.departedIndexUrl}
                    />
                    <li>
                        <span>予約情報 {reserve?.control_number}</span>
                    </li>
                </ol>
                <TopControlBox
                    isCanceled={consts?.common?.isCanceled}
                    isCanceling={isCanceling}
                    existPurchaseData={consts?.common?.existPurchaseData}
                    isDeleting={isDeleting}
                    updatePermission={permission.basic?.reserve_update}
                    deletePermission={permission.basic?.reserve_delete}
                />
            </div>

            {/**基本情報用エラーメッセージ */}
            <BaseErrorMessage message={baseErrorMessage} />
            {/**詳細情報用エラーメッセージ */}
            <DetailErrorMessage
                itineraryErrorMessage={itineraryErrorMessage}
                documentErrorMessage={documentErrorMessage}
            />

            {/**APIがらみのサクセスメッセージ */}
            <SuccessMessage
                message={successMessage}
                setMessage={setSuccessMessage}
            />

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
                                予約基本情報
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
                                予約詳細
                            </span>
                        </li>
                    )}
                    {permission.consultation.consultation_read && (
                        <li>
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
                                {tabBadgeCount?.[
                                    consts.common.tabCodes.tab_consultation
                                ] > 0 && (
                                    <span>
                                        {
                                            tabBadgeCount[
                                                consts.common.tabCodes
                                                    .tab_consultation
                                            ]
                                        }
                                    </span>
                                )}
                            </span>
                        </li>
                    )}
                </ul>
            </div>
            {permission.basic.reserve_read && (
                <ReserveBasicInfoArea
                    isShow={
                        currentTab === consts.common.tabCodes.tab_basic_info
                    }
                    reserveNumber={reserve?.control_number}
                    isDeparted={reserve?.is_departed}
                    isCancel={reserve?.cancel_at ? true : false}
                    status={status}
                    setStatus={setStatus}
                    setUpdatedAt={setReserveUpdatedAt}
                    consts={consts?.[consts.common.tabCodes.tab_basic_info]}
                    customFields={
                        customFields?.[consts.common.tabCodes.tab_basic_info]
                    }
                    constsCommon={consts?.common}
                    permission={permission.basic}
                    errorMessage={baseErrorMessage}
                    setErrorMessage={setBaseErrorMessage}
                />
            )}
            {permission.detail.reserve_read && (
                <ReserveDetail
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
                    setSuccessMessage={setSuccessMessage}
                    itineraryErrorMessage={itineraryErrorMessage}
                    setItineraryErrorMessage={setItineraryErrorMessage}
                    documentErrorMessage={documentErrorMessage}
                    setDocumentErrorMessage={setDocumentErrorMessage}
                    updatedAt={reserveUpdatedAt}
                    setUpdatedAt={setReserveUpdatedAt}
                />
            )}
            {permission.consultation.consultation_read && (
                <ConsultationArea
                    isShow={
                        currentTab === consts.common.tabCodes.tab_consultation
                    }
                    tab={consts.common.tabCodes.tab_consultation}
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
                    tabBadgeCount={tabBadgeCount}
                    setTabBadgeCount={setTabBadgeCount}
                />
            )}

            {/**ステータスモーダル */}
            <StatusModal
                id="mdStatus"
                apiUrl={`/api/${agencyAccount}/reserve/${reserve?.control_number}/status`}
                status={status}
                changeStatus={setStatus}
                updatedAt={reserveUpdatedAt ?? ""}
                setUpdatedAt={setReserveUpdatedAt}
                statuses={
                    formSelects?.[consts.common.tabCodes?.tab_basic_info]
                        ?.statuses
                }
            />

            {/* キャンセルモーダル。仕入情報があればキャンセルチャージ選択モーダル。なければ選択機能ナシのモーダルを表示 */}
            {consts.common.existPurchaseData && (
                <CancelChargeModal
                    id="mdCxl"
                    defaultCheck={defaultValue.common.cancel_charge}
                    nonChargeAction={handleNonCharge}
                    chargeAction={handleCharge}
                    isActioning={isCanceling}
                    title={
                        reserve?.cancel_at
                            ? "キャンセルチャージを設定しますか？"
                            : "この予約を取り消しますか？"
                    }
                    positiveLabel={reserve?.cancel_at ? "設定する" : "取り消す"}
                />
            )}
            {!consts.common.existPurchaseData && (
                <CancelModal
                    id="mdCxl"
                    nonChargeAction={handleNonCharge}
                    isActioning={isCanceling}
                />
            )}

            {/* 削除モーダル */}
            <SmallDangerModal
                id="mdDelete"
                title="この予約を削除しますか？"
                actionLabel="削除する"
                handleAction={handleDelete}
                isActioning={isDeleting}
            />
        </>
    );
};

const Element = document.getElementById("reserveShowArea");
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
            <ReserveShowArea
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
        document.getElementById("reserveShowArea")
    );
}
