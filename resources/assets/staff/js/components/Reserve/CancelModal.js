import React, { useState } from "react";
import classNames from "classnames";

/**
 * 予約キャンセルモーダル(キャンセルチャージ選択ナシ)
 *
 * @param {*} nonChargeAction チャージなし時のアクション
 * @returns
 */
const CancelModal = ({ id, nonChargeAction, isActioning = false }) => {
    return (
        <div
            id={id}
            className="modal js-modal"
            style={{ position: "fixed", left: 0, top: 0 }}
        >
            <div
                className={classNames("modal__bg", {
                    "js-modal-close": !isActioning
                })}
            ></div>
            <div className="modal__content">
                <p className="mdTit mb20">この予約を取り消しますか？</p>
                <ul className="sideList">
                    <li className="wd50">
                        <button
                            className="grayBtn js-modal-close"
                            disabled={isActioning}
                        >
                            閉じる
                        </button>
                    </li>
                    <li className="wd50 mr00">
                        <button
                            className="redBtn"
                            onClick={nonChargeAction}
                            disabled={isActioning}
                        >
                            取り消す
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    );
};

export default CancelModal;