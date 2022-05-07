import React, { useState } from "react";
import KenAll from "ken-all";
import OnlyNumberInput from "../OnlyNumberInput";
import classNames from "classnames";
// flatpickr
import "flatpickr/dist/themes/airbnb.css";
import { Japanese } from "flatpickr/dist/l10n/ja.js";
import Flatpickr from "react-flatpickr";

const AddUserModal = ({
    id = "mdAddUser",
    input,
    sexes,
    ageKbns,
    birthdayYears,
    birthdayMonths,
    birthdayDays,
    prefectures,
    handleChange,
    handleAddressSearchResult,
    countries,
    isEditing = false,
    handleSubmit
} = {}) => {
    const [isSearching, setIsSearching] = useState(false); // 住所検索中フラグ

    // 住所検索
    const handleSearch = async e => {
        e.preventDefault();

        if (!input?.userable?.zip_code) {
            alert("郵便番号を入力してください");
            return;
        }
        if (!/^[0-9]{7}$/.test(input?.userable?.zip_code)) {
            alert("郵便番号の入力形式が正しくありません（半角数字7桁）");
            return;
        }

        if (isSearching) return;

        setIsSearching(true); // 検索中On

        const response = await KenAll(input.userable.zip_code).finally(() => {
            setIsSearching(false); // 検索中Off
        });

        if (response && response.length > 0) {
            let address = response[0];
            let code = Object.keys(prefectures).find(
                code => prefectures[code] === address[0]
            );

            handleAddressSearchResult({
                prefecture_code: code,
                address1: `${address[1]}${address[2]}`,
                address2: ""
            });
        } else {
            handleAddressSearchResult({
                prefecture_code: "",
                address1: "",
                address2: ""
            });
        }
    };

    //「登録する」ボタン押下
    const handleAddUser = e => {
        e.preventDefault();
        if (!input?.userable?.user_ext?.age_kbn) {
            if (!confirm("「年齢区分」が設定されていません。よろしいですか?")) {
                return;
            }
        }
        handleSubmit(e);
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
                <p className="mdTit mb20">新規顧客登録</p>
                <ul className="baseList mb40">
                    <li>
                        <span className="inputLabel">氏名</span>
                        <input
                            type="text"
                            value={input?.userable?.name ?? ""}
                            maxLength={32}
                            onChange={e =>
                                handleChange({
                                    target: {
                                        name: "userable[name]",
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
                            value={input?.userable?.name_kana ?? ""}
                            maxLength={32}
                            onChange={e =>
                                handleChange({
                                    target: {
                                        name: "userable[name_kana]",
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
                            value={input?.userable?.name_roman ?? ""}
                            maxLength={100}
                            onChange={e =>
                                handleChange({
                                    target: {
                                        name: "userable[name_roman]",
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
                                            checked={input?.userable?.sex === v}
                                            onChange={e =>
                                                handleChange({
                                                    target: {
                                                        name: "userable[sex]",
                                                        value: e.target.value
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
                                            value={
                                                input?.userable?.birthday_y ??
                                                ""
                                            }
                                            onChange={e =>
                                                handleChange({
                                                    target: {
                                                        name:
                                                            "userable[birthday_y]",
                                                        value: e.target.value
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
                                                            {birthdayYears[v]}
                                                        </option>
                                                    ))}
                                        </select>
                                    </div>
                                    <div className="selectBox wd30 mr10">
                                        <select
                                            value={
                                                input?.userable?.birthday_m ??
                                                ""
                                            }
                                            onChange={e =>
                                                handleChange({
                                                    target: {
                                                        name:
                                                            "userable[birthday_m]",
                                                        value: e.target.value
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
                                                            {birthdayMonths[v]}
                                                        </option>
                                                    ))}
                                        </select>
                                    </div>
                                    <div className="selectBox wd30">
                                        <select
                                            value={
                                                input?.userable?.birthday_d ??
                                                ""
                                            }
                                            onChange={e =>
                                                handleChange({
                                                    target: {
                                                        name:
                                                            "userable[birthday_d]",
                                                        value: e.target.value
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
                                                            {birthdayDays[v]}
                                                        </option>
                                                    ))}
                                        </select>
                                    </div>
                                </div>
                            </li>
                            <li className="mt00 wd15">
                                <span className="inputLabel">年齢</span>
                                <OnlyNumberInput
                                    name="userable[user_ext][age]"
                                    value={input?.userable?.user_ext?.age ?? ""}
                                    handleChange={handleChange}
                                    maxLength={3}
                                />
                            </li>
                            <li className="mt00 wd20 mr00">
                                <span className="inputLabel">年齢区分</span>
                                <div className="selectBox">
                                    <select
                                        value={
                                            input?.userable?.user_ext
                                                ?.age_kbn ?? ""
                                        }
                                        onChange={e =>
                                            handleChange({
                                                target: {
                                                    name:
                                                        "userable[user_ext][age_kbn]",
                                                    value: e.target.value
                                                }
                                            })
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
                            value={input?.userable?.mobile_phone ?? ""}
                            maxLength={32}
                            onChange={e =>
                                handleChange({
                                    target: {
                                        name: "userable[mobile_phone]",
                                        value: e.target.value
                                    }
                                })
                            }
                        />
                    </li>
                </ul>
                <hr className="sepBorder mb20" />
                <ul className="baseList">
                    <li className="wd40">
                        <span className="inputLabel">郵便番号</span>
                        <div className="buttonSet">
                            <OnlyNumberInput
                                name="zip_code"
                                value={input?.userable?.zip_code ?? ""}
                                handleChange={e =>
                                    handleChange({
                                        target: {
                                            name: "userable[zip_code]",
                                            value: e.target.value
                                        }
                                    })
                                }
                                maxLength={7}
                                className="wd60"
                            />
                            <button
                                className="orangeBtn wd40"
                                onClick={handleSearch}
                            >
                                検索
                            </button>
                        </div>
                    </li>
                    <li>
                        <span className="inputLabel">住所</span>
                        <div className="selectSet">
                            <div className="selectBox wd40">
                                <select
                                    value={
                                        input?.userable?.prefecture_code ?? ""
                                    }
                                    onChange={e =>
                                        handleChange({
                                            target: {
                                                name:
                                                    "userable[prefecture_code]",
                                                value: e.target.value
                                            }
                                        })
                                    }
                                >
                                    {prefectures &&
                                        Object.keys(prefectures)
                                            .sort((a, b) => a - b)
                                            .map(v => (
                                                <option key={v} value={v}>
                                                    {prefectures[v]}
                                                </option>
                                            ))}
                                </select>
                            </div>
                            <input
                                type="text"
                                value={input?.userable?.address1 ?? ""}
                                maxLength={100}
                                onChange={e =>
                                    handleChange({
                                        target: {
                                            name: "userable[address1]",
                                            value: e.target.value
                                        }
                                    })
                                }
                                className="wd80"
                            />
                        </div>
                    </li>
                    <li>
                        <span className="inputLabel">ビル・建物名</span>
                        <input
                            type="text"
                            value={input?.userable?.address2 ?? ""}
                            maxLength={100}
                            onChange={e =>
                                handleChange({
                                    target: {
                                        name: "userable[address2]",
                                        value: e.target.value
                                    }
                                })
                            }
                            className="wd80"
                        />
                    </li>
                </ul>
                <hr className="sepBorder mb20" />
                <ul className="sideList half mb40">
                    <li>
                        <span className="inputLabel">旅券番号</span>
                        <input
                            type="text"
                            value={input?.userable?.passport_number ?? ""}
                            maxLength={100}
                            onChange={e =>
                                handleChange({
                                    target: {
                                        name: "userable[passport_number]",
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
                                value={input?.userable?.passport_issue_date}
                                onChange={(
                                    selectedDates,
                                    dateStr,
                                    instance
                                ) => {
                                    handleChange({
                                        target: {
                                            name:
                                                "userable[passport_issue_date]",
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
                                    input?.userable?.passport_expiration_date
                                }
                                onChange={(
                                    selectedDates,
                                    dateStr,
                                    instance
                                ) => {
                                    handleChange({
                                        target: {
                                            name:
                                                "userable[passport_expiration_date]",
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
                                    input?.userable?.passport_issue_country_code
                                }
                                onChange={e =>
                                    handleChange({
                                        target: {
                                            name:
                                                "userable[passport_issue_country_code]",
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
                                value={input?.userable?.citizenship_code}
                                onChange={e =>
                                    handleChange({
                                        target: {
                                            name: "userable[citizenship_code]",
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
                            disabled={isEditing}
                            onClick={handleAddUser}
                        >
                            登録する
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    );
};

export default AddUserModal;
