import React from "react";
import classNames from "classnames";
// flatpickr
import "flatpickr/dist/themes/airbnb.css";
import { Japanese } from "flatpickr/dist/l10n/ja.js";
import Flatpickr from "react-flatpickr";

/**
 * ビザ情報の追加・編集モーダル
 *
 * form送信時、値が送られないようにname属性はつけない
 *
 * @param {function} handleChange 入力値管理
 * @param {function} handleEdit 編集処理
 * @param {array} countries 国情報配列
 * @param {array} input 入力値
 * @param {string} modalMode 編集モード。create or edit
 * @param {boolean} isEditing 処理中か否か
 * @returns
 */
const VisaEditModal = ({
    handleChange,
    handleEdit,
    countries,
    input,
    modalMode,
    isEditing = false
} = {}) => {
    return (
        <>
            <div
                id="mdAddVisa"
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
                    <p className="mdTit mb20">ビザ情報追加</p>
                    <ul className="baseList mb40">
                        <li>
                            <span className="inputLabel">番号</span>
                            <input
                                type="text"
                                onChange={e =>
                                    handleChange({
                                        target: {
                                            name: "number",
                                            value: e.target.value
                                        }
                                    })
                                }
                                value={input.number ?? ""}
                                maxLength={50}
                            />
                        </li>
                        <li>
                            <span className="inputLabel">国</span>
                            <div className="selectBox">
                                <select
                                    onChange={e =>
                                        handleChange({
                                            target: {
                                                name: "country_code",
                                                value: e.target.value
                                            }
                                        })
                                    }
                                    value={input.country_code ?? ""}
                                >
                                    {countries &&
                                        Object.keys(countries)
                                            .sort((a, b) => a - b)
                                            .map((val, index) => (
                                                <option key={index} value={val}>
                                                    {countries[val]}
                                                </option>
                                            ))}
                                </select>
                            </div>
                        </li>
                        <li>
                            <span className="inputLabel">種別</span>
                            <input
                                type="text"
                                onChange={e =>
                                    handleChange({
                                        target: {
                                            name: "kind",
                                            value: e.target.value
                                        }
                                    })
                                }
                                value={input.kind ?? ""}
                                maxLength={50}
                            />
                        </li>
                        <li>
                            <span className="inputLabel">発行地</span>
                            <div className="selectBox">
                                <select
                                    onChange={e =>
                                        handleChange({
                                            target: {
                                                name: "issue_place_code",
                                                value: e.target.value
                                            }
                                        })
                                    }
                                    value={input.issue_place_code ?? ""}
                                >
                                    {countries &&
                                        Object.keys(countries)
                                            .sort((a, b) => a - b)
                                            .map((val, index) => (
                                                <option key={index} value={val}>
                                                    {countries[val]}
                                                </option>
                                            ))}
                                </select>
                            </div>
                        </li>
                        <li>
                            <span className="inputLabel">発行日</span>
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
                                        { defaultValue, value, ...props },
                                        ref
                                    ) => {
                                        return (
                                            <input
                                                defaultValue={value ?? ""}
                                                ref={ref}
                                            />
                                        );
                                    }}
                                />
                            </div>
                        </li>
                        <li>
                            <span className="inputLabel">有効期限</span>
                            <div className="calendar">
                                <Flatpickr
                                    theme="airbnb"
                                    value={input.expiration_date ?? ""}
                                    onChange={(date, dateStr) => {
                                        handleChange({
                                            target: {
                                                name: "expiration_date",
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
                                                defaultValue={value ?? ""}
                                                ref={ref}
                                            />
                                        );
                                    }}
                                />
                            </div>
                        </li>
                        <li>
                            <span className="inputLabel">備考</span>
                            <input
                                type="text"
                                onChange={e =>
                                    handleChange({
                                        target: {
                                            name: "note",
                                            value: e.target.value
                                        }
                                    })
                                }
                                value={input.note ?? ""}
                                maxLength={100}
                            />
                        </li>
                    </ul>
                    <ul className="sideList">
                        <li className="wd50">
                            <button
                                className="grayBtn js-modal-close"
                                disabled={isEditing}
                            >
                                閉じる
                            </button>
                        </li>
                        <li className="wd50 mr00">
                            <button
                                className="blueBtn"
                                onClick={handleEdit}
                                disabled={isEditing}
                            >
                                {modalMode === "create"
                                    ? "登録する"
                                    : "更新する"}
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </>
    );
};

export default VisaEditModal;
