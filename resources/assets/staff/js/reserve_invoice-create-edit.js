import React, { useState, useContext, useMemo } from "react";
import ConstApp from "./components/ConstApp";
import { ConstContext } from "./components/ConstApp";
import { render } from "react-dom";
import {
    DOCUMENT_COMMON,
    DOCUMENT_INVOICE,
    DOCUMENT_REQUEST
} from "./constants";
import { useMountedRef } from "../../hooks/useMountedRef";
import _ from "lodash";
import BrText from "./BrText";
import StatusUpdateModal from "./components/BusinessForm/StatusUpdateModal";
import PersonDocumentAddressSettingArea from "./components/BusinessForm/PersonDocumentAddressSettingArea";
import BusinessDocumentAddressSettingArea from "./components/BusinessForm/BusinessDocumentAddressSettingArea";
import ParticipantCheckSettingArea from "./components/BusinessForm/ParticipantCheckSettingArea";
import SealSettingArea from "./components/BusinessForm/SealSettingArea";
import SettingCheckRow from "./components/BusinessForm/SettingCheckRow";
import classNames from "classnames";
import SuccessMessage from "./components/SuccessMessage";
// flatpickr
import "flatpickr/dist/themes/airbnb.css";
import { Japanese } from "flatpickr/dist/l10n/ja.js";
import Flatpickr from "react-flatpickr";
import BreakdownPricePreviewArea from "./components/BusinessForm/BreakdownPricePreviewArea";
import AirticketInfoPreviewArea from "./components/BusinessForm/AirticketInfoPreviewArea";
import SealPreviewArea from "./components/BusinessForm/SealPreviewArea";
import HotelInfoPreviewArea from "./components/BusinessForm/HotelInfoPreviewArea";
import HotelPreviewArea from "./components/BusinessForm/HotelPreviewArea";
import OwnCompanyPreviewArea from "./components/BusinessForm/OwnCompanyPreviewArea";
import ReserveInfoPreviewArea from "./components/BusinessForm/ReserveInfoPreviewArea";
import PersonSuperscriptionPreviewArea from "./components/BusinessForm/PersonSuperscriptionPreviewArea";
import BusinessSuperscriptionPreviewArea from "./components/BusinessForm/BusinessSuperscriptionPreviewArea";

const ReserveInvoiceArea = ({
    reception,
    applicationStep,
    reserveNumber,
    defaultValue,
    documentRequestSetting,
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
    const { agencyAccount, receptionTypes } = useContext(ConstContext);

    const mounted = useMountedRef(); // ???????????????????????????????????????

    const [input, setInput] = useState({ ...defaultValue });

    const [saveMessage, setSaveMessage] = useState(""); // ???????????????????????????

    const [documentSetting, setDocumentSetting] = useState({
        ...documentRequestSetting
    }); // ????????????
    const [commonSetting, setCommonSetting] = useState({
        ...documentCommonSetting
    }); // ????????????

    // ?????????????????????????????????defaultValue???????????????????????????
    const [status, setStatus] = useState(defaultValue.status);

    const [amountTotal, setAmountTotal] = useState(0); // ????????????

    const [isSaving, setIsSaving] = useState(false); // ????????????????????????
    const [isPdfSaving, setIsPdfSaving] = useState(false); // PDF????????????????????????
    const [isLoading, setIsLoading] = useState(false); // API??????????????????
    const [isStatusUpdating, setIsStatusUpdating] = useState(false); // ?????????????????????????????????

    // ????????????????????????????????????
    const handleChange = e => {
        setInput({ ...input, [e.target.name]: e.target.value });
    };

    // ?????????????????????????????????????????????
    const handleDocumentInputChange = e => {
        setDocumentSetting({
            ...documentSetting,
            [e.target.name]: e.target.value
        });
    };

    // ???????????????????????????????????????(setting)
    const handleShowSettingChange = setting => {
        setDocumentSetting({ ...documentSetting, [setting]: setting });
    };

    // ???????????????select??????
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

    // ?????????????????????
    const handleDocumentAddressChange = e => {
        const da = input["document_address"];
        da[e.target.name] = e.target.value;
        setInput({ ...input });
    };

    // ?????????????????????On/Off??????
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

    // ?????????????????????
    const handleRepresentativeChange = e => {
        const representative = input["representative"];
        representative["name"] = e.target.value;
        setInput({ ...input });
    };

    // ???????????????????????????
    const handleSealItemChange = ({ index, value }) => {
        documentSetting["seal_items"][index] = value;
        setDocumentSetting({ ...documentSetting });
    };

    //????????????????????????
    const handleDocumentSettingChange = async e => {
        if (!mounted.current || isLoading) return;

        const name = e.target.name;
        const value = e.target.value;

        if (!value) {
            // ??????????????????ID???????????????ID????????????
            setInput({
                ...input,
                [name]: "", // ??????????????????
                document_common_id: "" // ????????????
            });
            setDocumentSetting({}); // ?????????????????????
            setCommonSetting({}); // ?????????????????????
            return;
        }

        setIsLoading(true);
        const response = await axios
            .get(`/api/${agencyAccount}/document_request/${value}`)
            .finally(() => {
                if (mounted.current) {
                    setIsLoading(false);
                }
            });
        if (mounted.current && response?.data?.data) {
            const data = response.data.data;

            // ??????????????????????????????????????????????????????????????????select?????????????????????
            setInput({
                ...input,
                [name]: value, // ??????????????????
                document_common_id: data.document_common_id ?? ""
                // ????????????
            });

            if (data?.seal == 1) {
                // ?????????????????????????????????setting??????????????????????????????????????????
                const setting = data.setting ?? [];
                setting[DOCUMENT_INVOICE.DISPLAY_BLOCK].push(
                    DOCUMENT_INVOICE.SEAL_LABEL
                );
                data.setting = setting;
            }
            setDocumentSetting({ ..._.omit(data, "document_common") });
            setCommonSetting({ ...data.document_common });
        }
    };

    // ????????????
    const handleSave = async e => {
        e.preventDefault();

        if (!input.document_request_id) {
            alert("???????????????????????????????????????????????????");
            return;
        }
        if (!input.departure_date) {
            alert("??????????????????????????????????????????");
            return;
        }
        if (!input.return_date) {
            alert("??????????????????????????????????????????");
            return;
        }

        if (!mounted.current) return;
        if (isSaving) return;

        setIsSaving(true);

        {
            /**???????????????flash??????????????????????????????set_message=1??? */
        }
        const response = await axios
            .post(
                `/api/${agencyAccount}/estimate/${reception}/reserve/${reserveNumber}/invoice`,
                {
                    ...input,
                    amount_total: amountTotal,
                    document_common_setting: commonSetting,
                    document_setting: _.omit(
                        documentSetting,
                        "document_common"
                    ), // ???????????????????????????????????????
                    //
                    option_prices: optionPrices,
                    airticket_prices: airticketPrices,
                    hotel_prices: hotelPrices,
                    hotel_info: hotelInfo,
                    hotel_contacts: hotelContacts,
                    //
                    is_canceled: isCanceled == 1 ? 1 : 0,
                    // set_message: 1,
                    _method: "put"
                }
            )
            .finally(() => {
                $(".js-modal-close").trigger("click"); // ????????????close
                setTimeout(function() {
                    if (mounted.current) {
                        setIsSaving(false);
                    }
                }, 3000);
            });
        if (mounted.current && response?.data?.data) {
            const res = response.data.data;
            // input.id = res.id; // ??????????????????ID????????????????????????
            // const reserve = {
            //     updated_at: res.reserve.updated_at
            // };
            setInput({
                ...input,
                ...res
            }); // ??????????????????????????????

            // ???????????????????????????slideDown(????????????)??????????????????????????????????????????
            // $("#successMessage .closeIcon")
            //     .parent()
            //     .slideDown();
            setSaveMessage("???????????????????????????????????????");

            // ????????????????????????????????????????????????????????????????????????
            // location.href = document.referrer
            //     ? document.referrer
            //     : `/${agencyAccount}/estimates/${applicationStep}/${reserveNumber}`;
            // return;
        }
    };

    // ?????????PDF????????????
    const handlePdf = async e => {
        e.preventDefault();

        if (!input.document_request_id) {
            alert("???????????????????????????????????????????????????");
            return;
        }
        if (!input.departure_date) {
            alert("??????????????????????????????????????????");
            return;
        }
        if (!input.return_date) {
            alert("??????????????????????????????????????????");
            return;
        }

        if (!mounted.current) return;
        if (isPdfSaving) return;

        setIsPdfSaving(true);

        const response = await axios
            .post(
                `/api/${agencyAccount}/estimate/${reception}/reserve/${reserveNumber}/invoice`,
                {
                    ...input,
                    amount_total: amountTotal,
                    document_common_setting: commonSetting,
                    document_setting: _.omit(
                        documentSetting,
                        "document_common"
                    ), // ???????????????????????????????????????
                    //
                    option_prices: optionPrices,
                    airticket_prices: airticketPrices,
                    hotel_prices: hotelPrices,
                    hotel_info: hotelInfo,
                    hotel_contacts: hotelContacts,
                    //
                    is_canceled: isCanceled == 1 ? 1 : 0,
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
            // input.id = res.id; // ??????????????????ID????????????????????????
            // const reserve = {
            //     updated_at: res.reserve.updated_at
            // };
            setInput({
                ...input,
                ...res
            }); // ??????????????????????????????

            // PDF??????????????????
            window.open(
                `/${agencyAccount}/pdf/document/request/${res.pdf.id}`,
                "_blank"
            );
        }
    };

    // ?????????????????????
    const handleUpdateStatus = async () => {
        if (!mounted.current || isStatusUpdating) {
            // ??????????????????????????????????????????????????????
            return;
        }

        if (input?.id) {
            if (status == input?.status) {
                //????????????????????????????????????????????????
                $(".js-modal-close").trigger("click"); // ????????????close
                return;
            }

            // ?????????
            setIsStatusUpdating(true); // ??????????????????On

            const response = await axios
                .post(`/api/${agencyAccount}/invoice/${input?.id}/status`, {
                    status: status,
                    reserve: { updated_at: input?.reserve?.updated_at },
                    _method: "put"
                })
                .finally(() => {
                    $(".js-modal-close").trigger("click"); // ????????????close
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
            // ???????????????(??????????????????????????????????????????????????????)
            setInput({ ...input, status });
            alert(
                "????????????????????????????????????????????????????????????\n?????????????????????????????????????????????????????????????????????"
            );
            $(".js-modal-close").trigger("click"); // ????????????close
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

    const IndexBreadcrumb = ({
        isDeparted,
        reception,
        reserveIndexUrl,
        departedIndexUrl
    }) => {
        if (isDeparted) {
            return (
                <li>
                    <a href={departedIndexUrl}>??????????????????</a>
                </li>
            );
        } else {
            return (
                <li>
                    <a href={reserveIndexUrl}>
                        {receptionTypes.web == reception && "WEB"}
                        ????????????
                    </a>
                </li>
            );
        }
    };

    return (
        <>
            <div id="pageHead">
                <h1>
                    <span className="material-icons">description</span>?????????
                    {input?.user_invoice_number}
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
                                ??????
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
                                <span className="material-icons">save</span>??????
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
                        reserveIndexUrl={consts?.reserveIndexUrl}
                        departedIndexUrl={consts?.departedIndexUrl}
                    />
                    <li>
                        <a href={consts.reserveUrl}>???????????? {reserveNumber}</a>
                    </li>
                    <li>
                        <span>?????????</span>
                    </li>
                </ol>
            </div>

            {/**??????????????????????????? */}
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
                                    ???????????????{input.user_invoice_number ?? ""}
                                </p>
                                <p>????????????{input.issue_date ?? ""}</p>
                            </div>
                            <div className="dcHead">
                                <div>
                                    {/**????????????????????????????????? */}
                                    {documentSetting?.setting?.[
                                        DOCUMENT_REQUEST.DISPLAY_BLOCK
                                    ] &&
                                        documentSetting.setting[
                                            DOCUMENT_REQUEST.DISPLAY_BLOCK
                                        ].includes("??????") &&
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
                                    {/**????????????????????????????????? */}
                                    {documentSetting?.setting?.[
                                        DOCUMENT_REQUEST.DISPLAY_BLOCK
                                    ] &&
                                        documentSetting.setting[
                                            DOCUMENT_REQUEST.DISPLAY_BLOCK
                                        ].includes("??????") &&
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
                                    {/**???????????????participants????????????????????????ON??????????????????????????????????????? */}
                                    {documentSetting?.setting?.[
                                        DOCUMENT_REQUEST.DISPLAY_BLOCK
                                    ] &&
                                        documentSetting.setting[
                                            DOCUMENT_REQUEST.DISPLAY_BLOCK
                                        ].includes(
                                            "????????????(???????????????????????????)"
                                        ) && (
                                            <ReserveInfoPreviewArea
                                                reserveSetting={
                                                    documentSetting.setting[
                                                        DOCUMENT_REQUEST
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
                                    {/**??????????????? */}
                                    {documentSetting?.setting?.[
                                        DOCUMENT_REQUEST.DISPLAY_BLOCK
                                    ] &&
                                        documentSetting.setting[
                                            DOCUMENT_REQUEST.DISPLAY_BLOCK
                                        ].includes("????????????") && (
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
                                        {/**????????? */}
                                        {documentSetting?.setting?.[
                                            DOCUMENT_REQUEST.DISPLAY_BLOCK
                                        ] &&
                                            documentSetting.setting[
                                                DOCUMENT_REQUEST.DISPLAY_BLOCK
                                            ].includes("?????????") && (
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
                            {/**????????? */}
                            {documentSetting?.setting?.[
                                DOCUMENT_REQUEST.DISPLAY_BLOCK
                            ] &&
                                documentSetting.setting[
                                    DOCUMENT_REQUEST.DISPLAY_BLOCK
                                ].includes("?????????") &&
                                documentSetting?.information && (
                                    <p className="dispAnounce">
                                        <BrText
                                            text={documentSetting.information}
                                        />
                                    </p>
                                )}
                            <div className="dispTotalPrice">
                                <dl>
                                    <dt>???????????????</dt>
                                    <dd>???{amountTotal.toLocaleString()}</dd>
                                    <dt>???????????????</dt>
                                    <dd>{input?.payment_deadline}</dd>
                                </dl>
                            </div>
                            {/**???????????? */}
                            {documentSetting?.setting?.[
                                DOCUMENT_REQUEST.DISPLAY_BLOCK
                            ] &&
                                documentSetting.setting[
                                    DOCUMENT_REQUEST.DISPLAY_BLOCK
                                ].includes("?????????") &&
                                documentSetting?.account_payable && (
                                    <div className="dispBank">
                                        <h3>????????????</h3>
                                        <BrText
                                            text={
                                                documentSetting.account_payable
                                            }
                                        />
                                    </div>
                                )}
                            {/**??????????????? */}
                            <p className="dispEtc mb20">
                                {documentSetting?.setting?.[
                                    DOCUMENT_REQUEST.DISPLAY_BLOCK
                                ] &&
                                    documentSetting.setting[
                                        DOCUMENT_REQUEST.DISPLAY_BLOCK
                                    ].includes("??????") &&
                                    documentSetting?.note && (
                                        <BrText text={documentSetting.note} />
                                    )}
                            </p>
                            <div className="dispPrice">
                                {/**???????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????? */}
                                <BreakdownPricePreviewArea
                                    isShow={
                                        documentSetting?.setting?.[
                                            DOCUMENT_REQUEST.DISPLAY_BLOCK
                                        ] &&
                                        documentSetting.setting[
                                            DOCUMENT_REQUEST.DISPLAY_BLOCK
                                        ].includes("????????????")
                                    }
                                    isCanceled={isCanceled}
                                    optionPrices={optionPriceFilter}
                                    hotelPrices={hotelPriceFilter}
                                    airticketPrices={airticketPriceFilter}
                                    showSetting={
                                        documentSetting.setting[
                                            DOCUMENT_REQUEST.BREAKDOWN_PRICE
                                        ] ?? []
                                    }
                                    amountTotal={amountTotal}
                                    setAmountTotal={setAmountTotal}
                                />
                            </div>
                            <div className="dispSchedule">
                                {/**???????????????????????? */}
                                {documentSetting?.setting?.[
                                    DOCUMENT_REQUEST.DISPLAY_BLOCK
                                ] &&
                                    documentSetting.setting[
                                        DOCUMENT_REQUEST.DISPLAY_BLOCK
                                    ].includes("???????????????") &&
                                    Object.keys(airticketPrices).length > 0 && (
                                        <AirticketInfoPreviewArea
                                            AirticketInfo
                                            reserveSetting={
                                                documentSetting.setting[
                                                    DOCUMENT_REQUEST
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
                                {/**?????????????????? */}
                                {documentSetting?.setting?.[
                                    DOCUMENT_REQUEST.DISPLAY_BLOCK
                                ] &&
                                    documentSetting.setting[
                                        DOCUMENT_REQUEST.DISPLAY_BLOCK
                                    ].includes("???????????????") &&
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
                                {/**????????????????????? */}
                                {documentSetting?.setting?.[
                                    DOCUMENT_REQUEST.DISPLAY_BLOCK
                                ] &&
                                    documentSetting.setting[
                                        DOCUMENT_REQUEST.DISPLAY_BLOCK
                                    ].includes("??????????????????") &&
                                    hotelContacts.length > 0 && (
                                        <HotelPreviewArea
                                            hotelContacts={hotelContacts}
                                            participantIds={
                                                input.participant_ids
                                            }
                                        />
                                    )}
                            </div>
                        </div>
                    </li>
                    <li className="wd40 mr00">
                        <div className="outputTag mt00">
                            <ul className="baseList">
                                <li>
                                    <h3>????????????</h3>
                                </li>
                                <li>
                                    <span className="inputLabel">????????????</span>
                                    <input
                                        type="text"
                                        name="user_invoice_number"
                                        value={input.user_invoice_number ?? ""}
                                        onChange={handleChange}
                                        maxLength={32}
                                    />
                                </li>
                                <li className="btBorder">
                                    <span className="inputLabel">?????????</span>
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
                                    <span className="inputLabel">????????????</span>
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
                                        ??????????????????
                                    </span>
                                    <div className="selectBox">
                                        <select
                                            name="document_request_id"
                                            value={
                                                input?.document_request_id ?? ""
                                            }
                                            onChange={
                                                handleDocumentSettingChange
                                            }
                                        >
                                            {formSelects.documentRequests &&
                                                Object.keys(
                                                    formSelects.documentRequests
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
                                                                    .documentRequests[
                                                                    k
                                                                ]
                                                            }
                                                        </option>
                                                    ))}
                                        </select>
                                    </div>
                                </li>
                                <li>
                                    <span className="inputLabel">??????</span>
                                    <input
                                        type="text"
                                        name="title"
                                        value={documentSetting.title ?? ""}
                                        onChange={handleDocumentInputChange}
                                        maxLength={32}
                                    />
                                </li>
                                <li>
                                    <span className="inputLabel">?????????</span>
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
                                    <span className="inputLabel">?????????</span>
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
                                    <span className="inputLabel">??????</span>
                                    <textarea
                                        cols="3"
                                        name="note"
                                        value={documentSetting.note ?? ""}
                                        onChange={handleDocumentInputChange}
                                    />
                                </li>
                                <li>
                                    <span className="inputLabel">
                                        ??????/????????????????????????
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
                                {/**????????? */}
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
                                        documentSetting?.setting?.[
                                            DOCUMENT_REQUEST.DISPLAY_BLOCK
                                        ] &&
                                        !documentSetting.setting[
                                            DOCUMENT_REQUEST.DISPLAY_BLOCK
                                        ].includes("?????????")
                                    }
                                />
                                {/**?????????????????????input */}
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
                                {/**?????????????????????input */}
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
                                    <h3>????????????</h3>
                                </li>
                                <li>
                                    <span className="inputLabel">?????????</span>
                                    <input
                                        type="text"
                                        name="name"
                                        value={input.name ?? ""}
                                        onChange={handleChange}
                                        maxLength={100}
                                    />
                                </li>
                                <li>
                                    <span className="inputLabel">?????????</span>
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
                                    <span className="inputLabel">?????????</span>
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
                                    <span className="inputLabel">?????????</span>
                                    <input
                                        type="text"
                                        value={input.representative.name ?? ""}
                                        onChange={handleRepresentativeChange}
                                        maxLength={32}
                                    />
                                </li>
                                <li>
                                    {/**?????????????????????????????? */}
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
                                    <h3 className="mb00">??????????????????</h3>
                                    <ul className="sideList half mb30">
                                        {Object.keys(
                                            formSelects?.setting?.[
                                                DOCUMENT_REQUEST.DISPLAY_BLOCK
                                            ]
                                        ).map((key, index) => (
                                            <SettingCheckRow
                                                key={index}
                                                category={
                                                    DOCUMENT_REQUEST.DISPLAY_BLOCK
                                                }
                                                row={
                                                    formSelects.setting[
                                                        DOCUMENT_REQUEST
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
                                    <h3>????????????</h3>
                                    <ul className="baseList">
                                        {Object.keys(
                                            formSelects?.setting?.[
                                                DOCUMENT_REQUEST
                                                    .RESERVATION_INFO
                                            ]
                                        ).map((key, index) => (
                                            <SettingCheckRow
                                                key={index}
                                                category={
                                                    DOCUMENT_REQUEST.RESERVATION_INFO
                                                }
                                                row={
                                                    formSelects.setting[
                                                        DOCUMENT_REQUEST
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
                                    <h3>???????????????</h3>
                                    <ul>
                                        {Object.keys(
                                            formSelects?.setting?.[
                                                DOCUMENT_REQUEST.AIR_TICKET_INFO
                                            ]
                                        ).map((key, index) => (
                                            <SettingCheckRow
                                                key={index}
                                                category={
                                                    DOCUMENT_REQUEST.AIR_TICKET_INFO
                                                }
                                                row={
                                                    formSelects.setting[
                                                        DOCUMENT_REQUEST
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
                                    <h3 className="mt20">????????????</h3>
                                    <ul className="mt00">
                                        {Object.keys(
                                            formSelects?.setting?.[
                                                DOCUMENT_REQUEST.BREAKDOWN_PRICE
                                            ]
                                        ).map((key, index) => (
                                            <SettingCheckRow
                                                key={index}
                                                category={
                                                    DOCUMENT_REQUEST.BREAKDOWN_PRICE
                                                }
                                                row={
                                                    formSelects.setting[
                                                        DOCUMENT_REQUEST
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
            {/*????????????????????????????????? */}
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

const Element = document.getElementById("reserveInvoiceArea");
if (Element) {
    const jsVars = Element.getAttribute("jsVars");
    const parsedJsVars = jsVars && JSON.parse(jsVars);
    const applicationStep = Element.getAttribute("applicationStep");
    const reception = Element.getAttribute("reception");
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
            <ReserveInvoiceArea
                reception={reception}
                applicationStep={applicationStep}
                reserveNumber={reserveNumber}
                defaultValue={parsedDefaultValue}
                documentRequestSetting={parsedDocumentSetting}
                documentCommonSetting={parsedDocumentCommonSetting}
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
        document.getElementById("reserveInvoiceArea")
    );
}
