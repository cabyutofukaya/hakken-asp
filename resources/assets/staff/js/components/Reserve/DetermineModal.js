import React from "react";
import { useMountedRef } from "../../../../hooks/useMountedRef";
import classNames from "classnames";

// 帰着日が過去の場合はtrue
const isDeparted = returnDate => {
    const dt = new Date();
    const rd = new Date(`${returnDate} 23:59:59`);
    return dt.getTime() > rd.getTime();
};

const DetermineModal = ({
    id,
    estimate,
    updatedAt,
    isConfirming,
    setIsConfirming,
    determineUrl,
    reserveIndexUrl,
    departedIndexUrl
}) => {
    const mounted = useMountedRef(); // マウント・アンマウント制御

    // 「予約に変更する」ボタン押下処理
    const handleClick = async e => {
        e.preventDefault();

        if (!mounted.current) return;
        if (isConfirming) return;
        setIsConfirming(true); // 多重処理制御

        let response = null;
        response = await axios
            .post(determineUrl, {
                departure_date: estimate?.departure_date, // 予約確定時、出発日が設定されているかバリデーションを行う
                return_date: estimate?.return_date, // 予約確定時、帰着日が設定されているかバリデーションを行う
                updated_at: updatedAt, // 同時編集チェックに使用
                set_message: true, // API処理完了後、flashメッセージセットを要求
                _method: "put"
            })
            .finally(() => {
                $(".js-modal-close").trigger("click"); // モーダルclose
                setTimeout(function() {
                    if (mounted.current) {
                        setIsConfirming(false);
                    }
                }, 3000);
            });

        if (mounted.current && response?.status == 200) {
            // ページ遷移
            if (isDeparted(estimate?.return_date)) {
                // 帰着日が過去の場合は催行済一覧へ遷移
                location.href = departedIndexUrl;
            } else {
                location.href = reserveIndexUrl;
            }
        }
    };

    // 帰着日が過去の場合は注意文を表示
    const ReturnDateWarning = ({ returnDate }) => {
        if (isDeparted(returnDate)) {
            return (
                <>
                    <br />
                    <small>※帰着日が過去の日付のため催行済みに移動します</small>
                </>
            );
        }
        return null;
    };

    return (
        <div
            id={id}
            className="modal js-modal"
            style={{ position: "fixed", left: 0, top: 0 }}
        >
            {/**.js-modal-closeをはずしてもjquery側からレイヤーclickでレイヤーが消えてまうのでやむを得ずfalseで固定 */}
            <div
                className={classNames("modal__bg", {
                    "js-modal-close": false
                })}
            ></div>
            <div className="modal__content">
                <p className="mdTit mb20">
                    この見積を予約確定しますか？
                    <ReturnDateWarning returnDate={estimate?.return_date} />
                </p>
                <ul className="sideList">
                    <li className="wd50">
                        <button
                            className="grayBtn js-modal-close"
                            disabled={isConfirming}
                        >
                            閉じる
                        </button>
                    </li>
                    <li className="wd50 mr00">
                        <button
                            className="blueBtn"
                            onClick={handleClick}
                            disabled={isConfirming}
                        >
                            予約に変更する
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    );
};

export default DetermineModal;
