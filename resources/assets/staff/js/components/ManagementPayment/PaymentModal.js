import React, { useMemo, useState, useContext } from "react";
import { ConstContext } from "../ConstApp";
import { useMountedRef } from "../../../../hooks/useMountedRef";
import OnlyNumberInput from "../OnlyNumberInput";
import { MANAGEMENT_PAYMENT } from "../../actions";
import _ from "lodash";
import CustomField from "../CustomField";
import SmallDangerModal from "../SmallDangerModal";
import classNames from "classnames";

// flatpickr
import "flatpickr/dist/themes/airbnb.css";
import { Japanese } from "flatpickr/dist/l10n/ja.js";
import Flatpickr from "react-flatpickr";

/**
 *
 * @param {*} isRegisting 登録処理中か否か
 * @param {*} withdrawalMethodKey 出金方法のカスタムキー
 * @returns
 */
const PaymentModal = ({
    id,
    data,
    dataDispatch,
    staffs,
    isRegisting,
    setIsRegisting,
    isWithdrawalDeleting,
    setIsWithDrawalDeleting,
    withdrawalMethodKey,
    customFields,
    customFieldPositions,
    customFieldCodes,
    customCategoryCode
}) => {
    const { agencyAccount } = useContext(ConstContext);

    const mounted = useMountedRef(); // マウント・アンマウント制御

    const { withdrawalData, currentPaymentData } = data; // 入力用データと支払い情報データの2種類あるので、プログラムで扱いやすいようにそれぞのれ変数に分けておく

    const [deleteWithdrawalId, setDeleteWithdrawalId] = useState(null); // 削除対象の出金ID

    //未払い金額を計算。TODO 依存オブジェクトcurrentPaymentDataの扱いを再考
    const unpaidAmount = useMemo(() => {
        return (
            currentPaymentData.amount_payment -
            _.sumBy(currentPaymentData.agency_withdrawals, "amount")
        );
    }, [currentPaymentData]);

    // 「未払金額を反映」ボタン
    const handleUnpaidReflect = e => {
        dataDispatch({
            type: MANAGEMENT_PAYMENT.CHANGE_WITHDRAWAL_INPUT,
            payload: {
                name: "amount",
                value: unpaidAmount ?? 0
            }
        });
    };

    // 削除ボタン押下時の処理（確認モーダル表示時）
    const handleDeleteModal = id => {
        setDeleteWithdrawalId(id);
    };

    // 削除ボタン
    const handleDelete = async () => {
        // 削除処理
        if (!mounted.current) return;
        if (isWithdrawalDeleting) return;

        setIsWithDrawalDeleting(true);

        const response = await axios
            .delete(
                `/api/${agencyAccount}/management/withdrawal/${deleteWithdrawalId}`
            )
            .finally(() => {
                $(".js-modal-close").trigger("click"); // モーダルclose
                setTimeout(function() {
                    if (mounted.current) {
                        setIsWithDrawalDeleting(false);
                    }
                }, 3000);
            });

        if (mounted.current && response?.data?.data) {
            dataDispatch({
                type: MANAGEMENT_PAYMENT.WITHDRAWAL_DELETED,
                payload: response.data.data
            });
        }
    };

    const handleWithdrawal = e => {
        e.preventDefault();

        // 出金登録
        const withdrawalRegist = async () => {
            if (!mounted.current) return;
            if (isRegisting) return;

            if (withdrawalData.amount == 0) {
                // 金額が0の場合は処理ナシ
                alert("出金額が入力されていません。");
                return;
            }

            setIsRegisting(true);
            const response = await axios
                .post(
                    `/api/${agencyAccount}/management/withdrawal/account_payable_detail/${currentPaymentData?.id}`,
                    {
                        ...withdrawalData,
                        account_payable_detail: {
                            updated_at: currentPaymentData?.updated_at
                        } // 同時編集チェックのために支払明細レコード更新日時もセット
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
                    type: MANAGEMENT_PAYMENT.WITHDRAWAL_REGISTED,
                    payload: response.data.data
                });
            }
        };

        withdrawalRegist();
    };

    const WithdrawalHistory = ({ withdrawals, withdrawalMethodKey }) => {
        return (
            <>
                <h3>出金履歴</h3>
                <div className="modalPriceList history">
                    <table className="baseTable">
                        <thead>
                            <tr>
                                <th>出金日</th>
                                <th>登録日</th>
                                <th>出金額</th>
                                <th className="txtalc">出金方法</th>
                                <th className="txtalc wd10">削除</th>
                            </tr>
                        </thead>
                        <tbody>
                            {Object.keys(withdrawals).map((k, index) => (
                                <tr key={index}>
                                    <td>
                                        {withdrawals[k]["withdrawal_date"] ??
                                            "-"}
                                    </td>
                                    <td>
                                        {withdrawals[k]["record_date"] ?? "-"}
                                    </td>
                                    <td>
                                        ￥
                                        {withdrawals[k][
                                            "amount"
                                        ].toLocaleString()}
                                    </td>
                                    <td className="txtalc">
                                        {withdrawals[k][withdrawalMethodKey] ??
                                            "-"}
                                    </td>

                                    <td className="txtalc">
                                        <span
                                            className={classNames(
                                                "material-icons",
                                                {
                                                    "js-modal-open": !isWithdrawalDeleting
                                                }
                                            )}
                                            data-target="mdDeleteWithdrawal"
                                            onClick={e =>
                                                handleDeleteModal(
                                                    withdrawals[k]["id"]
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
                    "js-modal-close": !isRegisting && !isWithdrawalDeleting
                })}
            ></div>
            <div className="modal__content">
                <p className="mdTit mb20">出金登録</p>
                <h3>支払情報</h3>
                <ul className="sideList half mb30">
                    <li>
                        <table className="baseTable">
                            <tbody>
                                <tr>
                                    <th>予約番号</th>
                                    <td>
                                        {currentPaymentData?.reserve
                                            ?.control_number ?? "-"}
                                    </td>
                                </tr>
                                <tr>
                                    <th>支払金額</th>
                                    <td>
                                        ￥
                                        {currentPaymentData?.amount_payment &&
                                            currentPaymentData.amount_payment.toLocaleString()}
                                    </td>
                                </tr>
                                <tr>
                                    <th>未払額</th>
                                    <td>￥{unpaidAmount.toLocaleString()}</td>
                                </tr>
                            </tbody>
                        </table>
                    </li>
                    <li>
                        <table className="baseTable">
                            <tbody>
                                <tr>
                                    <th>仕入先</th>
                                    <td>
                                        {currentPaymentData?.supplier_name ??
                                            "-"}
                                    </td>
                                </tr>
                                <tr>
                                    <th>支払予定日</th>
                                    <td>
                                        {currentPaymentData?.payment_date ??
                                            "-"}
                                    </td>
                                </tr>
                                <tr>
                                    <th>商品コード</th>
                                    <td>
                                        {currentPaymentData?.item_code ?? "-"}
                                    </td>
                                </tr>
                                <tr>
                                    <th>商品名</th>
                                    <td>
                                        {currentPaymentData?.item_name ?? "-"}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </li>
                </ul>
                {currentPaymentData?.agency_withdrawals && (
                    <WithdrawalHistory
                        withdrawals={currentPaymentData.agency_withdrawals}
                        withdrawalMethodKey={withdrawalMethodKey}
                    />
                )}

                <h3>出金詳細</h3>
                <ul className="baseList mb20">
                    <li className="wd70">
                        <span className="inputLabel">出金額</span>
                        <div className="buttonSet">
                            <OnlyNumberInput
                                name="amount"
                                value={withdrawalData?.amount ?? 0}
                                handleChange={e => {
                                    dataDispatch({
                                        type:
                                            MANAGEMENT_PAYMENT.CHANGE_WITHDRAWAL_INPUT,
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
                                onClick={handleUnpaidReflect}
                            >
                                未払金額を反映
                            </button>
                        </div>
                    </li>
                </ul>
                <ul className="sideList half mb30">
                    <li>
                        <span className="inputLabel">出金日</span>
                        <div className="calendar">
                            <Flatpickr
                                theme="airbnb"
                                value={withdrawalData?.withdrawal_date ?? ""}
                                onChange={(date, dateStr) => {
                                    dataDispatch({
                                        type:
                                            MANAGEMENT_PAYMENT.CHANGE_WITHDRAWAL_INPUT,
                                        payload: {
                                            name: "withdrawal_date",
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
                                            name="withdrawal_date"
                                            value={value}
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
                                value={withdrawalData?.record_date ?? ""}
                                onChange={(date, dateStr) => {
                                    dataDispatch({
                                        type:
                                            MANAGEMENT_PAYMENT.CHANGE_WITHDRAWAL_INPUT,
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
                                            value={value}
                                            ref={ref}
                                        />
                                    );
                                }}
                            />
                        </div>
                    </li>
                    {/**カスタム項目を出力（出金方法） */}
                    {[
                        _.find(
                            customFields[
                                customFieldPositions.payment_management
                            ],
                            {
                                code: customFieldCodes.withdrawal_method
                            }
                        )
                    ].map((row, index) => (
                        <CustomField
                            key={row.id}
                            customCategoryCode={customCategoryCode}
                            type={row?.type}
                            inputType={row?.input_type}
                            label={row?.name}
                            value={withdrawalData?.[row?.key] ?? ""}
                            list={row?.list}
                            uneditItem={row?.unedit_item}
                            handleChange={e =>
                                dataDispatch({
                                    type:
                                        MANAGEMENT_PAYMENT.CHANGE_WITHDRAWAL_INPUT,
                                    payload: {
                                        name: row?.key,
                                        value: e.target.value
                                    }
                                })
                            }
                        />
                    ))}
                    <li>
                        <span className="inputLabel">出金担当者</span>
                        <div className="selectBox">
                            <select
                                name="manager_id"
                                value={withdrawalData?.manager_id ?? ""}
                                onChange={e => {
                                    dataDispatch({
                                        type:
                                            MANAGEMENT_PAYMENT.CHANGE_WITHDRAWAL_INPUT,
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
                    {/**出金方法以外のカスタム項目を出力 */}
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
                            value={withdrawalData?.[row?.key] ?? ""}
                            list={row?.list}
                            uneditItem={row?.unedit_item}
                            handleChange={e =>
                                dataDispatch({
                                    type:
                                        MANAGEMENT_PAYMENT.CHANGE_WITHDRAWAL_INPUT,
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
                            value={withdrawalData.note ?? ""}
                            onChange={e => {
                                dataDispatch({
                                    type:
                                        MANAGEMENT_PAYMENT.CHANGE_WITHDRAWAL_INPUT,
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
                            disabled={isRegisting || isWithdrawalDeleting}
                        >
                            閉じる
                        </button>
                    </li>
                    <li className="wd50 mr00">
                        <button
                            className="blueBtn"
                            onClick={handleWithdrawal}
                            disabled={isRegisting || isWithdrawalDeleting}
                        >
                            登録する
                        </button>
                    </li>
                </ul>
            </div>
            {/* 出金履歴削除確認モーダル */}
            <SmallDangerModal
                id="mdDeleteWithdrawal"
                title="この出金履歴を削除しますか？"
                handleAction={handleDelete}
                isActioning={isWithdrawalDeleting}
            />
        </div>
    );
};

export default PaymentModal;
