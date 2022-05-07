import React, { useState, useContext } from "react";
import ConstApp from "./components/ConstApp";
import { ConstContext } from "./components/ConstApp";
import { render } from "react-dom";
import { useMountedRef } from "../../hooks/useMountedRef";
import _ from "lodash";
import BrText from "./BrText";
import StatusModal from "./components/BusinessForm/StatusModal";
import PersonDocumentAddressSettingArea from "./components/BusinessForm/Receipt/PersonDocumentAddressSettingArea";
import BusinessDocumentAddressSettingArea from "./components/BusinessForm/Receipt/BusinessDocumentAddressSettingArea";
import classNames from "classnames";
// flatpickr
import "flatpickr/dist/themes/airbnb.css";
import { Japanese } from "flatpickr/dist/l10n/ja.js";
import Flatpickr from "react-flatpickr";
import OwnCompanyPreviewArea from "./components/BusinessForm/Receipt/OwnCompanyPreviewArea";
import PersonSuperscriptionPreviewArea from "./components/BusinessForm/Receipt/PersonSuperscriptionPreviewArea";
import BusinessSuperscriptionPreviewArea from "./components/BusinessForm/Receipt/BusinessSuperscriptionPreviewArea";
import OnlyNumberInput from "./components/OnlyNumberInput";
import SuccessMessage from "./components/SuccessMessage";
import StatusUpdateModal from "./components/BusinessForm/StatusUpdateModal";

const ReserveReceiptArea = ({
    reserveNumber,
    maximumAmount,
    reception,
    defaultValue,
    documentReceiptSetting,
    documentCommonSetting,
    formSelects,
    consts,
    isDeparted,
    isCanceled
}) => {
    const { agencyAccount, receptionTypes } = useContext(ConstContext);

    const mounted = useMountedRef(); // マウント・アンマウント制御

    const [input, setInput] = useState({ ...defaultValue });

    const [saveMessage, setSaveMessage] = useState(""); // 保存完了メッセージ

    const [documentSetting, setDocumentSetting] = useState({
        ...documentReceiptSetting
    }); // 書類設定
    const [commonSetting, setCommonSetting] = useState({
        ...documentCommonSetting
    }); // 共通設定

    // ステータス変更用の値。defaultValueから初期値をセット
    const [status, setStatus] = useState(defaultValue.status);

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
            .get(`/api/${agencyAccount}/document_receipt/${value}`)
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

            setDocumentSetting({ ..._.omit(data, "document_common") });
            setCommonSetting({ ...data.document_common });
        }
    };

    // 保存処理
    const handleSave = async e => {
        e.preventDefault();

        if (!input.document_receipt_id) {
            alert("テンプレートが設定されていません。");
            return;
        }

        // 上限金額を超えている場合は警告
        if (
            maximumAmount < (input.receipt_amount ?? 0) &&
            !confirm(
                "領収金額が設定可能上限金額を超えています。このまま保存しますか？"
            )
        ) {
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
                `/api/${agencyAccount}/estimate/${reception}/reserve/${reserveNumber}/receipt`,
                {
                    ...input,
                    document_common_setting: commonSetting,
                    document_setting: _.omit(
                        documentSetting,
                        "document_common"
                    ), // 書類設定。共通設定はカット
                    is_canceled: isCanceled == 1 ? 1 : 0,
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
            // const reserve = {
            //     updated_at: res.reserve.updated_at
            // };
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
            //     : `/${agencyAccount}/estimates/${reception}/reserve/${reserveNumber}`;
            // return;
        }
    };

    // 保存＆PDF出力処理
    const handlePdf = async e => {
        e.preventDefault();

        if (!input.document_receipt_id) {
            alert("テンプレートが設定されていません。");
            return;
        }

        // 上限金額を超えている場合は警告
        if (
            maximumAmount < (input.receipt_amount ?? 0) &&
            !confirm(
                "領収金額が設定可能上限金額を超えています。このまま保存しますか？"
            )
        ) {
            return;
        }

        if (!mounted.current) return;
        if (isPdfSaving) return;

        setIsPdfSaving(true);

        const response = await axios
            .post(
                `/api/${agencyAccount}/estimate/${reception}/reserve/${reserveNumber}/receipt`,
                {
                    ...input,
                    document_common_setting: commonSetting,
                    document_setting: _.omit(
                        documentSetting,
                        "document_common"
                    ), // 書類設定。共通設定はカット
                    is_canceled: isCanceled,
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
            // const reserve = {
            //     updated_at: res.reserve.updated_at
            // };
            setInput({
                ...input,
                ...res
            }); // 更新日時をセットする

            // PDFダウンロード
            window.open(
                `/${agencyAccount}/pdf/document/receipt/${res.pdf.id}`,
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
                .post(`/api/${agencyAccount}/receipt/${input?.id}/status`, {
                    status: status,
                    reserve: { updated_at: input?.reserve?.updated_at },
                    _method: "put"
                })
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
    // // ステータス更新
    // const handleUpdateStatus = async () => {
    //     $(".js-modal-close").trigger("click"); // モーダルclose
    //     if (mounted.current) {
    //         setInput({ ...input, status });
    //     }
    // };

    const IndexBreadcrumb = ({
        isDeparted,
        reception,
        reserveIndexUrl,
        departedIndexUrl
    }) => {
        if (isDeparted) {
            return (
                <li>
                    <a href={departedIndexUrl}>催行済み一覧</a>
                </li>
            );
        } else {
            return (
                <li>
                    <a href={reserveIndexUrl ?? ""}>
                        {receptionTypes.web == reception && "WEB"}
                        予約管理
                    </a>
                </li>
            );
        }
    };

    return (
        <>
            <div id="pageHead">
                <h1>
                    <span className="material-icons">description</span>領収書
                    {input?.user_receipt_number}
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
                        reception={reception}
                        reserveIndexUrl={consts?.reserveUrl}
                        departedIndexUrl={consts?.departedIndexUrl}
                    />
                    <li>
                        <a href={consts?.reserveUrl ?? ""}>
                            予約情報 {reserveNumber}
                        </a>
                    </li>
                    <li>
                        <span>領収書</span>
                    </li>
                </ol>
            </div>

            {/**保存完了メッセージ */}
            <SuccessMessage message={saveMessage} setMessage={setSaveMessage} />

            <div id="inputArea">
                <ul className="sideList documentSetting">
                    <li className="wd60 overflowX dragTable">
                        <div className="documentPreview">
                            <h2 className="blockTitle">
                                {documentSetting.title ?? ""}
                            </h2>
                            <div className="number">
                                <p>
                                    領収書番号：
                                    {input.user_receipt_number ?? ""}
                                </p>
                                <p>発行日：{input.issue_date ?? ""}</p>
                            </div>
                            <div className="dcHead">
                                <div>
                                    <p className="dispReceiptName">
                                        {input?.document_address?.type ===
                                            consts.person && (
                                            <PersonSuperscriptionPreviewArea
                                                documentAddress={
                                                    input?.document_address
                                                }
                                                honorifics={
                                                    formSelects?.honorifics
                                                }
                                            />
                                        )}
                                        {input?.document_address?.type ===
                                            consts.business && (
                                            <BusinessSuperscriptionPreviewArea
                                                documentAddress={
                                                    input?.document_address
                                                }
                                                honorifics={
                                                    formSelects?.honorifics
                                                }
                                            />
                                        )}
                                    </p>
                                </div>
                                <OwnCompanyPreviewArea
                                    company={_.omit(commonSetting, "setting")}
                                    manager={input?.manager}
                                />
                            </div>
                            <div className="dispReceiptBox">
                                <div className="dispRevenue">
                                    収入
                                    <br />
                                    印紙
                                </div>
                                <p className="dispReceipt">
                                    ￥
                                    {(input?.receipt_amount
                                        ? parseInt(input.receipt_amount, 10)
                                        : 0
                                    ).toLocaleString()}
                                </p>
                                <p className="dispReceiptTxt">
                                    {documentSetting?.proviso && (
                                        <BrText
                                            text={documentSetting.proviso}
                                        />
                                    )}
                                </p>
                            </div>
                            <p className="dispEtc mb20">
                                {documentSetting?.note && (
                                    <BrText text={documentSetting.note} />
                                )}
                            </p>
                        </div>
                    </li>
                    <li className="wd40 mr00">
                        <div className="outputTag mt00">
                            <ul className="baseList">
                                <li>
                                    <h3>出力設定</h3>
                                </li>
                                <li>
                                    <span className="inputLabel">
                                        領収書番号
                                    </span>
                                    <input
                                        type="text"
                                        name="user_receipt_number"
                                        value={input.user_receipt_number ?? ""}
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
                                            name="document_receipt_id"
                                            value={
                                                input?.document_receipt_id ?? ""
                                            }
                                            onChange={
                                                handleDocumentSettingChange
                                            }
                                        >
                                            {formSelects.documentReceipts &&
                                                Object.keys(
                                                    formSelects.documentReceipts
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
                                                                    .documentReceipts[
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
                                    <span className="inputLabel">
                                        設定可能上限金額
                                    </span>
                                    <input
                                        type="text"
                                        value={`￥${parseInt(
                                            maximumAmount,
                                            10
                                        ).toLocaleString()}`}
                                        disabled
                                    />
                                </li>
                                <li>
                                    <span className="inputLabel">領収金額</span>
                                    <OnlyNumberInput
                                        name="receipt_amount"
                                        value={input.receipt_amount ?? 0}
                                        handleChange={handleChange}
                                        maxLength={10}
                                    />
                                </li>
                                <li>
                                    <span className="inputLabel">但し書き</span>
                                    <textarea
                                        cols="3"
                                        name="proviso"
                                        value={documentSetting.proviso ?? ""}
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

const Element = document.getElementById("reserveReceiptArea");
if (Element) {
    const jsVars = Element.getAttribute("jsVars");
    const parsedJsVars = jsVars && JSON.parse(jsVars);
    const reception = Element.getAttribute("reception");
    const maximumAmount = Element.getAttribute("maximumAmount");
    const defaultValue = Element.getAttribute("defaultValue");
    const parsedDefaultValue = defaultValue && JSON.parse(defaultValue);
    const documentCommonSetting = Element.getAttribute("documentCommonSetting");
    const parsedDocumentCommonSetting =
        documentCommonSetting && JSON.parse(documentCommonSetting);
    const documentSetting = Element.getAttribute("documentSetting");
    const parsedDocumentSetting =
        documentSetting && JSON.parse(documentSetting);
    const reserveNumber = Element.getAttribute("reserveNumber");
    const formSelects = Element.getAttribute("formSelects");
    const parsedFormSelects = formSelects && JSON.parse(formSelects);
    const consts = Element.getAttribute("consts");
    const parsedConsts = consts && JSON.parse(consts);
    const isDeparted = Element.getAttribute("isDeparted");
    const isCanceled = Element.getAttribute("isCanceled");

    render(
        <ConstApp jsVars={parsedJsVars}>
            <ReserveReceiptArea
                reserveNumber={reserveNumber}
                maximumAmount={maximumAmount}
                reception={reception}
                defaultValue={parsedDefaultValue}
                documentReceiptSetting={parsedDocumentSetting}
                documentCommonSetting={parsedDocumentCommonSetting}
                formSelects={parsedFormSelects}
                consts={parsedConsts}
                isDeparted={isDeparted}
                isCanceled={isCanceled}
            />
        </ConstApp>,
        document.getElementById("reserveReceiptArea")
    );
}
