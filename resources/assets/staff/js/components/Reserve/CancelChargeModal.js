import React, { useState } from "react";
import classNames from "classnames";

/**
 * 予約キャンセルモーダル(キャンセルチャージ選択付き)
 *
 * @param {int} defaultCheck キャンセルチャージの有無デフォルト選択
 * @param {*} nonChargeAction チャージなし時のアクション
 * @param {*} chargeAction チャージあり時のアクション
 * @returns
 */
const CancelChargeModal = ({
    id,
    defaultCheck,
    nonChargeAction,
    chargeAction,
    isActioning = false,
    title,
    positiveLabel
}) => {
    const [value, setValue] = useState(defaultCheck);
    const handleChange = e => {
        setValue(e.target.value);
    };

    const handleSubmit = e => {
        //チャージの有無でアクションを分岐
        value == 1 ? chargeAction(e) : nonChargeAction(e);
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
                <p className="mdTit mb20">{title}</p>
                <ul className="sideList baseRadio mb20 central">
                    <li>
                        <input
                            type="radio"
                            name="cancel_charge"
                            id="charge_n"
                            value="0"
                            onChange={handleChange}
                            checked={value == 0}
                        />
                        <label htmlFor="charge_n">キャンセルチャージなし</label>
                    </li>
                    <li>
                        <input
                            type="radio"
                            name="cancel_charge"
                            id="charge_y"
                            value="1"
                            onChange={handleChange}
                            checked={value == 1}
                        />
                        <label htmlFor="charge_y">キャンセルチャージあり</label>
                    </li>
                </ul>
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
                            onClick={handleSubmit}
                            disabled={isActioning}
                        >
                            {positiveLabel}
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    );
};

export default CancelChargeModal;
