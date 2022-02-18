import React, { useContext } from "react";
import { ConstContext } from "../ConstApp";
import { useMountedRef } from "../../../../hooks/useMountedRef";
import classNames from "classnames";

const DetermineModal = ({
    id,
    estimate,
    isConfirming,
    setIsConfirming,
    determineUrl,
    afterDetermineRedirectUrl
}) => {
    const { agencyAccount } = useContext(ConstContext);
    console.log(determineUrl);
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
                updated_at: estimate?.updated_at, // 同時編集チェックに使用
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
            location.href = afterDetermineRedirectUrl;
        }
    };

    return (
        <div
            id={id}
            className="modal js-modal"
            style={{ position: "fixed", left: 0, top: 0 }}
        >
            <div
                className={classNames("modal__bg", {
                    "js-modal-close": !isConfirming
                })}
            ></div>
            <div className="modal__content">
                <p className="mdTit mb20">この見積を予約確定しますか？</p>
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
