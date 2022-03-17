import React from "react";
import classNames from "classnames";
// flatpickr
import "flatpickr/dist/themes/airbnb.css";
import { Japanese } from "flatpickr/dist/l10n/ja.js";
import Flatpickr from "react-flatpickr";
import OnlyNumberInput from "../OnlyNumberInput";

const ParticipantEditModal = ({
    id = "mdAddUser",
    input,
    handleChange,
    handleSubmit,
    sexes,
    ageKbns,
    birthdayYears,
    birthdayMonths,
    birthdayDays,
    countries,
    editMode,
    isEditing,
    permission
} = {}) => {
    return (
        <>
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
                        参加者{editMode === "edit" ? "編集" : "追加"}
                    </p>
                    <ul className="baseList mb40">
                        <li>
                            <span className="inputLabel">氏名</span>
                            <input
                                type="text"
                                value={input?.name ?? ""}
                                maxLength={32}
                                onChange={e =>
                                    handleChange({
                                        target: {
                                            name: "name",
                                            value: e.target.value
                                        }
                                    })
                                }
                            />
                        </li>
                        <li>
                            <span className="inputLabel">氏名(カナ)</span>
                            <input
                                type="text"
                                value={input?.name_kana ?? ""}
                                maxLength={32}
                                onChange={e =>
                                    handleChange({
                                        target: {
                                            name: "name_kana",
                                            value: e.target.value
                                        }
                                    })
                                }
                            />
                        </li>
                        <li>
                            <span className="inputLabel">氏名(ローマ字)</span>
                            <input
                                type="text"
                                value={input?.name_roman ?? ""}
                                maxLength={100}
                                onChange={e =>
                                    handleChange({
                                        target: {
                                            name: "name_roman",
                                            value: e.target.value
                                        }
                                    })
                                }
                            />
                        </li>
                        <li>
                            <span className="inputLabel">性別</span>
                            <ul className="baseRadio sideList">
                                {sexes &&
                                    Object.keys(sexes).map((v, index) => (
                                        <li className="wd20" key={index}>
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
                            <ul className="sideList">
                                <li className="wd80">
                                    <span className="inputLabel">生年月日</span>
                                    <div className="selectSet wd100">
                                        <div className="selectBox wd40 mr10">
                                            <select
                                                value={input?.birthday_y ?? ""}
                                                onChange={e =>
                                                    handleChange({
                                                        target: {
                                                            name: "birthday_y",
                                                            value:
                                                                e.target.value
                                                        }
                                                    })
                                                }
                                            >
                                                {birthdayYears &&
                                                    Object.keys(birthdayYears)
                                                        .sort((a, b) => a - b)
                                                        .map(v => (
                                                            <option
                                                                key={v}
                                                                value={v}
                                                            >
                                                                {
                                                                    birthdayYears[
                                                                        v
                                                                    ]
                                                                }
                                                            </option>
                                                        ))}
                                            </select>
                                        </div>
                                        <div className="selectBox wd30 mr10">
                                            <select
                                                value={input?.birthday_m ?? ""}
                                                onChange={e =>
                                                    handleChange({
                                                        target: {
                                                            name: "birthday_m",
                                                            value:
                                                                e.target.value
                                                        }
                                                    })
                                                }
                                            >
                                                {birthdayMonths &&
                                                    Object.keys(birthdayMonths)
                                                        .sort((a, b) => a - b)
                                                        .map(v => (
                                                            <option
                                                                key={v}
                                                                value={v}
                                                            >
                                                                {
                                                                    birthdayMonths[
                                                                        v
                                                                    ]
                                                                }
                                                            </option>
                                                        ))}
                                            </select>
                                        </div>
                                        <div className="selectBox wd30">
                                            <select
                                                value={input?.birthday_d ?? ""}
                                                onChange={e =>
                                                    handleChange({
                                                        target: {
                                                            name: "birthday_d",
                                                            value:
                                                                e.target.value
                                                        }
                                                    })
                                                }
                                            >
                                                {birthdayDays &&
                                                    Object.keys(birthdayDays)
                                                        .sort((a, b) => a - b)
                                                        .map(v => (
                                                            <option
                                                                key={v}
                                                                value={v}
                                                            >
                                                                {
                                                                    birthdayDays[
                                                                        v
                                                                    ]
                                                                }
                                                            </option>
                                                        ))}
                                            </select>
                                        </div>
                                    </div>
                                </li>
                                <li className="mt00 wd15">
                                    <span className="inputLabel">年齢</span>
                                    <OnlyNumberInput
                                        name="age"
                                        value={input?.age ?? ""}
                                        handleChange={handleChange}
                                        maxLength={3}
                                    />
                                </li>
                                <li className="mt00 wd20 mr00">
                                    <span className="inputLabel">年齢区分</span>
                                    <div className="selectBox">
                                        <select
                                            value={input?.age_kbn ?? ""}
                                            onChange={e =>
                                                handleChange({
                                                    target: {
                                                        name: "age_kbn",
                                                        value: e.target.value
                                                    }
                                                })
                                            }
                                            disabled={
                                                !permission?.participant_agekbn_update
                                            }
                                        >
                                            {ageKbns &&
                                                Object.keys(ageKbns).map(v => (
                                                    <option key={v} value={v}>
                                                        {ageKbns[v]}
                                                    </option>
                                                ))}
                                        </select>
                                    </div>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <span className="inputLabel">携帯電話</span>
                            <input
                                type="tel"
                                value={input?.mobile_phone ?? ""}
                                maxLength={100}
                                onChange={e =>
                                    handleChange({
                                        target: {
                                            name: "mobile_phone",
                                            value: e.target.value
                                        }
                                    })
                                }
                            />
                        </li>
                        <li>
                            <span className="inputLabel">備考</span>
                            <input
                                type="text"
                                value={input?.note ?? ""}
                                onChange={e =>
                                    handleChange({
                                        target: {
                                            name: "note",
                                            value: e.target.value
                                        }
                                    })
                                }
                            />
                        </li>
                    </ul>
                    <ul className="sideList half mb40">
                        <li>
                            <span className="inputLabel">旅券番号</span>
                            <input
                                type="text"
                                value={input?.passport_number ?? ""}
                                maxLength={100}
                                onChange={e =>
                                    handleChange({
                                        target: {
                                            name: "passport_number",
                                            value: e.target.value
                                        }
                                    })
                                }
                            />
                        </li>
                        <li>
                            <span className="inputLabel">旅券発行日</span>
                            <div className="calendar">
                                <Flatpickr
                                    theme="airbnb"
                                    value={input?.passport_issue_date ?? ""}
                                    onChange={(
                                        selectedDates,
                                        dateStr,
                                        instance
                                    ) => {
                                        handleChange({
                                            target: {
                                                name: "passport_issue_date",
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
                            <span className="inputLabel">旅券有効期限</span>
                            <div className="calendar">
                                <Flatpickr
                                    theme="airbnb"
                                    value={
                                        input?.passport_expiration_date ?? ""
                                    }
                                    onChange={(
                                        selectedDates,
                                        dateStr,
                                        instance
                                    ) => {
                                        handleChange({
                                            target: {
                                                name:
                                                    "passport_expiration_date",
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
                            <span className="inputLabel">旅券発行国</span>
                            <div className="selectBox">
                                <select
                                    value={
                                        input?.passport_issue_country_code ?? ""
                                    }
                                    onChange={e =>
                                        handleChange({
                                            target: {
                                                name:
                                                    "passport_issue_country_code",
                                                value: e.target.value
                                            }
                                        })
                                    }
                                >
                                    {countries &&
                                        Object.keys(countries).map(v => (
                                            <option key={v} value={v}>
                                                {countries[v]}
                                            </option>
                                        ))}
                                </select>
                            </div>
                        </li>
                        <li className="mr00">
                            <span className="inputLabel">国籍</span>
                            <div className="selectBox">
                                <select
                                    value={input?.citizenship_code ?? ""}
                                    onChange={e =>
                                        handleChange({
                                            target: {
                                                name: "citizenship_code",
                                                value: e.target.value
                                            }
                                        })
                                    }
                                >
                                    {countries &&
                                        Object.keys(countries).map(v => (
                                            <option key={v} value={v}>
                                                {countries[v]}
                                            </option>
                                        ))}
                                </select>
                            </div>
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
                                disabled={isEditing || input?.user?.is_deleted}
                                onClick={handleSubmit}
                            >
                                {editMode === "edit" ? "更新する" : "登録する"}
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </>
    );
};

export default ParticipantEditModal;
