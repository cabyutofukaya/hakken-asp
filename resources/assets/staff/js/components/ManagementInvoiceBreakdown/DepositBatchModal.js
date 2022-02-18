import React, { useContext } from "react";
import { ConstContext } from "../ConstApp";
import { useMountedRef } from "../../../../hooks/useMountedRef";
import { MANAGEMENT_INVOICE_BREAKDOWN } from "../../actions";
import CustomField from "../CustomField";
import classNames from "classnames";

// flatpickr
import "flatpickr/dist/themes/airbnb.css";
import { Japanese } from "flatpickr/dist/l10n/ja.js";
import Flatpickr from "react-flatpickr";

const DepositBatchModal = ({
    id,
    data,
    staffs,
    dataDispatch,
    isProcessing,
    setIsProcessing,
    reserveBundleInvoiceId,
    customFields,
    customFieldPositions,
    customFieldCodes,
    customCategoryCode,
    pageParam,
    listTypes
}) => {
    const { agencyAccount } = useContext(ConstContext);

    const mounted = useMountedRef(); // マウント・アンマウント制御

    const { depositBatchData, doneLists, invoiceLists } = data; // 入力用データとチェック済みIDリスト、一覧リストの3種類あるので、プログラムで扱いやすいようにそれぞのれ変数に分けておく

    // 支払い済み一括処理
    const handleClick = e => {
        e.preventDefault();

        // 処理
        const batch = async () => {
            if (!mounted.current) return;
            if (isProcessing) return;
            if (doneLists.length === 0) {
                $(".js-modal-close").trigger("click"); // モーダルclose
                return;
            }

            setIsProcessing(true);

            // POST用に処理対象IDと更新日時のデータをまとめる
            let data = [];
            const targetRow = doneLists.map((id, index) => {
                let targetRow = _.find(invoiceLists, function(row) {
                    return row.id == id;
                });

                let tmp = {};
                tmp["id"] = id;
                tmp["updated_at"] = targetRow.updated_at ?? null;

                data.push(tmp);
            });

            const q = new URLSearchParams(pageParam).toString(); //ページネーションパラメータ。一覧データの再リクエストに使用
            const response = await axios
                .post(`/api/${agencyAccount}/management/deposit_batch?${q}`, {
                    data: [...data], // 一括処理対象のデータリスト(idと更新日時)
                    input: {
                        ...depositBatchData
                    },
                    reserve_bundle_invoice_id: reserveBundleInvoiceId, // 一括請求レコードID。レスポンスのリストを取得するのに必要なのでセットしておく
                    list_type: listTypes.list_type_breakdown, // レスポンス時のリストタイプ
                    _method: "put" // 更新
                })
                .finally(() => {
                    $(".js-modal-close").trigger("click"); // モーダルclose
                    setTimeout(function() {
                        if (mounted.current) {
                            setIsProcessing(false);
                        }
                    }, 3000);
                });

            if (mounted.current && response?.data?.data) {
                dataDispatch({
                    type: MANAGEMENT_INVOICE_BREAKDOWN.DEPOSIT_BATCED,
                    payload: response.data.data
                });
            }
        };

        batch();
    };

    return (
        <>
            <div
                id={id}
                className="modal js-modal"
                style={{ position: "fixed", left: 0, top: 0 }}
            >
                <div
                    className={classNames("modal__bg", {
                        "js-modal-close": !isProcessing
                    })}
                ></div>
                <div className="modal__content">
                    <p className="mdTit mb20">
                        チェックした項目の未入金額を入金済みに反映しますか？
                    </p>

                    <ul className="sideList half mb30">
                        <li>
                            <span className="inputLabel">入金日</span>
                            <div className="calendar">
                                <Flatpickr
                                    theme="airbnb"
                                    value={depositBatchData?.deposit_date ?? ""}
                                    onChange={(date, dateStr) => {
                                        dataDispatch({
                                            type:
                                                MANAGEMENT_INVOICE_BREAKDOWN.CHANGE_DEPOSITBATCH_INPUT,
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
                                    value={depositBatchData?.record_date ?? ""}
                                    onChange={(date, dateStr) => {
                                        dataDispatch({
                                            type:
                                                MANAGEMENT_INVOICE_BREAKDOWN.CHANGE_DEPOSITBATCH_INPUT,
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
                                value={depositBatchData?.[row?.key] ?? ""}
                                list={row?.list}
                                uneditItem={row?.unedit_item}
                                handleChange={e =>
                                    dataDispatch({
                                        type:
                                            MANAGEMENT_INVOICE_BREAKDOWN.CHANGE_DEPOSITBATCH_INPUT,
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
                                    value={depositBatchData?.manager_id ?? ""}
                                    onChange={e => {
                                        dataDispatch({
                                            type:
                                                MANAGEMENT_INVOICE_BREAKDOWN.CHANGE_DEPOSITBATCH_INPUT,
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
                            customFields[
                                customFieldPositions.management_common
                            ],
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
                                value={depositBatchData?.[row?.key] ?? ""}
                                list={row?.list}
                                uneditItem={row?.unedit_item}
                                handleChange={e =>
                                    dataDispatch({
                                        type:
                                            MANAGEMENT_INVOICE_BREAKDOWN.CHANGE_DEPOSITBATCH_INPUT,
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
                                value={depositBatchData?.note}
                                onChange={e => {
                                    dataDispatch({
                                        type:
                                            MANAGEMENT_INVOICE_BREAKDOWN.CHANGE_DEPOSITBATCH_INPUT,
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
                                disabled={isProcessing}
                            >
                                閉じる
                            </button>
                        </li>
                        <li className="wd50 mr00">
                            <button
                                className="blueBtn"
                                onClick={handleClick}
                                disabled={isProcessing}
                            >
                                反映する
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </>
    );
};

export default DepositBatchModal;
