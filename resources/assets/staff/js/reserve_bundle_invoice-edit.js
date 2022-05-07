import React, { useState, useContext, useMemo, useEffect } from "react";
import ConstApp from "./components/ConstApp";
import { ConstContext } from "./components/ConstApp";
import { render } from "react-dom";
import {
    DOCUMENT_COMMON,
    DOCUMENT_BUNDLE_INVOICE,
    DOCUMENT_REQUEST_ALL
} from "./constants";
import { useMountedRef } from "../../hooks/useMountedRef";
import _ from "lodash";
import OnlyNumberInput from "./components/OnlyNumberInput";
import BrText from "./BrText";
import SealSettingArea from "./components/BusinessForm/SealSettingArea";
import SettingCheckRow from "./components/BusinessForm/SettingCheckRow";
import classNames from "classnames";
import SuccessMessage from "./components/SuccessMessage";
// flatpickr
import "flatpickr/dist/themes/airbnb.css";
import { Japanese } from "flatpickr/dist/l10n/ja.js";
import Flatpickr from "react-flatpickr";
import SealPreviewArea from "./components/BusinessForm/SealPreviewArea";
import OwnCompanyPreviewArea from "./components/BusinessForm/OwnCompanyPreviewArea";
import CompanySuperscriptionPreviewArea from "./components/BusinessForm/CompanySuperscriptionPreviewArea";
import PartnerManagerCheckSettingArea from "./components/BusinessForm/PartnerManagerCheckSettingArea";
import InvoiceInfoPreviewArea from "./components/BusinessForm/InvoiceInfoPreviewArea";
import ReserveBreakdownPricePreviewArea from "./components/BusinessForm/ReserveBreakdownPricePreviewArea";
import StatusUpdateModal from "./components/BusinessForm/StatusUpdateModal";
import ErrorMessage from "./components/ErrorMessage";

const PARTNER_MANAGER_CHANGE_ERROR = "PARTNER_MANAGER_CHANGE_ERROR";

const BundleInvoiceArea = ({
    reserveBundleInvoiceId,
    defaultValue,
    documentRequestAllSetting,
    documentCommonSetting,
    formSelects,
    reservePrices,
    reserveCancelInfo,
    consts
}) => {
    const { agencyAccount } = useContext(ConstContext);

    const mounted = useMountedRef(); // マウント・アンマウント制御

    const [input, setInput] = useState({ ...defaultValue });

    const [saveMessage, setSaveMessage] = useState(""); // 保存完了メッセージ

    const [errorObj, setErrorObj] = useState({}); // エラー文言を保持

    const [documentSetting, setDocumentSetting] = useState({
        ...documentRequestAllSetting
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

    // 全ての担当者がチェックされていない場合はエラー文言をセット
    const checkPartnerManager = partnerManagerIds => {
        if (!mounted.current) {
            return;
        }
        if (formSelects.partnerManagers.length != partnerManagerIds.length) {
            setErrorObj({
                PARTNER_MANAGER_CHANGE_ERROR:
                    "御社担当欄でチェックされていない担当者がいます。"
            });
        } else {
            setErrorObj({ PARTNER_MANAGER_CHANGE_ERROR: null });
        }
    };

    // 担当者が全てチェックされていない場合はエラーを表示(→途中で追加された担当者はそのままではチェックONの対象になっていないので請求対象になっていないことを改めて認識してもらうため)
    useEffect(() => {
        checkPartnerManager(input.partner_manager_ids ?? []); // 担当者chekboxの状態をチェック
    }, []);

    // 担当者チェックOn/Off制御
    const handlePartnerManagerChange = e => {
        let partner_manager_ids = input.partner_manager_ids;

        if (partner_manager_ids.includes(parseInt(e.target.value, 10))) {
            partner_manager_ids = partner_manager_ids.filter(
                id => id != e.target.value
            );
        } else {
            partner_manager_ids = [
                ...partner_manager_ids,
                parseInt(e.target.value, 10)
            ];
        }
        setInput({ ...input, partner_manager_ids });

        checkPartnerManager(partner_manager_ids ?? []); // 担当者chekboxの状態をチェック
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
            .get(`/api/${agencyAccount}/document_request_all/${value}`)
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
                setting[DOCUMENT_BUNDLE_INVOICE.DISPLAY_BLOCK].push(
                    DOCUMENT_BUNDLE_INVOICE.SEAL_LABEL
                );
                data.setting = setting;
            }
            setDocumentSetting({ ..._.omit(data, "document_common") });
            setCommonSetting({ ...data.document_common });
        }
    };

    // 保存処理
    const handleSave = async e => {
        e.preventDefault();

        if (!input.document_request_all_id) {
            alert("テンプレートが設定されていません。");
            return;
        }

        if (!mounted.current) return;
        if (isSaving) return;

        setIsSaving(true);

        {
            /**処理完了のflashメッセージをセット（set_message=1） */
        }
        const response = await axios
            .post(
                `/api/${agencyAccount}/management/bundle_invoice/${reserveBundleInvoiceId}`,
                {
                    ...input,
                    amount_total: amountTotal,
                    document_common_setting: commonSetting,
                    document_setting: _.omit(
                        documentSetting,
                        "document_common"
                    ), // 書類設定。共通設定はカット
                    reserve_prices: reservePrices,
                    reserve_cancel_info: reserveCancelInfo,
                    // set_message: 1,
                    _method: "put"
                }
            )
            .finally(() => {
                $(".js-modal-close").trigger("click"); // モーダルclose
                setTimeout(function() {
                    if (mounted.current) {
                        setIsSaving(false);
                    }
                }, 3000);
            });
        if (mounted.current && response?.data?.data) {
            const res = response.data.data;
            // input.id = res.id; // 新規保存後はIDが必要なので取得
            setInput({
                ...input,
                ...res
            }); // 更新日時をセットする

            // メッセージエリアをslideDown(表示状態)にした後でメッセージをセット
            // $("#successMessage .closeIcon")
            //     .parent()
            //     .slideDown();
            setSaveMessage("請求書データを保存しました");

            // ↓ページ遷移すると慌ただしのでひとまず遷移ナシに
            // location.href = document.referrer
            //     ? document.referrer
            //     : `/${agencyAccount}/management/invoice/index`;
            // return;
        }
    };

    // 保存＆PDF出力処理
    const handlePdf = async e => {
        e.preventDefault();

        if (!input.document_request_all_id) {
            alert("テンプレートが設定されていません。");
            return;
        }

        if (!mounted.current) return;
        if (isPdfSaving) return;

        setIsPdfSaving(true);

        const response = await axios
            .post(
                `/api/${agencyAccount}/management/bundle_invoice/${reserveBundleInvoiceId}`,
                {
                    ...input,
                    amount_total: amountTotal,
                    document_common_setting: commonSetting,
                    document_setting: _.omit(
                        documentSetting,
                        "document_common"
                    ), // 書類設定。共通設定はカット
                    reserve_prices: reservePrices,
                    reserve_cancel_info: reserveCancelInfo,
                    // set_message: 1,
                    create_pdf: 1,
                    _method: "put"
                }
            )
            .finally(() => {
                if (mounted.current) {
                    setIsPdfSaving(false);
                }
            });
        if (mounted.current && response?.data?.data) {
            const res = response.data.data;
            // input.id = res.id; // 新規保存後はIDが必要なので取得
            setInput({
                ...input,
                ...res
            }); // 更新日時をセットする

            // PDFダウンロード
            window.open(
                `/${agencyAccount}/pdf/document/request/${res.pdf.id}`,
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
                    `/api/${agencyAccount}/bundle_invoice/${input?.id}/status`,
                    {
                        status: status,
                        updated_at: input?.updated_at,
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
                // input.updated_at = res.updated_at;
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

    const reservePriceFilter = useMemo(() => {
        return _.pick(reservePrices, input.partner_manager_ids);
    }, [input.partner_manager_ids]);

    return (
        <>
            <div id="pageHead">
                <h1>
                    <span className="material-icons">description</span>
                    一括請求書 {input?.user_bundle_invoice_number}
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
                                    location.href = document.referrer;
                                }}
                            >
                                戻る
                            </button>
                        </li>
                        <li>
                            <button
                                className="blueBtn"
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
                    <li>
                        <a href={`/${agencyAccount}/management/invoice/index`}>
                            請求管理
                        </a>
                    </li>
                    <li>
                        <span>
                            一括請求書 {input?.user_bundle_invoice_number}
                        </span>
                    </li>
                </ol>
            </div>

            {/**保存完了メッセージ */}
            <SuccessMessage message={saveMessage} setMessage={setSaveMessage} />

            <ErrorMessage errorObj={errorObj} />

            <div id="inputArea">
                <ul className="sideList documentSetting">
                    <li className="wd60 overflowX dragTable">
                        <div className="documentPreview">
                            <h2 className="blockTitle">
                                {documentSetting.title ?? ""}
                            </h2>
                            <div className="number">
                                <p>
                                    請求番号：
                                    {input.user_bundle_invoice_number ?? ""}
                                </p>
                                <p>発行日：{input.issue_date ?? ""}</p>
                            </div>
                            <div className="dcHead">
                                <div>
                                    {/**宛名ゾーン（会社用） */}
                                    {documentSetting?.setting?.[
                                        DOCUMENT_REQUEST_ALL.DISPLAY_BLOCK
                                    ] &&
                                        documentSetting.setting[
                                            DOCUMENT_REQUEST_ALL.DISPLAY_BLOCK
                                        ].includes("宛名") &&
                                        input?.document_address?.type ===
                                            consts.business && (
                                            <CompanySuperscriptionPreviewArea
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
                                        DOCUMENT_REQUEST_ALL.DISPLAY_BLOCK
                                    ] &&
                                        documentSetting.setting[
                                            DOCUMENT_REQUEST_ALL.DISPLAY_BLOCK
                                        ].includes(
                                            "予約情報(件名・期間・担当者)"
                                        ) && (
                                            <InvoiceInfoPreviewArea
                                                reserveSetting={
                                                    documentSetting.setting[
                                                        DOCUMENT_REQUEST_ALL
                                                            .RESERVATION_INFO
                                                    ]
                                                }
                                                name={input?.name}
                                                periodFrom={input?.period_from}
                                                periodTo={input?.period_to}
                                                partnerManagers={formSelects.partnerManagers.filter(
                                                    item =>
                                                        input.partner_manager_ids.includes(
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
                                        DOCUMENT_REQUEST_ALL.DISPLAY_BLOCK
                                    ] &&
                                        documentSetting.setting[
                                            DOCUMENT_REQUEST_ALL.DISPLAY_BLOCK
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
                                            DOCUMENT_REQUEST_ALL.DISPLAY_BLOCK
                                        ] &&
                                            documentSetting.setting[
                                                DOCUMENT_REQUEST_ALL
                                                    .DISPLAY_BLOCK
                                            ].includes("検印欄") && (
                                                <SealPreviewArea
                                                    sealNumber={
                                                        documentSetting.seal_number ??
                                                        0
                                                    }
                                                    sealItems={
                                                        documentSetting.seal_items ??
                                                        []
                                                    }
                                                    sealWording={
                                                        documentSetting.seal_wording ??
                                                        ""
                                                    }
                                                />
                                            )}
                                    </div>
                                </div>
                            </div>
                            {/**案内文 */}
                            {documentSetting?.setting?.[
                                DOCUMENT_REQUEST_ALL.DISPLAY_BLOCK
                            ] &&
                                documentSetting.setting[
                                    DOCUMENT_REQUEST_ALL.DISPLAY_BLOCK
                                ].includes("案内文") &&
                                documentSetting?.information && (
                                    <p className="dispAnounce">
                                        <BrText
                                            text={documentSetting.information}
                                        />
                                    </p>
                                )}
                            <div className="dispTotalPrice">
                                <dl>
                                    <dt>ご請求金額</dt>
                                    <dd>￥{amountTotal.toLocaleString()}</dd>
                                    <dt>お支払期限</dt>
                                    <dd>{input?.payment_deadline}</dd>
                                </dl>
                            </div>

                            {/**お振込先 */}
                            {documentSetting?.setting?.[
                                DOCUMENT_REQUEST_ALL.DISPLAY_BLOCK
                            ] &&
                                documentSetting.setting[
                                    DOCUMENT_REQUEST_ALL.DISPLAY_BLOCK
                                ].includes("振込先") &&
                                documentSetting?.account_payable && (
                                    <div className="dispBank">
                                        <h3>お振込先</h3>
                                        <BrText
                                            text={
                                                documentSetting.account_payable
                                            }
                                        />
                                    </div>
                                )}
                            {/**備考ゾーン */}
                            <p className="dispEtc mb20">
                                {documentSetting?.setting?.[
                                    DOCUMENT_REQUEST_ALL.DISPLAY_BLOCK
                                ] &&
                                    documentSetting.setting[
                                        DOCUMENT_REQUEST_ALL.DISPLAY_BLOCK
                                    ].includes("備考") &&
                                    documentSetting?.note && (
                                        <BrText text={documentSetting.note} />
                                    )}
                            </p>
                            <div className="dispPrice">
                                {/**代金内訳ゾーン。他のコンポーネントと異なり表示・非表示の判定はコンポーネント内で行う（合計金額の計算が必要な為） */}
                                <ReserveBreakdownPricePreviewArea
                                    isShow={
                                        documentSetting?.setting?.[
                                            DOCUMENT_REQUEST_ALL.DISPLAY_BLOCK
                                        ] &&
                                        documentSetting.setting[
                                            DOCUMENT_REQUEST_ALL.DISPLAY_BLOCK
                                        ].includes("代金内訳")
                                    }
                                    reservePrices={reservePriceFilter}
                                    showSetting={
                                        documentSetting.setting[
                                            DOCUMENT_REQUEST_ALL.BREAKDOWN_PRICE
                                        ] ?? []
                                    }
                                    reserveCancelInfo={reserveCancelInfo}
                                    amountTotal={amountTotal}
                                    setAmountTotal={setAmountTotal}
                                    partnerManagers={formSelects.partnerManagers.filter(
                                        item =>
                                            input.partner_manager_ids.includes(
                                                parseInt(item.id, 10)
                                            )
                                    )}
                                />
                            </div>
                        </div>
                    </li>
                    <li className="wd40 mr00">
                        <div className="outputTag mt00">
                            <ul className="baseList">
                                <li>
                                    <h3>出力設定</h3>
                                </li>
                                <li>
                                    <span className="inputLabel">請求番号</span>
                                    <input
                                        type="text"
                                        name="user_bundle_invoice_number"
                                        value={
                                            input.user_bundle_invoice_number ??
                                            ""
                                        }
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
                                <li className="btBorder">
                                    <span className="inputLabel">支払期限</span>
                                    <div className="calendar">
                                        <Flatpickr
                                            theme="airbnb"
                                            value={input.payment_deadline ?? ""}
                                            onChange={(date, dateStr) => {
                                                handleChange({
                                                    target: {
                                                        name:
                                                            "payment_deadline",
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
                                                        name="payment_deadline"
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
                                            name="document_request_all_id"
                                            value={
                                                input?.document_request_all_id ??
                                                ""
                                            }
                                            onChange={
                                                handleDocumentSettingChange
                                            }
                                        >
                                            {formSelects.documentRequestAlls &&
                                                Object.keys(
                                                    formSelects.documentRequestAlls
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
                                                                    .documentRequestAlls[
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
                                    <span className="inputLabel">振込先</span>
                                    <textarea
                                        cols="3"
                                        name="account_payable"
                                        value={
                                            documentSetting.account_payable ??
                                            ""
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
                                {/**検印欄 */}
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
                                            DOCUMENT_REQUEST_ALL.DISPLAY_BLOCK
                                        ] ||
                                        (documentSetting?.setting?.[
                                            DOCUMENT_REQUEST_ALL.DISPLAY_BLOCK
                                        ] &&
                                            !documentSetting.setting[
                                                DOCUMENT_REQUEST_ALL
                                                    .DISPLAY_BLOCK
                                            ].includes("検印欄"))
                                    }
                                />
                                <li className="mt40">
                                    <h3>宛名(顧客情報)</h3>
                                </li>
                                <li>
                                    <span className="inputLabel">社名</span>
                                    <div className="inputSelectSet">
                                        <input
                                            type="text"
                                            name="company_name"
                                            className="wd100"
                                            value={
                                                input.document_address
                                                    ?.company_name ?? ""
                                            }
                                            onChange={
                                                handleDocumentAddressChange
                                            }
                                            maxLength={50}
                                        />
                                    </div>
                                </li>
                                <li>
                                    <span className="inputLabel">郵便番号</span>
                                    <OnlyNumberInput
                                        type="text"
                                        name="zip_code"
                                        maxLength={7}
                                        value={
                                            input.document_address?.zip_code ??
                                            ""
                                        }
                                        handleChange={
                                            handleDocumentAddressChange
                                        }
                                    />
                                </li>
                                <li>
                                    <span className="inputLabel">都道府県</span>
                                    <input
                                        type="text"
                                        name="prefecture"
                                        value={
                                            input.document_address
                                                ?.prefecture ?? ""
                                        }
                                        onChange={handleDocumentAddressChange}
                                        maxLength={12}
                                    />
                                </li>
                                <li>
                                    <span className="inputLabel">住所</span>
                                    <input
                                        type="text"
                                        name="address1"
                                        value={
                                            input.document_address?.address1 ??
                                            ""
                                        }
                                        onChange={handleDocumentAddressChange}
                                        maxLength={100}
                                    />
                                </li>
                                <li>
                                    <span className="inputLabel">
                                        ビル・建物名
                                    </span>
                                    <input
                                        type="text"
                                        name="address2"
                                        value={
                                            input.document_address?.address2 ??
                                            ""
                                        }
                                        onChange={handleDocumentAddressChange}
                                        maxLength={100}
                                    />
                                </li>
                                <li className="mt40">
                                    <h3>予約情報</h3>
                                </li>
                                <li>
                                    <span className="inputLabel">件名</span>
                                    <input
                                        type="text"
                                        name="name"
                                        value={input.name ?? ""}
                                        onChange={handleChange}
                                        maxLength={100}
                                    />
                                </li>
                                <li>
                                    <span className="inputLabel">期間</span>
                                    <ul className="sideList half">
                                        <li>
                                            <div className="calendar">
                                                <Flatpickr
                                                    theme="airbnb"
                                                    value={
                                                        input.period_from ?? ""
                                                    }
                                                    onChange={(
                                                        date,
                                                        dateStr
                                                    ) => {
                                                        handleChange({
                                                            target: {
                                                                name:
                                                                    "period_from",
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
                                                                name="period_from"
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
                                        <li className="mt00">
                                            <div className="calendar">
                                                <Flatpickr
                                                    theme="airbnb"
                                                    value={
                                                        input.period_to ?? ""
                                                    }
                                                    onChange={(
                                                        date,
                                                        dateStr
                                                    ) => {
                                                        handleChange({
                                                            target: {
                                                                name:
                                                                    "period_to",
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
                                                                name="period_to"
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
                                    </ul>
                                </li>
                                <li>
                                    {/**担当者チェックエリア */}
                                    <PartnerManagerCheckSettingArea
                                        partnerManagers={
                                            formSelects.partnerManagers ?? null
                                        }
                                        handleChange={
                                            handlePartnerManagerChange
                                        }
                                        checkIds={
                                            input.partner_manager_ids ?? []
                                        }
                                    />
                                </li>
                            </ul>
                            <ul className="documentOutput">
                                <li className="wd100">
                                    <h3 className="mb00">表示ブロック</h3>
                                    <ul className="sideList half mb30">
                                        {Object.keys(
                                            formSelects?.setting?.[
                                                DOCUMENT_REQUEST_ALL
                                                    .DISPLAY_BLOCK
                                            ]
                                        ).map((key, index) => (
                                            <SettingCheckRow
                                                key={index}
                                                category={
                                                    DOCUMENT_REQUEST_ALL.DISPLAY_BLOCK
                                                }
                                                row={
                                                    formSelects.setting[
                                                        DOCUMENT_REQUEST_ALL
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
                                                DOCUMENT_REQUEST_ALL
                                                    .RESERVATION_INFO
                                            ]
                                        ).map((key, index) => (
                                            <SettingCheckRow
                                                key={index}
                                                category={
                                                    DOCUMENT_REQUEST_ALL.RESERVATION_INFO
                                                }
                                                row={
                                                    formSelects.setting[
                                                        DOCUMENT_REQUEST_ALL
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
                                    <h3 className="mt20">代金内訳</h3>
                                    <ul className="mt00">
                                        {Object.keys(
                                            formSelects?.setting?.[
                                                DOCUMENT_REQUEST_ALL
                                                    .BREAKDOWN_PRICE
                                            ]
                                        ).map((key, index) => (
                                            <SettingCheckRow
                                                key={index}
                                                category={
                                                    DOCUMENT_REQUEST_ALL.BREAKDOWN_PRICE
                                                }
                                                row={
                                                    formSelects.setting[
                                                        DOCUMENT_REQUEST_ALL
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

const Element = document.getElementById("bundleInvoiceArea");
if (Element) {
    const jsVars = Element.getAttribute("jsVars");
    const parsedJsVars = jsVars && JSON.parse(jsVars);
    const reserveBundleInvoiceId = Element.getAttribute(
        "reserveBundleInvoiceId"
    );
    const defaultValue = Element.getAttribute("defaultValue");
    const parsedDefaultValue = defaultValue && JSON.parse(defaultValue);
    const documentCommonSetting = Element.getAttribute("documentCommonSetting");
    const parsedDocumentCommonSetting =
        documentCommonSetting && JSON.parse(documentCommonSetting);
    const documentSetting = Element.getAttribute("documentSetting");
    const parsedDocumentSetting =
        documentSetting && JSON.parse(documentSetting);
    const formSelects = Element.getAttribute("formSelects");
    const parsedFormSelects = formSelects && JSON.parse(formSelects);
    const reservePrices = Element.getAttribute("reservePrices");
    const parsedReservePrices = reservePrices && JSON.parse(reservePrices);
    const reserveCancelInfo = Element.getAttribute("reserveCancelInfo");
    const parsedReserveCancelInfo =
        reserveCancelInfo && JSON.parse(reserveCancelInfo);
    const consts = Element.getAttribute("consts");
    const parsedConsts = consts && JSON.parse(consts);

    render(
        <ConstApp jsVars={parsedJsVars}>
            <BundleInvoiceArea
                reserveBundleInvoiceId={reserveBundleInvoiceId}
                defaultValue={parsedDefaultValue}
                documentRequestAllSetting={parsedDocumentSetting}
                documentCommonSetting={parsedDocumentCommonSetting}
                formSelects={parsedFormSelects}
                reservePrices={parsedReservePrices}
                reserveCancelInfo={parsedReserveCancelInfo}
                consts={parsedConsts}
            />
        </ConstApp>,
        document.getElementById("bundleInvoiceArea")
    );
}
