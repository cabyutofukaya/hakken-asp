import _ from "lodash";
import React, { useState, useContext } from "react";
import { render } from "react-dom";
import ConstApp from "./components/ConstApp";
import { ConstContext } from "./components/ConstApp";
import StatusModal from "./components/Reserve/StatusModal";
import { useMountedRef } from "../../hooks/useMountedRef";
import SmallDangerModal from "./components/SmallDangerModal";
import classNames from "classnames";
import InvalidMessage from "./components/Reserve/InvalidMessage";
import WebReserveBasicInfoArea from "./components/Reserve/WebReserveBasicInfoArea";
import WebConsultationArea from "./components/Reserve/WebConsultationArea";
import WebReserveDetail from "./components/Reserve/WebReserveDetail";
import TopControlBox from "./components/Reserve/TopControlBox";
import CancelModal from "./components/Reserve/CancelModal";
import CancelChargeModal from "./components/Reserve/CancelChargeModal";

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

    // キャンセル処理(チャージあり→チャージ設定ページへ遷移)
    const handleCharge = () => {
        if (!mounted.current) return;
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
                `/api/${agencyAccount}/web/reserve/${reserve?.control_number}/no-cancel-charge/cancel`,
                {
                    set_message: true, // API処理完了後、flashメッセージセットを要求
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

        if (response) {
            window.location.reload(); // リロード
        }
    };

    // 削除処理
    const handleDelete = async () => {
        if (!mounted.current) return;
        if (isDeleting) return;

        setIsDeleting(true);

        const response = await axios
            .delete(`/api/${agencyAccount}/web/reserve/${reserve?.hash_id}`, {
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

    // 上部ステータス部(催行済みか否かで出し分け)
    const TopStatus = ({ reserve, status, reserveStatus }) => {
        if (reserve?.is_departed) {
            return <span className="status gray fix">{reserveStatus}</span>;
        } else {
            return (
                <span
                    className="status blue js-modal-open"
                    data-target="mdStatus"
                >
                    {status}
                </span>
            );
        }
    };

    // パンクズリストindex部(催行済みか否かで出し分け)
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
                    <a href={reserveIndexUrl}>WEB予約管理</a>
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
                    {/**催行済か否かで出し分け */}
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
                    isCanceling={isCanceling}
                    isDeleting={isDeleting}
                    updatePermission={permission.basic?.reserve_update}
                    deletePermission={permission.basic?.reserve_delete}
                />
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
                                予約基本情報
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
                                予約詳細
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
                <WebReserveBasicInfoArea
                    isShow={
                        currentTab === consts.common.tabCodes.tab_basic_info
                    }
                    reserveNumber={reserve?.control_number}
                    isDeparted={reserve?.is_departed}
                    status={status}
                    consts={consts?.[consts.common.tabCodes.tab_basic_info]}
                    customFields={
                        customFields?.[consts.common.tabCodes.tab_basic_info]
                    }
                    constsCommon={consts?.common}
                    permission={permission.basic}
                />
            )}
            {permission.detail.reserve_read && (
                <WebReserveDetail
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

            {/**ステータスモーダル */}
            <StatusModal
                id="mdStatus"
                apiUrl={`/api/${agencyAccount}/web/reserve/${reserve?.control_number}/status`}
                status={status}
                changeStatus={setStatus}
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
            <ReserveShowArea
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
        document.getElementById("reserveShowArea")
    );
}
