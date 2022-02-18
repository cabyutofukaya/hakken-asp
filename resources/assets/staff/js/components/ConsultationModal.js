import React, { useContext } from "react";
import { ConstContext } from "./ConstApp";
import CustomField from "./CustomField";
import classNames from "classnames";
// flatpickr
import "flatpickr/dist/themes/airbnb.css";
import { Japanese } from "flatpickr/dist/l10n/ja.js";
import Flatpickr from "react-flatpickr";
import _ from "lodash";

/**
 *
 * form送信時、値が送られないようにname属性はつけない
 *
 * @param {*} param0
 * @returns
 */
const ConsultationModal = ({
    handleChange,
    handleSubmit,
    staffs,
    statuses,
    kinds,
    input,
    modalMode,
    isEditing = false,
    id = "mdAddContact",
    customFields = {}
} = {}) => {
    const { customFieldPositions, userCustomCategoryCodes } = useContext(
        ConstContext
    );
    return (
        <div
            id={id}
            className="modal js-modal"
            style={{ position: "fixed", left: 0, top: 0 }}
        >
            <div
                className={classNames("modal__bg", {
                    "js-modal-close": !isEditing
                })}
            ></div>
            <div className="modal__content">
                <p className="mdTit mb20">
                    {modalMode === "create" ? "新規相談追加" : "相談編集"}
                </p>
                <ul className="baseList mb25">
                    <li>
                        <span className="inputLabel">タイトル</span>
                        <input
                            type="text"
                            onChange={e =>
                                handleChange({
                                    target: {
                                        name: "title",
                                        value: e.target.value
                                    }
                                })
                            }
                            value={input.title ?? ""}
                            maxLength={32}
                        />
                    </li>
                    <li>
                        <span className="inputLabel">自社担当</span>
                        <div className="selectBox">
                            <select
                                onChange={e =>
                                    handleChange({
                                        target: {
                                            name: "manager_id",
                                            value: e.target.value
                                        }
                                    })
                                }
                                value={input?.manager_id ?? ""}
                            >
                                {staffs &&
                                    Object.keys(staffs)
                                        .sort()
                                        .map((val, index) => (
                                            <option key={index} value={val}>
                                                {staffs[val]}
                                            </option>
                                        ))}
                            </select>
                        </div>
                    </li>
                </ul>
                <ul className="sideList half mb25">
                    <li>
                        <span className="inputLabel">受付日</span>
                        <div className="calendar">
                            <Flatpickr
                                theme="airbnb"
                                value={input?.reception_date}
                                onChange={(
                                    selectedDates,
                                    dateStr,
                                    instance
                                ) => {
                                    handleChange({
                                        target: {
                                            name: "reception_date",
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
                            />
                        </div>
                    </li>
                    <li>
                        <span className="inputLabel">種別</span>
                        <div className="selectBox">
                            <select
                                onChange={e =>
                                    handleChange({
                                        target: {
                                            name: "kind",
                                            value: e.target.value
                                        }
                                    })
                                }
                                value={input.kind ?? ""}
                            >
                                {kinds &&
                                    Object.keys(kinds)
                                        .sort((a, b) => a - b)
                                        .map((val, index) => (
                                            <option key={index} value={val}>
                                                {kinds[val]}
                                            </option>
                                        ))}
                            </select>
                        </div>
                    </li>
                    <li>
                        <span className="inputLabel">期限</span>
                        <div className="calendar">
                            <Flatpickr
                                theme="airbnb"
                                value={input?.deadline}
                                onChange={(
                                    selectedDates,
                                    dateStr,
                                    instance
                                ) => {
                                    handleChange({
                                        target: {
                                            name: "deadline",
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
                            />
                        </div>
                    </li>
                    <li>
                        <span className="inputLabel">ステータス</span>
                        <div className="selectBox">
                            <select
                                onChange={e =>
                                    handleChange({
                                        target: {
                                            name: "status",
                                            value: e.target.value
                                        }
                                    })
                                }
                                value={input.status ?? ""}
                            >
                                {statuses &&
                                    Object.keys(statuses)
                                        .sort()
                                        .map((val, index) => (
                                            <option key={index} value={val}>
                                                {statuses[val]}
                                            </option>
                                        ))}
                            </select>
                        </div>
                    </li>
                    {/**カスタム項目 */}
                    {_.filter(customFields, {
                        display_position:
                            customFieldPositions.consultation_custom
                    }).map((row, index) => {
                        return (
                            <CustomField
                                key={index}
                                customCategoryCode={
                                    userCustomCategoryCodes.consultation
                                }
                                type={row?.type}
                                inputType={row?.input_type}
                                label={row?.name}
                                name={row?.key}
                                list={row?.list}
                                value={input?.[row?.key]}
                                handleChange={handleChange}
                                uneditItem={row?.unedit_item}
                            />
                        );
                    })}
                </ul>
                <ul className="baseList mb25">
                    <li>
                        <span className="inputLabel">内容</span>
                        <textarea
                            rows="3"
                            onChange={e =>
                                handleChange({
                                    target: {
                                        name: "contents",
                                        value: e.target.value
                                    }
                                })
                            }
                            value={input.contents ?? ""}
                        ></textarea>
                    </li>
                </ul>
                <ul className="sideList">
                    <li className="wd50">
                        <button className="grayBtn js-modal-close">
                            閉じる
                        </button>
                    </li>
                    <li className="wd50 mr00">
                        <button
                            className="blueBtn"
                            disabled={isEditing}
                            onClick={handleSubmit}
                        >
                            {modalMode === "create" ? "登録する" : "更新する"}
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    );
};

export default ConsultationModal;
