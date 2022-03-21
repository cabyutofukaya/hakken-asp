import React from "react";
import classNames from "classnames";

/**
 * 担当者情報の追加・編集モーダル
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
const ManagerEditModal = ({
    handleChange,
    handleEdit,
    dms,
    sexes,
    input,
    modalMode,
    isEditing = false
} = {}) => {
    return (
        <>
            <div
                id="mdAddManager"
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
                    <p className="mdTit mb20">取引先担当者情報追加</p>
                    <ul className="baseList mb40">
                        <li>
                            <span className="inputLabel">担当者名</span>
                            <input
                                type="text"
                                onChange={e =>
                                    handleChange({
                                        target: {
                                            name: "name",
                                            value: e.target.value
                                        }
                                    })
                                }
                                value={input.name ?? ""}
                                maxLength={32}
                            />
                        </li>
                        <li>
                            <span className="inputLabel">
                                担当者名(ローマ字)
                            </span>
                            <input
                                type="text"
                                onChange={e =>
                                    handleChange({
                                        target: {
                                            name: "name_roman",
                                            value: e.target.value
                                        }
                                    })
                                }
                                value={input.name_roman ?? ""}
                                maxLength={64}
                            />
                        </li>
                        <li className="wd40">
                            <span className="inputLabel">性別</span>
                            <ul className="baseRadio sideList half mt10">
                                {sexes &&
                                    Object.keys(sexes).map((v, index) => (
                                        <li key={index}>
                                            <input
                                                type="radio"
                                                id={`sex${v}`}
                                                value={v}
                                                checked={input?.sex === v}
                                                onChange={e =>
                                                    handleChange({
                                                        target: {
                                                            name: "sex",
                                                            value:
                                                                e.target.value
                                                        }
                                                    })
                                                }
                                            />
                                            <label htmlFor={`sex${v}`}>
                                                {sexes[v]}
                                            </label>
                                        </li>
                                    ))}
                            </ul>
                        </li>
                        <li>
                            <span className="inputLabel">部署名</span>
                            <input
                                type="text"
                                onChange={e =>
                                    handleChange({
                                        target: {
                                            name: "department_name",
                                            value: e.target.value
                                        }
                                    })
                                }
                                value={input.department_name ?? ""}
                                maxLength={32}
                            />
                        </li>
                        <li>
                            <span className="inputLabel">メールアドレス</span>
                            <input
                                type="mail"
                                onChange={e =>
                                    handleChange({
                                        target: {
                                            name: "email",
                                            value: e.target.value
                                        }
                                    })
                                }
                                value={input.email ?? ""}
                                maxLength={100}
                            />
                        </li>
                        <li>
                            <span className="inputLabel">電話番号</span>
                            <input
                                type="tel"
                                onChange={e =>
                                    handleChange({
                                        target: {
                                            name: "tel",
                                            value: e.target.value
                                        }
                                    })
                                }
                                value={input.tel ?? ""}
                                maxLength={32}
                            />
                        </li>
                        <li className="wd20">
                            <span className="inputLabel">DM</span>
                            <div className="selectBox">
                                <select
                                    onChange={e =>
                                        handleChange({
                                            target: {
                                                name: "dm",
                                                value: e.target.value
                                            }
                                        })
                                    }
                                    value={input.dm ?? ""}
                                >
                                    {dms &&
                                        Object.keys(dms)
                                            .sort((a, b) => a - b)
                                            .map((val, index) => (
                                                <option key={index} value={val}>
                                                    {dms[val]}
                                                </option>
                                            ))}
                                </select>
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

export default ManagerEditModal;
