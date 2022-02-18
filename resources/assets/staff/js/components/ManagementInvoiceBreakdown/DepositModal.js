import React, { useMemo, useState, useContext } from "react";
import { ConstContext } from "../ConstApp";
import { useMountedRef } from "../../../../hooks/useMountedRef";
import OnlyNumberInput from "../OnlyNumberInput";
import { MANAGEMENT_INVOICE_BREAKDOWN } from "../../actions";
import _ from "lodash";
import CustomField from "../CustomField";
import SmallDangerModal from "../SmallDangerModal";
import classNames from "classnames";

// flatpickr
import "flatpickr/dist/themes/airbnb.css";
import { Japanese } from "flatpickr/dist/l10n/ja.js";
import Flatpickr from "react-flatpickr";

const DepositModal = ({
    id,
    data,
    dataDispatch,
    staffs,
    isRegisting,
    setIsRegisting,
    isDepositDeleting,
    setIsDepositDeleting,
    depositMethodKey,
    customFields,
    customFieldPositions,
    customFieldCodes,
    customCategoryCode,
    listTypes
}) => {
    const { agencyAccount } = useContext(ConstContext);

    const mounted = useMountedRef(); // マウント・アンマウント制御

    const { depositData, currentInvoiceData } = data; // 入力用データと請求情報データの2種類あるので、プログラムで扱いやすいようにそれぞのれ変数に分けておく

    const [deleteDepositlId, setDeleteDepositlId] = useState(null); // 削除対象の入金ID

    // 未入金額を計算。TODO 依存オブジェクトcurrentInvoiceDataの扱いを再考
    const notDepositedAmount = useMemo(() => {
        return (
            currentInvoiceData.amount_total -
            _.sumBy(currentInvoiceData.agency_deposits, "amount")
        );
    }, [currentInvoiceData]);

    // 「未入金額を反映」ボタン
    const handleNotDepositedReflect = e => {
        dataDispatch({
            type: MANAGEMENT_INVOICE_BREAKDOWN.CHANGE_DEPOSIT_INPUT,
            payload: {
                name: "amount",
                value: notDepositedAmount ?? 0
            }
        });
    };

    // 削除ボタン押下時の処理（確認モーダル表示時）
    const handleDeleteModal = id => {
        setDeleteDepositlId(id);
    };

    // 削除ボタン
    const handleDelete = async () => {
        // 削除処理
        if (!mounted.current) return;
        if (isDepositDeleting) return;

        setIsDepositDeleting(true);

        const response = await axios
            .delete(
                `/api/${agencyAccount}/management/deposit/${deleteDepositlId}`,
                {
                    data: {
                        list_type: listTypes.list_type_breakdown //レスポンス時のリストタイプ
                    }
                }
            )
            .finally(() => {
                $(".js-modal-close").trigger("click"); // モーダルclose
                setTimeout(function() {
                    if (mounted.current) {
                        setIsDepositDeleting(false);
                    }
                }, 3000);
            });

        if (mounted.current && response?.data?.data) {
            dataDispatch({
                type: MANAGEMENT_INVOICE_BREAKDOWN.DEPOSIT_DELETED,
                payload: response.data.data
            });
        }
    };

    const handleDeposit = e => {
        e.preventDefault();

        // 入金登録
        const depositRegist = async () => {
            if (!mounted.current) return;
            if (isRegisting) return;

            if (depositData.amount == 0) {
                // 金額が0の場合は処理ナシ
                alert("入金額が入力されていません。");
                return;
            }

            setIsRegisting(true);
            const response = await axios
                .post(
                    `/api/${agencyAccount}/management/deposit/reserve_invoice/${currentInvoiceData?.id}`,
                    {
                        ...depositData,
                        list_type: listTypes.list_type_breakdown,
                        reserve_invoice: {
                            updated_at: currentInvoiceData?.updated_at
                        } // 同時編集チェックのために請求レコード更新日時もセット
                    }
                )
                .finally(() => {
                    $(".js-modal-close").trigger("click"); // モーダルclose
                    setTimeout(function() {
                        if (mounted.current) {
                            setIsRegisting(false);
                        }
                    }, 3000);
                });

            if (mounted.current && response?.data?.data) {
                dataDispatch({
                    type: MANAGEMENT_INVOICE_BREAKDOWN.DEPOSIT_REGISTED,
                    payload: response.data.data
                });
            }
        };

        depositRegist();
    };

    const DepositHistory = ({ deposits, depositMethodKey }) => {
        return (
            <>
                <h3>入金履歴</h3>
                <div className="modalPriceList history">
                    <table className="baseTable">
                        <thead>
                            <tr>
                                <th>入金日</th>
                                <th>登録日</th>
                                <th>入金額</th>
                                <th className="txtalc">入金方法</th>
                                <th className="txtalc wd10">削除</th>
                            </tr>
                        </thead>
                        <tbody>
                            {Object.keys(deposits).map((k, index) => (
                                <tr key={index}>
                                    <td>
                                        {deposits[k]["deposit_date"] ?? "-"}
                                    </td>
                                    <td>{deposits[k]["record_date"] ?? "-"}</td>
                                    <td>
                                        ￥
                                        {deposits[k]["amount"].toLocaleString()}
                                    </td>
                                    <td className="txtalc">
                                        {deposits[k][depositMethodKey] ?? "-"}
                                    </td>

                                    <td className="txtalc">
                                        <span
                                            className={classNames(
                                                "material-icons",
                                                {
                                                    "js-modal-open": !isDepositDeleting
                                                }
                                            )}
                                            data-target="mdDeletDeposit"
                                            onClick={e =>
                                                handleDeleteModal(
                                                    deposits[k]["id"]
                                                )
                                            }
                                        >
                                            delete
                                        </span>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </>
        );
    };

    return (
        <div
            id={id}
            className="wideModal modal js-modal mgModal"
            style={{ position: "fixed", left: 0, top: 0 }}
        >
            <div
                className={classNames("modal__bg", {
                    "js-modal-close": !isRegisting && !isDepositDeleting
                })}
            ></div>
            <div className="modal__content">
                <p className="mdTit mb20">入金登録</p>
                <h3>請求情報</h3>
                <ul className="sideList half mb30">
                    <li>
                        <table className="baseTable">
                            <tbody>
                                <tr>
                                    <th>請求番号</th>
                                    <td>
                                        {currentInvoiceData.user_invoice_number ??
                                            "-"}
                                    </td>
                                </tr>
                                <tr>
                                    <th>請求金額</th>
                                    <td>
                                        ￥
                                        {(
                                            currentInvoiceData.amount_total ?? 0
                                        ).toLocaleString()}
                                    </td>
                                </tr>
                                <tr>
                                    <th>未入金額</th>
                                    <td>
                                        ￥{notDepositedAmount.toLocaleString()}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </li>
                    <li>
                        <table className="baseTable">
                            <tbody>
                                <tr>
                                    <th>請求先</th>
                                    <td>
                                        {currentInvoiceData.billing_address_name ??
                                            "^"}
                                    </td>
                                </tr>
                                <tr>
                                    <th>支払期限</th>
                                    <td>
                                        {currentInvoiceData.payment_deadline ??
                                            "-"}
                                    </td>
                                </tr>
                                <tr>
                                    <th>出発日</th>
                                    <td>
                                        {currentInvoiceData.departure_date ??
                                            "-"}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </li>
                </ul>
                {currentInvoiceData?.agency_deposits && (
                    <DepositHistory
                        deposits={currentInvoiceData.agency_deposits}
                        depositMethodKey={depositMethodKey}
                    />
                )}
                <h3>入金詳細</h3>
                <ul className="baseList mb20">
                    <li className="wd70">
                        <span className="inputLabel">入金額</span>
                        <div className="buttonSet">
                            <OnlyNumberInput
                                name="amount"
                                value={depositData.amount ?? 0}
                                handleChange={e => {
                                    dataDispatch({
                                        type:
                                            MANAGEMENT_INVOICE_BREAKDOWN.CHANGE_DEPOSIT_INPUT,
                                        payload: {
                                            name: e.target.name,
                                            value: e.target.value
                                        }
                                    });
                                }}
                                className="wd60"
                            />
                            <button
                                className="blueBtn wd40"
                                onClick={handleNotDepositedReflect}
                            >
                                未入金額を反映
                            </button>
                        </div>
                    </li>
                </ul>
                <ul className="sideList half mb30">
                    <li>
                        <span className="inputLabel">入金日</span>
                        <div className="calendar">
                            <Flatpickr
                                theme="airbnb"
                                value={depositData.deposit_date ?? ""}
                                onChange={(date, dateStr) => {
                                    dataDispatch({
                                        type:
                                            MANAGEMENT_INVOICE_BREAKDOWN.CHANGE_DEPOSIT_INPUT,
                                        payload: {
                                            name: "deposit_date",
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
                                    { defaultValue, value, ...props },
                                    ref
                                ) => {
                                    return (
                                        <input
                                            name="deposit_date"
                                            defaultValue={value ?? ""}
                                            ref={ref}
                                        />
                                    );
                                }}
                            />
                        </div>
                    </li>
                    <li>
                        <span className="inputLabel">登録日</span>
                        <div className="calendar">
                            <Flatpickr
                                theme="airbnb"
                                value={depositData?.record_date ?? ""}
                                onChange={(date, dateStr) => {
                                    dataDispatch({
                                        type:
                                            MANAGEMENT_INVOICE_BREAKDOWN.CHANGE_DEPOSIT_INPUT,
                                        payload: {
                                            name: "record_date",
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
                                    { defaultValue, value, ...props },
                                    ref
                                ) => {
                                    return (
                                        <input
                                            name="record_date"
                                            defaultValue={value ?? ""}
                                            ref={ref}
                                        />
                                    );
                                }}
                            />
                        </div>
                    </li>

                    {/**カスタム項目を出力（入金方法） */}
                    {[
                        _.find(
                            customFields[
                                customFieldPositions.invoice_management
                            ],
                            {
                                code: customFieldCodes.deposit_method
                            }
                        )
                    ].map((row, index) => (
                        <CustomField
                            key={row.id}
                            customCategoryCode={customCategoryCode}
                            type={row?.type}
                            inputType={row?.input_type}
                            label={row?.name}
                            value={depositData?.[row?.key] ?? ""}
                            list={row.list ?? []}
                            uneditItem={row?.unedit_item}
                            handleChange={e =>
                                dataDispatch({
                                    type:
                                        MANAGEMENT_INVOICE_BREAKDOWN.CHANGE_DEPOSIT_INPUT,
                                    payload: {
                                        name: row?.key,
                                        value: e.target.value
                                    }
                                })
                            }
                        />
                    ))}
                    <li>
                        <span className="inputLabel">入金担当者</span>
                        <div className="selectBox">
                            <select
                                name="manager_id"
                                value={depositData.manager_id ?? ""}
                                onChange={e => {
                                    dataDispatch({
                                        type:
                                            MANAGEMENT_INVOICE_BREAKDOWN.CHANGE_DEPOSIT_INPUT,
                                        payload: {
                                            name: e.target.name,
                                            value: e.target.value
                                        }
                                    });
                                }}
                            >
                                {staffs &&
                                    Object.keys(staffs)
                                        .sort()
                                        .map((k, index) => (
                                            <option key={index} value={k}>
                                                {staffs[k]}
                                            </option>
                                        ))}
                            </select>
                        </div>
                    </li>
                    {/**入金方法以外のカスタム項目を出力 */}
                    {_.filter(
                        customFields[customFieldPositions.management_common],
                        {
                            code: null
                        }
                    ).map((row, index) => (
                        <CustomField
                            key={index}
                            customCategoryCode={customCategoryCode}
                            type={row?.type}
                            inputType={row?.input_type}
                            label={row?.name}
                            value={depositData?.[row?.key] ?? ""}
                            list={row.list ?? []}
                            uneditItem={row?.unedit_item}
                            handleChange={e =>
                                dataDispatch({
                                    type:
                                        MANAGEMENT_INVOICE_BREAKDOWN.CHANGE_DEPOSIT_INPUT,
                                    payload: {
                                        name: row?.key,
                                        value: e.target.value
                                    }
                                })
                            }
                        />
                    ))}
                    <li className="wd100 mr00">
                        <span className="inputLabel">備考</span>
                        <textarea
                            cols="3"
                            name="note"
                            value={depositData.note ?? ""}
                            onChange={e => {
                                dataDispatch({
                                    type:
                                        MANAGEMENT_INVOICE_BREAKDOWN.CHANGE_DEPOSIT_INPUT,
                                    payload: {
                                        name: e.target.name,
                                        value: e.target.value
                                    }
                                });
                            }}
                        ></textarea>
                    </li>
                </ul>
                <ul className="sideList">
                    <li className="wd50">
                        <button
                            className="grayBtn js-modal-close"
                            disabled={isRegisting || isDepositDeleting}
                        >
                            閉じる
                        </button>
                    </li>
                    <li className="wd50 mr00">
                        <button
                            className="blueBtn"
                            onClick={handleDeposit}
                            disabled={isRegisting || isDepositDeleting}
                        >
                            登録する
                        </button>
                    </li>
                </ul>
            </div>
            {/* 入金履歴削除確認モーダル */}
            <SmallDangerModal
                id="mdDeletDeposit"
                title="この入金履歴を削除しますか？"
                handleAction={handleDelete}
                isActioning={isDepositDeleting}
            />
        </div>
    );
};

export default DepositModal;
