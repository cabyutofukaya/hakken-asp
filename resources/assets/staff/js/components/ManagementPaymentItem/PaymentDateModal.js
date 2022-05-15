import React, { useContext } from "react";
import { ConstContext } from "../ConstApp";
import { useMountedRef } from "../../../../hooks/useMountedRef";
import { MANAGEMENT_PAYMENT_ITEM } from "../../actions";
import classNames from "classnames";
// flatpickr
import "flatpickr/dist/themes/airbnb.css";
import { Japanese } from "flatpickr/dist/l10n/ja.js";
import Flatpickr from "react-flatpickr";

const PaymentDateModal = ({
    id = "mdEditPayday",
    data,
    dataDispatch,
    isProcessing,
    setIsProcessing
} = {}) => {
    const { agencyAccount } = useContext(ConstContext);

    const mounted = useMountedRef(); // マウント・アンマウント制御

    const { paymentDateData, currentPaymentData } = data; // dataから支払日の入力用データと現在編集対象の支払いレコードを変数に取り出す

    // 支払日変更
    const handleChanging = e => {
        e.preventDefault();

        // 出金登録
        const paymentDateUpdate = async () => {
            if (!mounted.current) return;
            if (isProcessing) return;

            if (
                paymentDateData.payment_date === currentPaymentData.payment_date
            ) {
                // 日付が変わっていない場合は処理ナシ
                $(".js-modal-close").trigger("click"); // モーダルclose
                return;
            }

            setIsProcessing(true);
            const response = await axios
                .post(
                    `/api/${agencyAccount}/management/account_payable_item/${currentPaymentData?.id}/payment_date`,
                    {
                        ...paymentDateData,
                        updated_at: currentPaymentData?.updated_at, // 同時編集チェックのために支払明細レコード更新日時もセット
                        _method: "put" // 更新
                    }
                )
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
                    type: MANAGEMENT_PAYMENT_ITEM.PAYMENTDATA_CHANGED,
                    payload: response.data.data
                });
            }
        };

        paymentDateUpdate();
    };

    return (
        <>
            <div
                id={id}
                className="modal js-modal mgModal"
                style={{ position: "fixed", left: 0, top: 0 }}
            >
                {/**.js-modal-closeをはずしてもjquery側からレイヤーclickでレイヤーが消えてまうのでやむを得ずfalseで固定 */}
                <div
                    className={classNames("modal__bg", {
                        "js-modal-close": false
                    })}
                ></div>
                <div className="modal__content">
                    <p className="mdTit mb20">支払予定日変更</p>

                    <ul className="baseList mb40">
                        <li>
                            <span className="inputLabel">支払予定日</span>
                            <div className="calendar">
                                <Flatpickr
                                    theme="airbnb"
                                    value={paymentDateData?.payment_date ?? ""}
                                    onChange={(date, dateStr) => {
                                        dataDispatch({
                                            type:
                                                MANAGEMENT_PAYMENT_ITEM.CHANGE_PAYMENTDATA_INPUT,
                                            payload: {
                                                name: "payment_date",
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
                                                name="payment_date"
                                                defaultValue={value ?? ""}
                                                ref={ref}
                                            />
                                        );
                                    }}
                                />
                            </div>
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
                                onClick={handleChanging}
                                disabled={isProcessing}
                            >
                                変更する
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </>
    );
};

export default PaymentDateModal;
