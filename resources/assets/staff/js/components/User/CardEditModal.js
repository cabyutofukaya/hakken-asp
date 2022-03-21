import React from "react";
import classNames from "classnames";

/**
 * メンバーカード情報の追加・編集モーダル
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
const CardEditModal = ({
    handleChange,
    handleEdit,
    input,
    modalMode,
    isEditing = false,
    customFields,
    customCategoryCode
} = {}) => {
    return (
        <>
            <div
                id="mdAddCard"
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
                    <p className="mdTit mb20">メンバーズカード情報追加</p>
                    <ul className="baseList mb40">
                        <li>
                            <span className="inputLabel">カード名</span>
                            <input
                                type="text"
                                onChange={e =>
                                    handleChange({
                                        target: {
                                            name: "card_name",
                                            value: e.target.value
                                        }
                                    })
                                }
                                value={input.card_name ?? ""}
                                maxLength={50}
                            />
                        </li>
                        <li>
                            <span className="inputLabel">カード番号</span>
                            <input
                                type="text"
                                onChange={e =>
                                    handleChange({
                                        target: {
                                            name: "card_number",
                                            value: e.target.value
                                        }
                                    })
                                }
                                value={input.card_number ?? ""}
                                maxLength={50}
                            />
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

export default CardEditModal;
