import React, { useContext, useState, useCallback } from "react";
import { render } from "react-dom";
import { ConstContext } from "./components/ConstApp";
import ConstApp from "./components/ConstApp";
import { useMountedRef } from "../../hooks/useMountedRef";
import DeclineMessage from "./portal/DeclineMessage";
import classNames from "classnames";

/**
 *
 * @param {string} rejectionAt 辞退日時
 * @param {string} requstNumber 管理番号
 * @returns
 */
const FormControl = ({ rejectionAt, requstNumber }) => {
    const mounted = useMountedRef(); // マウント・アンマウント制御

    const { agencyAccount } = useContext(ConstContext);

    // 辞退関連
    const [isRejectionAt, setIsRejectionAt] = useState(rejectionAt); // 辞退日時
    const [isRejectinig, setIsRejectinig] = useState(false); // 辞退処理中か否か

    // 承諾関連
    const [consentMessage, setConsentMessage] = useState(""); // 一言メッセージ
    const [isConsenting, setIsConsenting] = useState(false); // 承諾処理中か否か

    const [showDeclineMessage, setShowDeclineMessage] = useState(false);
    const [declineMessage, setDeclineMessage] = useState("");

    // メッセージ入力制御
    const handleChangeConsenMessage = e => {
        setConsentMessage(e.target.value);
    };

    //承諾ダイアログclose
    const handleCloseConsent = e => {
        $(".js-modal-close").trigger("click");
        setConsentMessage(""); // 入力メッセージ初期化
    };

    // 辞退ボタンを押した時の挙動
    const handlRejection = useCallback(async e => {
        if (!mounted.current) return;
        if (isRejectinig) return;

        setIsRejectinig(true);

        const response = await axios
            .post(`/api/${agencyAccount}/web/estimate/${requstNumber}/reject`, {
                _method: "put"
            })
            .finally(() => {
                $(".js-modal-close").trigger("click"); // 辞退モーダルclose
                setTimeout(function() {
                    if (mounted.current) {
                        setIsRejectinig(false);
                    }
                }, 3000);
            });

        if (mounted.current && response?.data?.data) {
            setIsRejectionAt(response.data.data.rejection_at); // 辞退日時On → 辞退ボタン非表示
            setShowDeclineMessage(true); // decline Messageエリア表示
            setDeclineMessage(
                `オンライン相談依頼「${requstNumber}」を辞退しました。`
            );
        }
    }, []);

    // 承諾ボタンを押した時の挙動
    const handlConsent = useCallback(
        async e => {
            if (consentMessage.trim().length === 0) {
                alert("一言メッセージが入力されていません。");
                return;
            }
            if (!mounted.current) return;
            if (isConsenting) return;

            setIsConsenting(true);

            {
                /**処理完了のflashメッセージをセット（set_message=1） */
            }
            const response = await axios
                .post(
                    `/api/${agencyAccount}/web/estimate/${requstNumber}/consent`,
                    {
                        message: consentMessage,
                        set_message: 1,
                        _method: "put"
                    }
                )
                .finally(() => {
                    $(".js-modal-close").trigger("click"); // モーダルclose
                    setTimeout(function() {
                        if (mounted.current) {
                            setIsConsenting(false);
                        }
                    }, 3000);
                });

            if (mounted.current && response?.data?.data) {
                setConsentMessage(""); // 入力フィールド初期化
                // 見積もりページへ転送
                location.href = `/${agencyAccount}/estimates/web/normal/${response.data.data.estimate_number}`;
                return;
            }
        },
        [consentMessage]
    );

    return (
        <>
            <DeclineMessage message={declineMessage} />
            <li className="wd20">
                <button
                    className="grayBtn"
                    onClick={e => {
                        e.preventDefault();
                        history.back();
                    }}
                >
                    <span className="material-icons">arrow_back_ios</span>戻る
                </button>
            </li>
            {/**辞退状態の場合はbutton非表示 */}
            {!isRejectionAt && (
                <>
                    <li className="wd30">
                        <button
                            className="redBtn js-modal-open"
                            data-target="mdDecline"
                            disabled={isRejectinig}
                        >
                            <span className="material-icons">cancel</span>
                            辞退する
                        </button>
                    </li>
                    <li className="wd50">
                        <button
                            className="blueBtn js-modal-open"
                            data-target="mdAccept"
                        >
                            <span className="material-icons">check_circle</span>
                            相談を受け付ける
                        </button>
                    </li>
                </>
            )}

            {/**辞退モーダル */}
            <div
                id="mdDecline"
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
                    <p className="mdTit mb20">この依頼を辞退しますか？</p>
                    <ul className="sideList">
                        <li className="wd50">
                            <button
                                className="grayBtn js-modal-close"
                                disabled={isRejectinig}
                            >
                                閉じる
                            </button>
                        </li>
                        <li className="wd50 mr00">
                            <button
                                className="redBtn"
                                onClick={handlRejection}
                                disabled={isRejectinig}
                            >
                                辞退する
                            </button>
                        </li>
                    </ul>
                </div>
            </div>

            {/**承諾モーダル */}
            <div
                id="mdAccept"
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
                    <p className="mdTit mb20">この依頼を受け付けますか？</p>
                    <ul className="baseList mb20">
                        <li>
                            <span className="inputLabel">
                                一言メッセージ(50文字以内)
                            </span>
                            <textarea
                                maxLength="50"
                                value={consentMessage}
                                onChange={handleChangeConsenMessage}
                            ></textarea>
                        </li>
                    </ul>
                    <ul className="sideList">
                        <li className="wd50">
                            <button
                                className="grayBtn"
                                disabled={isConsenting}
                                onClick={handleCloseConsent}
                            >
                                閉じる
                            </button>
                        </li>
                        <li className="wd50 mr00">
                            <button
                                className="blueBtn"
                                disabled={isConsenting}
                                onClick={handlConsent}
                            >
                                受け付ける
                            </button>
                        </li>
                    </ul>
                </div>
            </div>

            <DeclineMessage
                show={showDeclineMessage}
                message={declineMessage}
            />
        </>
    );
};

const Element = document.getElementById("formControl");
if (Element) {
    const jsVars = Element.getAttribute("jsVars");
    const parsedJsVars = jsVars && JSON.parse(jsVars);
    const rejectionAt = Element.getAttribute("rejectionAt");
    const requstNumber = Element.getAttribute("requstNumber");

    render(
        <ConstApp jsVars={parsedJsVars}>
            <FormControl
                rejectionAt={rejectionAt}
                requstNumber={requstNumber}
            />
        </ConstApp>,
        document.getElementById("formControl")
    );
}
