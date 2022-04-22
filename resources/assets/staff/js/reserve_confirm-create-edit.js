import React, { useState, useContext, useMemo } from "react";
import ConstApp from "./components/ConstApp";
import { ConstContext } from "./components/ConstApp";
import { render } from "react-dom";
import { DOCUMENT_COMMON, DOCUMENT_QUOTE } from "./constants";
import classNames from "classnames";
import { useMountedRef } from "../../hooks/useMountedRef";
import _ from "lodash";
import BrText from "./BrText";
import PersonDocumentAddressSettingArea from "./components/BusinessForm/PersonDocumentAddressSettingArea";
import BusinessDocumentAddressSettingArea from "./components/BusinessForm/BusinessDocumentAddressSettingArea";
import SettingCheckRow from "./components/BusinessForm/SettingCheckRow";
import ParticipantCheckSettingArea from "./components/BusinessForm/ParticipantCheckSettingArea";
import SealSettingArea from "./components/BusinessForm/SealSettingArea";
import BreakdownPricePreviewArea from "./components/BusinessForm/BreakdownPricePreviewArea";
import PersonSuperscriptionPreviewArea from "./components/BusinessForm/PersonSuperscriptionPreviewArea";
import BusinessSuperscriptionPreviewArea from "./components/BusinessForm/BusinessSuperscriptionPreviewArea";

// flatpickr
import "flatpickr/dist/themes/airbnb.css";
import { Japanese } from "flatpickr/dist/l10n/ja.js";
import Flatpickr from "react-flatpickr";
import AirticketInfoPreviewArea from "./components/BusinessForm/AirticketInfoPreviewArea";
import SealPreviewArea from "./components/BusinessForm/SealPreviewArea";
import HotelInfoPreviewArea from "./components/BusinessForm/HotelInfoPreviewArea";
import HotelPreviewArea from "./components/BusinessForm/HotelPreviewArea";
import OwnCompanyPreviewArea from "./components/BusinessForm/OwnCompanyPreviewArea";
import ReserveInfoPreviewArea from "./components/BusinessForm/ReserveInfoPreviewArea";
import SuccessMessage from "./components/SuccessMessage";
import StatusUpdateModal from "./components/BusinessForm/StatusUpdateModal";

// 戻る URL
const getBackUrl = (
    reception,
    step,
    types,
    agencyAccount,
    estimateNumber,
    reserveNumber,
    detailTab
) => {
    if (step == types.application_step_draft) {
        return `/${agencyAccount}/estimates/${reception}/${step}/${estimateNumber}?tab=${detailTab}`;
    } else if (step == types.application_step_reserve) {
        return `/${agencyAccount}/estimates/${reception}/${step}/${reserveNumber}?tab=${detailTab}`;
    } else {
        return null;
    }
};

// 作成URL
const getStoreUrl = (
    reception,
    step,
    types,
    agencyAccount,
    estimateNumber,
    reserveNumber,
    itineraryNumber
) => {
    if (step == types.application_step_draft) {
        // 見積
        return `/api/${agencyAccount}/estimate/${reception}/${step}/${estimateNumber}/itinerary/${itineraryNumber}/confirm`;
    } else if (step == types.application_step_reserve) {
        // 予約
        return `/api/${agencyAccount}/estimate/${reception}/${step}/${reserveNumber}/itinerary/${itineraryNumber}/confirm`;
    } else {
        return null;
    }
};

// 更新URL
const getUpdateUrl = (
    reception,
    step,
    types,
    agencyAccount,
    estimateNumber,
    reserveNumber,
    itineraryNumber,
    confirmNumber
) => {
    if (step == types.application_step_draft) {
        // 見積
        return `/api/${agencyAccount}/estimate/${reception}/${step}/${estimateNumber}/itinerary/${itineraryNumber}/confirm/${confirmNumber}`;
    } else if (step == types.application_step_reserve) {
        // 予約
        return `/api/${agencyAccount}/estimate/${reception}/${step}/${reserveNumber}/itinerary/${itineraryNumber}/confirm/${confirmNumber}`;
    } else {
        return null;
    }
};

/**
 *
 * @param {integer} isDeparted 催行済みの場合は1
 * @returns
 */
const ReserveConfirmArea = ({
    reception,
    applicationStep,
    reserveNumber,
    estimateNumber,
    itineraryNumber,
    defaultValue,
    documentQuoteSetting,
    documentCommonSetting,
    formSelects,
    hotelInfo,
    airticketPrices,
    hotelPrices,
    optionPrices,
    hotelContacts,
    consts,
    isDeparted,
    isCanceled
}) => {
    const { agencyAccount } = useContext(ConstContext);
    const mounted = useMountedRef(); // マウント・アンマウント制御

    const [input, setInput] = useState({ ...defaultValue });

    const [saveMessage, setSaveMessage] = useState(""); // 保存完了メッセージ

    const [documentSetting, setDocumentSetting] = useState({
        ...documentQuoteSetting
    }); // 書類設定
    const [commonSetting, setCommonSetting] = useState({
        ...documentCommonSetting
    }); // 共通設定

    // ステータス変更用の値。defaultValueから初期値をセット
    const [status, setStatus] = useState(defaultValue.status);
    const [amountTotal, setAmountTotal] = useState(0); // 合計金額

    const [isSaving, setIsSaving] = useState(false); // 保存処理中か否か
    const [isPdfSaving, setIsPdfSaving] = useState(false); // PDF保存処理中か否か
    const [isLoading, setIsLoading] = useState(false); // API取得中か否か
    const [isStatusUpdating, setIsStatusUpdating] = useState(false); // ステータス更新中か否か

    // 入力フィールドの入力制御
    const handleChange = e => {
        setInput({ ...input, [e.target.name]: e.target.value });
    };

    // 書類設定のフィールドを入力変更
    const handleDocumentInputChange = e => {
        setDocumentSetting({
            ...documentSetting,
            [e.target.name]: e.target.value
        });
    };

    // 表示設定の設定値入力値制御(setting)
    const handleShowSettingChange = setting => {
        setDocumentSetting({ ...documentSetting, [setting]: setting });
    };

    // 共通設定のselect変更
    const handleDocumentCommonSettingChange = async e => {
        if (!mounted.current || isLoading) return;

        const name = e.target.name;
        const value = e.target.value;
        if (!value) {
            setInput({ ...input, [name]: value });
            setCommonSetting({});
            return;
        }

        setIsLoading(true);
        const response = await axios
            .get(`/api/${agencyAccount}/document_common/${value}`)
            .finally(() => {
                if (mounted.current) {
                    setIsLoading(false);
                }
            });
        if (mounted.current && response?.data?.data) {
            const data = response.data.data;
            setInput({ ...input, [name]: value });
            setCommonSetting({ ...data });
        }
    };

    // 申込者入力制御
    const handleDocumentAddressChange = e => {
        const da = input["document_address"];
        da[e.target.name] = e.target.value;
        setInput({ ...input });
    };

    // 参加者チェックOn/Off制御
    const handleParticipantChange = e => {
        let participant_ids = input.participant_ids;
        if (participant_ids.includes(parseInt(e.target.value, 10))) {
            participant_ids = participant_ids.filter(
                id => id != e.target.value
            );
        } else {
            participant_ids = [
                ...participant_ids,
                parseInt(e.target.value, 10)
            ];
        }
        setInput({ ...input, participant_ids });
    };

    // 代表者入力制御
    const handleRepresentativeChange = e => {
        const representative = input["representative"];
        representative["name"] = e.target.value;
        setInput({ ...input });
    };

    // 検印欄項目入力制御
    const handleSealItemChange = ({ index, value }) => {
        documentSetting["seal_items"][index] = value;
        setDocumentSetting({ ...documentSetting });
    };

    //テンプレート変更
    const handleDocumentSettingChange = async e => {
        if (!mounted.current || isLoading) return;

        const name = e.target.name;
        const value = e.target.value;

        if (!value) {
            // テンプレートID、共通設定IDを初期化
            setInput({
                ...input,
                [name]: "", // テンプレート
                document_common_id: "" // 共通設定
            });
            setDocumentSetting({}); // 書類設定初期化
            setCommonSetting({}); // 共通設定初期化
            return;
        }

        setIsLoading(true);
        const response = await axios
            .get(`/api/${agencyAccount}/document_quote/${value}`)
            .finally(() => {
                if (mounted.current) {
                    setIsLoading(false);
                }
            });
        if (mounted.current && response?.data?.data) {
            const data = response.data.data;

            // 選択したテンプレートとそれに関連する共通設定selectメニューを更新
            setInput({
                ...input,
                [name]: value, // テンプレート
                document_common_id: data.document_common_id ?? ""
                // 共通設定
            });

            if (data?.seal == 1) {
                // 検印欄表示設定の場合はsettingプロパティに表示設定をセット
                const setting = data.setting ?? [];
                setting[DOCUMENT_QUOTE.DISPLAY_BLOCK].push(
                    DOCUMENT_QUOTE.SEAL_LABEL
                );
                data.setting = setting;
            }
            setDocumentSetting({ ..._.omit(data, "document_common") });
            setCommonSetting({ ...data.document_common });
        }
    };

    const backUrl = getBackUrl(
        reception,
        applicationStep,
        consts.application_step_list,
        agencyAccount,
        estimateNumber,
        reserveNumber,
        consts.detailTab
    );

    /**
     * 予約確認書類作成処理
     *
     * 引数:setMessage 処理完了後にflashメッセージをサーバー側でセットする場合はTrue
     * 引数:createPdf 保存完了後にPDFを作成するか否か
     */
    const createExec = (setMessage, createPdf, handleLoadingFlag) => {
        const optionParam = { set_message: setMessage, create_pdf: createPdf };
        return axios
            .post(
                getStoreUrl(
                    reception,
                    applicationStep,
                    consts.application_step_list,
                    agencyAccount,
                    estimateNumber,
                    reserveNumber,
                    itineraryNumber
                ),
                {
                    ...input,
                    ...optionParam,
                    amount_total: amountTotal,
                    document_common_setting: commonSetting,
                    document_setting: _.omit(
                        documentSetting,
                        "document_common"
                    ), // 書類設定。共通設定はカット
                    //
                    option_prices: optionPrices,
                    airticket_prices: airticketPrices,
                    hotel_prices: hotelPrices,
                    hotel_info: hotelInfo,
                    hotel_contacts: hotelContacts,
                    //
                    is_canceled: isCanceled == 1 ? 1 : 0
                }
            )
            .finally(() => {
                handleLoadingFlag(false);
            });
    };

    /**
     * 予約確認書類更新処理
     *
     * 引数:setMessage 処理完了後にflashメッセージをサーバー側でセットする場合はTrue
     * 引数:createPdf 保存完了後にPDFを作成するか否か
     * 引数:handleLoadingFlag 処理完了後のflag変更メソッド
     */
    const updateExec = (setMessage, createPdf, handleLoadingFlag) => {
        const optionParam = { set_message: setMessage, create_pdf: createPdf };
        return axios
            .post(
                getUpdateUrl(
                    reception,
                    applicationStep,
                    consts.application_step_list,
                    agencyAccount,
                    estimateNumber,
                    reserveNumber,
                    itineraryNumber,
                    input.confirm_number
                ),
                {
                    ...input,
                    ...optionParam,
                    amount_total: amountTotal,
                    document_common_setting: commonSetting,
                    document_setting: _.omit(
                        documentSetting,
                        "document_common"
                    ), // 書類設定。共通設定はカット
                    //
                    option_prices: optionPrices,
                    airticket_prices: airticketPrices,
                    hotel_prices: hotelPrices,
                    hotel_info: hotelInfo,
                    hotel_contacts: hotelContacts,
                    //
                    is_canceled: isCanceled == 1 ? 1 : 0,
                    _method: "put"
                }
            )
            .finally(() => {
                if (mounted.current) {
                    handleLoadingFlag(false);
                }
            });
    };

    // 保存処理
    const handleSave = async e => {
        e.preventDefault();

        if (!input.document_quote_id) {
            alert("テンプレートが設定されていません。");
            return;
        }
        if (!input.departure_date) {
            alert("出発日が設定されていません。");
            return;
        }
        if (!input.return_date) {
            alert("帰着日が設定されていません。");
            return;
        }

        if (!mounted.current) return;
        if (isSaving) return;

        setIsSaving(true);

        let response = null;
        if (input?.confirm_number) {
            // 更新
            response = await updateExec(false, false, setIsSaving);
        } else {
            // 新規
            response = await createExec(false, false, setIsSaving);
        }

        if (mounted.current && response?.data?.data) {
            const res = response.data.data;
            // input.id = res.id; // 新規保存後はIDが必要なので取得
            // const reserve = {
            //     updated_at: res.reserve.updated_at
            // };
            setInput({
                ...input,
                ...res
            }); // 更新日時等を更新

            // メッセージエリアをslideDown(表示状態)にした後でメッセージをセット
            $("#successMessage .closeIcon")
                .parent()
                .slideDown();
            setSaveMessage("請求書データを保存しました");
        }
    };

    // 保存＆PDF出力処理
    const handlePdf = async e => {
        e.preventDefault();

        if (!input.document_quote_id) {
            alert("テンプレートが設定されていません。");
            return;
        }
        if (!input.departure_date) {
            alert("出発日が設定されていません。");
            return;
        }
        if (!input.return_date) {
            alert("帰着日が設定されていません。");
            return;
        }

        if (!mounted.current) return;
        if (isPdfSaving) return;

        setIsPdfSaving(true);

        let response = null;
        if (input?.confirm_number) {
            // 更新
            response = await updateExec(false, true, setIsPdfSaving);
        } else {
            // 新規
            response = await createExec(false, true, setIsPdfSaving);
        }

        if (mounted.current && response?.data?.data) {
            const res = response.data.data;
            // input.id = res.id; // 新規保存後はIDが必要なので取得
            // const reserve = {
            //     updated_at: res.reserve.updated_at
            // };
            setInput({
                ...input,
                ...res
            }); // 確認番号、更新日時をセットする

            // PDFダウンロード
            window.open(
                `/${agencyAccount}/pdf/document/quote/${res.pdf.id}`,
                "_blank"
            );
        }
    };

    // ステータス更新
    const handleUpdateStatus = async () => {
        if (!mounted.current || isStatusUpdating) {
            // アンマウント、処理中の場合は処理ナシ
            return;
        }

        if (input?.id) {
            if (status == input?.status) {
                //値が変わっていない場合は処理ナシ
                $(".js-modal-close").trigger("click"); // モーダルclose
                return;
            }

            // 更新時
            setIsStatusUpdating(true); // 処理中フラグOn

            const response = await axios
                .post(
                    `/api/${agencyAccount}/reserve_confirm/${input?.id}/status`,
                    {
                        status: status,
                        reserve: { updated_at: input?.reserve?.updated_at },
                        _method: "put"
                    }
                )
                .finally(() => {
                    $(".js-modal-close").trigger("click"); // モーダルclose
                    setTimeout(function() {
                        if (mounted.current) {
                            setIsStatusUpdating(false);
                        }
                    }, 3000);
                });

            if (mounted.current && response?.data?.data) {
                const res = response.data.data;
                // const reserve = {
                //     updated_at: res.reserve.updated_at
                // };
                setInput({ ...input, ...res, status });
            }
        } else {
            // 新規登録時(まだ書類レコードが存在していない場合)
            setInput({ ...input, status });
            alert(
                "ステータスの保存はまだ完了していません。\n「保存」ボタンより書類情報を保存してください。"
            );
            $(".js-modal-close").trigger("click"); // モーダルclose
        }
    };

    const optionPriceFilter = useMemo(() => {
        return optionPrices.filter(item =>
            input.participant_ids.includes(parseInt(item.participant_id, 10))
        );
    }, [input.participant_ids]);

    const hotelPriceFilter = useMemo(() => {
        return hotelPrices.filter(item =>
            input.participant_ids.includes(parseInt(item.participant_id, 10))
        );
    }, [input.participant_ids]);

    const airticketPriceFilter = useMemo(() => {
        return airticketPrices.filter(item =>
            input.participant_ids.includes(parseInt(item.participant_id, 10))
        );
    }, [input.participant_ids]);

    // パンクズリストindex部(催行済、予約状態を判別してリンクを出し分け)
    const IndexBreadcrumb = ({
        isDeparted,
        applicationStep,
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
                    {applicationStep ==
                        consts.application_step_list
                            .application_step_reserve && (
                        <a href={reserveIndexUrl}>予約管理</a>
                    )}
                    {applicationStep ==
                        consts.application_step_list.application_step_draft && (
                        <a href={reserveIndexUrl}>見積管理</a>
                    )}
                </li>
            );
        }
    };

    return (
        <>
            <div id="pageHead">
                <h1>
                    <span className="material-icons">description</span>
                    {applicationStep ===
                        consts.application_step_list.application_step_draft &&
                        "見積書"}
                    {applicationStep ===
                        consts.application_step_list.application_step_reserve &&
                        "予約確認書"}
                    {applicationStep ===
                        consts.application_step_list.application_step_draft &&
                        estimateNumber}
                    {applicationStep ===
                        consts.application_step_list.application_step_reserve &&
                        reserveNumber}
                    <span
                        className="status blue js-modal-open"
                        data-target="mdStatus"
                    >
                        {formSelects.statuses?.[input.status]}
                    </span>
                </h1>
                <div className="documentControl">
                    <ul>
                        <li>
                            <button
                                className="grayBtn"
                                onClick={e => {
                                    e.preventDefault();
                                    location.href = backUrl;
                                }}
                            >
                                戻る
                            </button>
                        </li>
                        <li>
                            <button
                                className={classNames("blueBtn", {
                                    loading: isSaving
                                })}
                                onClick={handleSave}
                                disabled={isSaving || isPdfSaving}
                            >
                                <span className="material-icons">save</span>保存
                            </button>
                        </li>
                        <li>
                            <button
                                className={classNames("blueBtn", {
                                    loading: isPdfSaving
                                })}
                                disabled={isSaving || isPdfSaving}
                                onClick={handlePdf}
                            >
                                <span className="material-icons">
                                    picture_as_pdf
                                </span>
                                PDF
                            </button>
                        </li>
                    </ul>
                </div>
                <ol className="breadCrumbs">
                    <IndexBreadcrumb
                        isDeparted={isDeparted}
                        applicationStep={applicationStep}
                        reserveIndexUrl={backUrl}
                        departedIndexUrl={consts?.departedIndexUrl}
                    />
                    <li>
                        {applicationStep ==
                            consts.application_step_list
                                .application_step_reserve && (
                            <a href={backUrl}>{`予約情報 ${reserveNumber}`}</a>
                        )}
                        {applicationStep ==
                            consts.application_step_list
                                .application_step_draft && (
                            <a href={backUrl}>{`見積情報 ${estimateNumber}`}</a>
                        )}
                    </li>
                    <li>
                        <span>
                            {applicationStep ===
                                consts.application_step_list
                                    .application_step_draft && "見積書"}
                            {applicationStep ===
                                consts.application_step_list
                                    .application_step_reserve && "予約確認書"}
                        </span>
                    </li>
                </ol>
            </div>

            {/**保存完了メッセージ */}
            <SuccessMessage message={saveMessage} />

            <div id="inputArea">
                <ul className="sideList documentSetting">
                    <li className="wd60 overflowX dragTable">
                        <div className="documentPreview">
                            <h2 className="blockTitle">
                                {documentSetting?.title}
                            </h2>
                            <div className="number">
                                <p>
                                    {documentSetting?.management_name &&
                                        `${documentSetting?.management_name}：`}
                                    {input?.control_number}
                                </p>
                                <p>発行日：{input?.issue_date}</p>
                            </div>
                            <div className="dcHead">
                                <div>
                                    {/**宛名ゾーン（個人向け） */}
                                    {documentSetting?.setting?.[
                                        DOCUMENT_QUOTE.DISPLAY_BLOCK
                                    ] &&
                                        documentSetting.setting[
                                            DOCUMENT_QUOTE.DISPLAY_BLOCK
                                        ].includes("宛名") &&
                                        input?.document_address?.type ===
                                            consts.person && (
                                            <PersonSuperscriptionPreviewArea
                                                commonSetting={
                                                    commonSetting?.setting
                                                }
                                                documentAddress={
                                                    input?.document_address
                                                }
                                                honorifics={
                                                    formSelects?.honorifics
                                                }
                                            />
                                        )}
                                    {/**宛名ゾーン（法人向け） */}
                                    {documentSetting?.setting?.[
                                        DOCUMENT_QUOTE.DISPLAY_BLOCK
                                    ] &&
                                        documentSetting.setting[
                                            DOCUMENT_QUOTE.DISPLAY_BLOCK
                                        ].includes("宛名") &&
                                        input?.document_address?.type ===
                                            consts.business && (
                                            <BusinessSuperscriptionPreviewArea
                                                commonSetting={
                                                    commonSetting?.setting
                                                }
                                                documentAddress={
                                                    input?.document_address
                                                }
                                                honorifics={
                                                    formSelects?.honorifics
                                                }
                                            />
                                        )}
                                    {/**予約情報。participantsは表示チェックがONになっているデータのみ渡す */}
                                    {documentSetting?.setting?.[
                                        DOCUMENT_QUOTE.DISPLAY_BLOCK
                                    ] &&
                                        documentSetting.setting[
                                            DOCUMENT_QUOTE.DISPLAY_BLOCK
                                        ].includes(
                                            "予約情報(件名・期間・参加者)"
                                        ) && (
                                            <ReserveInfoPreviewArea
                                                reserveSetting={
                                                    documentSetting.setting[
                                                        DOCUMENT_QUOTE
                                                            .RESERVATION_INFO
                                                    ]
                                                }
                                                name={input?.name}
                                                departureDate={
                                                    input?.departure_date
                                                }
                                                returnDate={input?.return_date}
                                                representative={
                                                    input?.representative
                                                }
                                                participants={formSelects.participants.filter(
                                                    item =>
                                                        input.participant_ids.includes(
                                                            parseInt(
                                                                item.id,
                                                                10
                                                            )
                                                        )
                                                )}
                                            />
                                        )}
                                </div>
                                <div className="dispCorp">
                                    {/**宛名ゾーン */}
                                    {documentSetting?.setting?.[
                                        DOCUMENT_QUOTE.DISPLAY_BLOCK
                                    ] &&
                                        documentSetting.setting[
                                            DOCUMENT_QUOTE.DISPLAY_BLOCK
                                        ].includes("自社情報") && (
                                            <OwnCompanyPreviewArea
                                                showSetting={
                                                    commonSetting?.setting?.[
                                                        DOCUMENT_COMMON
                                                            .COMPANY_INFO
                                                    ]
                                                }
                                                company={_.omit(
                                                    commonSetting,
                                                    "setting"
                                                )}
                                                manager={input?.manager}
                                            />
                                        )}
                                    <div className="dispStump">
                                        {/**検印欄 */}
                                        {documentSetting?.setting?.[
                                            DOCUMENT_QUOTE.DISPLAY_BLOCK
                                        ] &&
                                            documentSetting.setting[
                                                DOCUMENT_QUOTE.DISPLAY_BLOCK
                                            ].includes("検印欄") && (
                                                <SealPreviewArea
                                                    sealNumber={
                                                        documentSetting?.seal_number ??
                                                        0
                                                    }
                                                    sealItems={
                                                        documentSetting?.seal_items ??
                                                        []
                                                    }
                                                    sealWording={
                                                        documentSetting?.seal_wording
                                                    }
                                                />
                                            )}
                                    </div>
                                </div>
                            </div>
                            {/**案内文 */}
                            {documentSetting?.setting?.[
                                DOCUMENT_QUOTE.DISPLAY_BLOCK
                            ] &&
                                documentSetting.setting[
                                    DOCUMENT_QUOTE.DISPLAY_BLOCK
                                ].includes("案内文") &&
                                documentSetting?.information && (
                                    <p className="dispAnounce">
                                        <BrText
                                            text={documentSetting?.information}
                                        />
                                    </p>
                                )}
                            <div className="dispPrice">
                                {/**代金内訳ゾーン。他のコンポーネントと異なり表示・非表示の判定はコンポーネント内で行う（合計金額の計算が必要な為） */}
                                <BreakdownPricePreviewArea
                                    isShow={
                                        documentSetting?.setting?.[
                                            DOCUMENT_QUOTE.DISPLAY_BLOCK
                                        ] &&
                                        documentSetting.setting[
                                            DOCUMENT_QUOTE.DISPLAY_BLOCK
                                        ].includes("代金内訳")
                                    }
                                    isCanceled={isCanceled}
                                    optionPrices={optionPriceFilter}
                                    hotelPrices={hotelPriceFilter}
                                    airticketPrices={airticketPriceFilter}
                                    showSetting={
                                        documentSetting.setting[
                                            DOCUMENT_QUOTE.BREAKDOWN_PRICE
                                        ] ?? []
                                    }
                                    amountTotal={amountTotal}
                                    setAmountTotal={setAmountTotal}
                                />
                            </div>
                            <div className="dispSchedule">
                                {/**航空券情報ゾーン */}
                                {documentSetting?.setting?.[
                                    DOCUMENT_QUOTE.DISPLAY_BLOCK
                                ] &&
                                    documentSetting.setting[
                                        DOCUMENT_QUOTE.DISPLAY_BLOCK
                                    ].includes("航空券情報") &&
                                    Object.keys(airticketPrices).length > 0 && (
                                        <AirticketInfoPreviewArea
                                            AirticketInfo
                                            reserveSetting={
                                                documentSetting.setting[
                                                    DOCUMENT_QUOTE
                                                        .AIR_TICKET_INFO
                                                ]
                                            }
                                            airticketPrices={airticketPrices.filter(
                                                item =>
                                                    input.participant_ids.includes(
                                                        parseInt(
                                                            item.participant_id,
                                                            10
                                                        )
                                                    )
                                            )}
                                        />
                                    )}
                            </div>
                            <div className="dispHotel">
                                {/**宿泊施設情報 */}
                                {documentSetting?.setting?.[
                                    DOCUMENT_QUOTE.DISPLAY_BLOCK
                                ] &&
                                    documentSetting.setting[
                                        DOCUMENT_QUOTE.DISPLAY_BLOCK
                                    ].includes("ホテル情報") &&
                                    Object.keys(hotelInfo).length > 0 && (
                                        <HotelInfoPreviewArea
                                            hotelInfo={hotelInfo}
                                            participantIds={
                                                input.participant_ids
                                            }
                                        />
                                    )}
                            </div>
                            <div className="dispHotelInfo">
                                {/**宿泊施設連絡先 */}
                                {documentSetting?.setting?.[
                                    DOCUMENT_QUOTE.DISPLAY_BLOCK
                                ] &&
                                    documentSetting.setting[
                                        DOCUMENT_QUOTE.DISPLAY_BLOCK
                                    ].includes("ホテル連絡先") &&
                                    hotelContacts.length > 0 && (
                                        <HotelPreviewArea
                                            hotelContacts={hotelContacts}
                                            participantIds={
                                                input.participant_ids
                                            }
                                        />
                                    )}
                            </div>
                            {/**備考ゾーン */}
                            {documentSetting?.setting?.[
                                DOCUMENT_QUOTE.DISPLAY_BLOCK
                            ] &&
                                documentSetting.setting[
                                    DOCUMENT_QUOTE.DISPLAY_BLOCK
                                ].includes("備考") &&
                                documentSetting?.note && (
                                    <p className="dispEtc">
                                        <BrText text={documentSetting.note} />
                                    </p>
                                )}
                        </div>
                    </li>
                    <li className="wd40 mr00">
                        <div className="outputTag mt00">
                            <ul className="baseList">
                                <li>
                                    <h3>出力設定</h3>
                                </li>
                                <li>
                                    <span className="inputLabel">管理名称</span>
                                    <input
                                        type="text"
                                        name="management_name"
                                        value={
                                            documentSetting.management_name ??
                                            ""
                                        }
                                        onChange={handleDocumentInputChange}
                                        maxLength={32}
                                    />
                                </li>
                                <li>
                                    <span className="inputLabel">管理番号</span>
                                    <input
                                        type="text"
                                        name="control_number"
                                        value={input.control_number ?? ""}
                                        onChange={handleChange}
                                        maxLength={32}
                                    />
                                </li>
                                <li className="btBorder">
                                    <span className="inputLabel">発行日</span>
                                    <div className="calendar">
                                        <Flatpickr
                                            theme="airbnb"
                                            value={input.issue_date ?? ""}
                                            onChange={(date, dateStr) => {
                                                handleChange({
                                                    target: {
                                                        name: "issue_date",
                                                        value: dateStr
                                                    }
                                                });
                                            }}
                                            options={{
                                                dateFormat: "Y/m/d",
                                                locale: {
                                                    ...Japanese
                                                }
                                            }}
                                            render={(
                                                {
                                                    defaultValue,
                                                    value,
                                                    ...props
                                                },
                                                ref
                                            ) => {
                                                return (
                                                    <input
                                                        name="issue_date"
                                                        defaultValue={
                                                            value ?? ""
                                                        }
                                                        ref={ref}
                                                    />
                                                );
                                            }}
                                        />
                                    </div>
                                </li>
                                <li>
                                    <span className="inputLabel">
                                        テンプレート
                                    </span>
                                    <div className="selectBox">
                                        <select
                                            name="document_quote_id"
                                            value={
                                                input.document_quote_id ?? ""
                                            }
                                            onChange={
                                                handleDocumentSettingChange
                                            }
                                        >
                                            {formSelects.documentQuotes &&
                                                Object.keys(
                                                    formSelects.documentQuotes
                                                )
                                                    .sort((a, b) => {
                                                        return a - b;
                                                    })
                                                    .map((k, index) => (
                                                        <option
                                                            key={index}
                                                            value={k}
                                                        >
                                                            {
                                                                formSelects
                                                                    .documentQuotes[
                                                                    k
                                                                ]
                                                            }
                                                        </option>
                                                    ))}
                                        </select>
                                    </div>
                                </li>
                                <li>
                                    <span className="inputLabel">表題</span>
                                    <input
                                        type="text"
                                        name="title"
                                        value={documentSetting.title ?? ""}
                                        onChange={handleDocumentInputChange}
                                        maxLength={32}
                                    />
                                </li>
                                <li>
                                    <span className="inputLabel">案内文</span>
                                    <textarea
                                        cols="3"
                                        name="information"
                                        value={
                                            documentSetting.information ?? ""
                                        }
                                        onChange={handleDocumentInputChange}
                                    />
                                </li>
                                <li>
                                    <span className="inputLabel">備考</span>
                                    <textarea
                                        cols="3"
                                        name="note"
                                        value={documentSetting.note ?? ""}
                                        onChange={handleDocumentInputChange}
                                    />
                                </li>
                                <li>
                                    <span className="inputLabel">
                                        宛名/自社情報共通設定
                                    </span>
                                    <div className="selectBox">
                                        <select
                                            name="document_common_id"
                                            value={
                                                input.document_common_id ?? ""
                                            }
                                            onChange={
                                                handleDocumentCommonSettingChange
                                            }
                                        >
                                            {formSelects.documentCommons &&
                                                Object.keys(
                                                    formSelects.documentCommons
                                                )
                                                    .sort((a, b) => {
                                                        return a - b;
                                                    })
                                                    .map((k, index) => (
                                                        <option
                                                            key={index}
                                                            value={k}
                                                        >
                                                            {
                                                                formSelects
                                                                    .documentCommons[
                                                                    k
                                                                ]
                                                            }
                                                        </option>
                                                    ))}
                                        </select>
                                    </div>
                                </li>
                                {/**検印欄設定エリア */}
                                <SealSettingArea
                                    sealNumber={
                                        documentSetting.seal_number ?? 0
                                    }
                                    sealItems={documentSetting.seal_items ?? []}
                                    sealMaxNum={consts.sealMaxNum ?? 0}
                                    handleSelectChange={
                                        handleDocumentInputChange
                                    }
                                    handleInputChange={handleSealItemChange}
                                    disabled={
                                        !documentSetting?.setting?.[
                                            DOCUMENT_QUOTE.DISPLAY_BLOCK
                                        ] ||
                                        (documentSetting?.setting?.[
                                            DOCUMENT_QUOTE.DISPLAY_BLOCK
                                        ] &&
                                            !documentSetting.setting[
                                                DOCUMENT_QUOTE.DISPLAY_BLOCK
                                            ].includes("検印欄"))
                                    }
                                />
                                {/**個人顧客申込者input */}
                                {input?.document_address?.type ===
                                    consts.person && (
                                    <PersonDocumentAddressSettingArea
                                        documentAddress={
                                            input?.document_address
                                        }
                                        honorifics={formSelects.honorifics}
                                        onChange={handleDocumentAddressChange}
                                    />
                                )}
                                {/**法人顧客申込者input */}
                                {input?.document_address?.type ===
                                    consts.business && (
                                    <BusinessDocumentAddressSettingArea
                                        documentAddress={
                                            input?.document_address
                                        }
                                        honorifics={formSelects.honorifics}
                                        onChange={handleDocumentAddressChange}
                                    />
                                )}
                                <li className="mt40">
                                    <h3>予約情報</h3>
                                </li>
                                <li>
                                    <span className="inputLabel">旅行名</span>
                                    <input
                                        type="text"
                                        name="name"
                                        value={input.name ?? ""}
                                        onChange={handleChange}
                                        maxLength={100}
                                    />
                                </li>
                                <li>
                                    <span className="inputLabel">出発日</span>
                                    <div className="calendar">
                                        <Flatpickr
                                            theme="airbnb"
                                            value={input.departure_date ?? ""}
                                            onChange={(date, dateStr) => {
                                                handleChange({
                                                    target: {
                                                        name: "departure_date",
                                                        value: dateStr
                                                    }
                                                });
                                            }}
                                            options={{
                                                dateFormat: "Y/m/d",
                                                locale: {
                                                    ...Japanese
                                                }
                                            }}
                                            render={(
                                                {
                                                    defaultValue,
                                                    value,
                                                    ...props
                                                },
                                                ref
                                            ) => {
                                                return (
                                                    <input
                                                        name="departure_date"
                                                        defaultValue={
                                                            value ?? ""
                                                        }
                                                        ref={ref}
                                                    />
                                                );
                                            }}
                                        />
                                    </div>
                                </li>
                                <li>
                                    <span className="inputLabel">帰着日</span>
                                    <div className="calendar">
                                        <Flatpickr
                                            theme="airbnb"
                                            value={input.return_date ?? ""}
                                            onChange={(date, dateStr) => {
                                                handleChange({
                                                    target: {
                                                        name: "return_date",
                                                        value: dateStr
                                                    }
                                                });
                                            }}
                                            options={{
                                                dateFormat: "Y/m/d",
                                                locale: {
                                                    ...Japanese
                                                }
                                            }}
                                            render={(
                                                {
                                                    defaultValue,
                                                    value,
                                                    ...props
                                                },
                                                ref
                                            ) => {
                                                return (
                                                    <input
                                                        name="return_date"
                                                        defaultValue={
                                                            value ?? ""
                                                        }
                                                        ref={ref}
                                                    />
                                                );
                                            }}
                                        />
                                    </div>
                                </li>
                                <li>
                                    <span className="inputLabel">代表者</span>
                                    <input
                                        type="text"
                                        value={input.representative.name ?? ""}
                                        onChange={handleRepresentativeChange}
                                        maxLength={32}
                                    />
                                </li>
                                <li>
                                    {/**参加者チェックエリア */}
                                    <ParticipantCheckSettingArea
                                        participants={
                                            formSelects.participants ?? null
                                        }
                                        handleChange={handleParticipantChange}
                                        checkIds={input.participant_ids ?? []}
                                    />
                                </li>
                            </ul>
                            <ul className="documentOutput">
                                <li className="wd100">
                                    <h3 className="mb00">表示ブロック</h3>
                                    <ul className="sideList half mb30">
                                        {Object.keys(
                                            formSelects?.setting?.[
                                                DOCUMENT_QUOTE.DISPLAY_BLOCK
                                            ]
                                        ).map((key, index) => (
                                            <SettingCheckRow
                                                key={index}
                                                category={
                                                    DOCUMENT_QUOTE.DISPLAY_BLOCK
                                                }
                                                row={
                                                    formSelects.setting[
                                                        DOCUMENT_QUOTE
                                                            .DISPLAY_BLOCK
                                                    ][key]
                                                }
                                                index={index}
                                                prefix={"db"}
                                                setting={
                                                    documentSetting.setting ??
                                                    {}
                                                }
                                                handleChange={
                                                    handleShowSettingChange
                                                }
                                            />
                                        ))}
                                    </ul>
                                </li>
                                <li>
                                    <h3>予約情報</h3>
                                    <ul className="baseList">
                                        {Object.keys(
                                            formSelects?.setting?.[
                                                DOCUMENT_QUOTE.RESERVATION_INFO
                                            ]
                                        ).map((key, index) => (
                                            <SettingCheckRow
                                                key={index}
                                                category={
                                                    DOCUMENT_QUOTE.RESERVATION_INFO
                                                }
                                                row={
                                                    formSelects.setting[
                                                        DOCUMENT_QUOTE
                                                            .RESERVATION_INFO
                                                    ][key]
                                                }
                                                index={index}
                                                prefix={"ri"}
                                                setting={
                                                    documentSetting.setting ??
                                                    {}
                                                }
                                                handleChange={
                                                    handleShowSettingChange
                                                }
                                            />
                                        ))}
                                    </ul>
                                </li>
                                <li>
                                    <h3>航空券情報</h3>
                                    <ul>
                                        {Object.keys(
                                            formSelects?.setting?.[
                                                DOCUMENT_QUOTE.AIR_TICKET_INFO
                                            ]
                                        ).map((key, index) => (
                                            <SettingCheckRow
                                                key={index}
                                                category={
                                                    DOCUMENT_QUOTE.AIR_TICKET_INFO
                                                }
                                                row={
                                                    formSelects.setting[
                                                        DOCUMENT_QUOTE
                                                            .AIR_TICKET_INFO
                                                    ][key]
                                                }
                                                index={index}
                                                prefix={"ti"}
                                                setting={
                                                    documentSetting.setting ??
                                                    {}
                                                }
                                                handleChange={
                                                    handleShowSettingChange
                                                }
                                            />
                                        ))}
                                    </ul>
                                    <hr className="sepBorder" />
                                    <h3 className="mt20">代金内訳</h3>
                                    <ul className="mt00">
                                        {Object.keys(
                                            formSelects?.setting?.[
                                                DOCUMENT_QUOTE.BREAKDOWN_PRICE
                                            ]
                                        ).map((key, index) => (
                                            <SettingCheckRow
                                                key={index}
                                                category={
                                                    DOCUMENT_QUOTE.BREAKDOWN_PRICE
                                                }
                                                row={
                                                    formSelects.setting[
                                                        DOCUMENT_QUOTE
                                                            .BREAKDOWN_PRICE
                                                    ][key]
                                                }
                                                index={index}
                                                prefix={"bp"}
                                                setting={
                                                    documentSetting.setting ??
                                                    {}
                                                }
                                                handleChange={
                                                    handleShowSettingChange
                                                }
                                            />
                                        ))}
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </div>
            {/*ステータス変更モーダル */}
            <StatusUpdateModal
                id="mdStatus"
                status={status}
                setStatus={setStatus}
                statuses={formSelects.statuses}
                handleUpdate={handleUpdateStatus}
                isUpdating={isStatusUpdating}
            />
        </>
    );
};

const Element = document.getElementById("reserveConfirmArea");
if (Element) {
    const jsVars = Element.getAttribute("jsVars");
    const parsedJsVars = jsVars && JSON.parse(jsVars);
    const reception = Element.getAttribute("reception");
    const applicationStep = Element.getAttribute("applicationStep");
    const defaultValue = Element.getAttribute("defaultValue");
    const parsedDefaultValue = defaultValue && JSON.parse(defaultValue);
    const documentCommonSetting = Element.getAttribute("documentCommonSetting");
    const parsedDocumentCommonSetting =
        documentCommonSetting && JSON.parse(documentCommonSetting);
    const documentSetting = Element.getAttribute("documentSetting");
    const parsedDocumentSetting =
        documentSetting && JSON.parse(documentSetting);
    const itineraryNumber = Element.getAttribute("itineraryNumber");
    const reserveNumber = Element.getAttribute("reserveNumber");
    const estimateNumber = Element.getAttribute("estimateNumber");
    const formSelects = Element.getAttribute("formSelects");
    const parsedFormSelects = formSelects && JSON.parse(formSelects);
    const hotelContacts = Element.getAttribute("hotelContacts");
    const parsedHotelContacts = hotelContacts && JSON.parse(hotelContacts);
    const hotelInfo = Element.getAttribute("hotelInfo");
    const parsedHotelInfo = hotelInfo && JSON.parse(hotelInfo);
    const optionPrices = Element.getAttribute("optionPrices");
    const parsedOptionPrices = optionPrices && JSON.parse(optionPrices);
    const hotelPrices = Element.getAttribute("hotelPrices");
    const parsedHotelPrices = hotelPrices && JSON.parse(hotelPrices);
    const airticketPrices = Element.getAttribute("airticketPrices");
    const parsedAirticketPrices =
        airticketPrices && JSON.parse(airticketPrices);
    const consts = Element.getAttribute("consts");
    const parsedConsts = consts && JSON.parse(consts);
    const isDeparted = Element.getAttribute("isDeparted");
    const isCanceled = Element.getAttribute("isCanceled");

    render(
        <ConstApp jsVars={parsedJsVars}>
            <ReserveConfirmArea
                reception={reception}
                applicationStep={applicationStep}
                reserveNumber={reserveNumber}
                estimateNumber={estimateNumber}
                itineraryNumber={itineraryNumber}
                defaultValue={parsedDefaultValue}
                documentCommonSetting={parsedDocumentCommonSetting}
                documentQuoteSetting={parsedDocumentSetting}
                formSelects={parsedFormSelects}
                hotelContacts={parsedHotelContacts}
                hotelInfo={parsedHotelInfo}
                airticketPrices={parsedAirticketPrices}
                hotelPrices={parsedHotelPrices}
                optionPrices={parsedOptionPrices}
                consts={parsedConsts}
                isDeparted={isDeparted}
                isCanceled={isCanceled}
            />
        </ConstApp>,
        document.getElementById("reserveConfirmArea")
    );
}
