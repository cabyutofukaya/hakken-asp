import React, { useState } from "react";
import { render } from "react-dom";
import "flatpickr/dist/themes/airbnb.css";
import { Japanese } from "flatpickr/dist/l10n/ja.js";
import Flatpickr from "react-flatpickr";
import KenAll from "ken-all";
import classNames from "classnames";
import { isObject } from "./libs";
import OnlyNumberInput from "./components/OnlyNumberInput";

const AgencyCreate = ({ consts, errors, defaultValue, formSelects }) => {
    const [account, setAccount] = useState(defaultValue.account ?? "");
    const [zipCode, setZipCode] = useState(defaultValue.zip_code ?? "");
    const [prefectureCode, setPrefectureCode] = useState(
        defaultValue.prefecture_code ?? ""
    );
    const [address1, setAddress1] = useState(defaultValue.address1 ?? "");
    const [fairTradeCouncil, setFairTradeCouncil] = useState(
        defaultValue?.fair_trade_council ?? 1
    );
    const [iata, setIata] = useState(defaultValue?.iata ?? 1);
    const [etbt, setEtbt] = useState(defaultValue?.etbt ?? 1);
    const [bondGuarantee, setBondGuarantee] = useState(
        defaultValue?.bond_guarantee ?? 1
    );
    const [trial, setTrial] = useState(defaultValue?.trial ?? 0);
    const [businessScope, setBusinessScope] = useState(
        _.get(defaultValue, "business_scope", "1")
    );
    const [registrationType, setRegistrationType] = useState(
        _.get(defaultValue, "registration_type", "1")
    );
    const [travelAgencyAssociation, setTravelAgencyAssociation] = useState(
        _.get(defaultValue, "travel_agency_association", "0")
    );
    const [capital, setCapital] = useState(defaultValue.capital ?? "");
    const [employeesNumber, setEmployeesNumber] = useState(
        defaultValue.employeesNumber ?? ""
    );

    const [accountCheck, setAccountCheck] = useState(null);
    // アカウント重複チェック
    const handleDuplicateCheck = e => {
        e.preventDefault();

        setAccountCheck(null);

        if (!account) {
            alert("アカウントを入力してください");
            return;
        }
        axios
            .post("/api/agency/is-account-exists", {
                account
            })
            .then(response => {
                if (response.data.exists === "no") {
                    setAccountCheck(true);
                }
            })
            .catch(error => {
                if (error.response) {
                    if (error.response?.status === 422) {
                        setAccountCheck(false);
                        // let msg = [];
                        // for (let key in error.response.data.errors) {
                        //     msg.push(error.response.data.errors[key][0]);
                        // }
                        // alert(msg.join("\n"));
                    }
                } else if (error.request) {
                    alert(error.request);
                } else {
                    alert("Error", error.message);
                }
            });
    };

    const handleAddressSearch = e => {
        e.preventDefault();
        if (!zipCode) {
            alert("郵便番号を入力してください");
            return;
        }
        if (!/^[0-9]{7}$/.test(zipCode)) {
            alert("郵便番号の入力形式が正しくありません");
            return;
        }

        KenAll(zipCode).then(res => {
            if (res.length > 0) {
                let adr = res[0];
                let index = Object.keys(formSelects.prefectureCodes).find(
                    index => formSelects.prefectureCodes[index] === adr[0]
                );
                setPrefectureCode(index);
                setAddress1(`${adr[1]}${adr[2]}`);
            }
        });
    };

    return (
        <div>
            {isObject(errors) && (
                <div id="errorMessage">
                    <p>
                        {Object.keys(errors).map(key => (
                            <span key={key}>
                                {errors[key]}
                                <br />
                            </span>
                        ))}
                    </p>
                </div>
            )}
            <div id="inputArea">
                <ul className="sideList">
                    <li className="wd30">
                        <span className="inputLabel">登録年月日</span>
                        <div className="calendar">
                            <Flatpickr
                                theme="airbnb"
                                value={defaultValue.registration_at ?? ""}
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
                                            name="registration_at"
                                            defaultValue={value ?? ""}
                                            ref={ref}
                                        />
                                    );
                                }}
                            />
                        </div>
                    </li>
                    <li className="wd40">
                        <span className="inputLabel">アカウントID</span>
                        <div className="buttonSet">
                            <div
                                className={classNames("wd60 check", {
                                    ok: accountCheck === true,
                                    ng: accountCheck === false
                                })}
                            >
                                <input
                                    type="text"
                                    name="account"
                                    defaultValue={defaultValue?.account}
                                    onChange={e => setAccount(e.target.value)}
                                />
                            </div>
                            <button
                                className="orangeBtn wd40"
                                onClick={handleDuplicateCheck}
                            >
                                重複チェック
                            </button>
                        </div>
                    </li>
                    <li className="wd30">
                        <span className="inputLabel">
                            パスワード(管理ユーザー)
                        </span>
                        <input
                            type="text"
                            name="password"
                            defaultValue={defaultValue.password ?? ""}
                            placeholder="半角英数6~12。1つ以上の !#$%&=-"
                        />
                    </li>
                </ul>
                <ul className="sideList half">
                    <li>
                        <span className="inputLabel">社名</span>
                        <input
                            type="text"
                            name="company_name"
                            defaultValue={defaultValue.company_name ?? ""}
                        />
                    </li>
                    <li>
                        <span className="inputLabel">社名(カナ)</span>
                        <input
                            type="text"
                            name="company_kana"
                            defaultValue={defaultValue.company_kana ?? ""}
                        />
                    </li>
                </ul>
                <hr className="sepBorder" />
                <ul className="sideList half">
                    <li>
                        <span className="inputLabel">代表者名</span>
                        <input
                            type="text"
                            name="representative_name"
                            defaultValue={
                                defaultValue.representative_name ?? ""
                            }
                        />
                    </li>
                    <li>
                        <span className="inputLabel">代表者名(カナ)</span>
                        <input
                            type="text"
                            name="representative_kana"
                            defaultValue={
                                defaultValue.representative_kana ?? ""
                            }
                        />
                    </li>
                    <li>
                        <span className="inputLabel">担当者名</span>
                        <input
                            type="text"
                            name="person_in_charge_name"
                            defaultValue={
                                defaultValue.person_in_charge_name ?? ""
                            }
                        />
                    </li>
                    <li>
                        <span className="inputLabel">担当者名(カナ)</span>
                        <input
                            type="text"
                            name="person_in_charge_kana"
                            defaultValue={
                                defaultValue.person_in_charge_kana ?? ""
                            }
                        />
                    </li>
                </ul>
                <hr className="sepBorder" />
                <ul className="baseList">
                    <li className="wd60">
                        <span className="inputLabel">郵便番号</span>
                        <div className="buttonSet">
                            <input
                                type="text"
                                name="zip_code"
                                defaultValue={defaultValue.zip_code ?? ""}
                                onChange={e => setZipCode(e.target.value)}
                                maxLength={7}
                            />
                            <button
                                className="orangeBtn wd40"
                                onClick={handleAddressSearch}
                            >
                                検索
                            </button>
                        </div>
                    </li>
                    <li>
                        <span className="inputLabel">住所</span>
                        <div className="selectSet">
                            <div className="selectBox wd20">
                                <select
                                    name="prefecture_code"
                                    value={prefectureCode}
                                >
                                    {formSelects?.prefectureCodes &&
                                        Object.keys(formSelects.prefectureCodes)
                                            .sort()
                                            .map(k => (
                                                <option value={k} key={k}>
                                                    {
                                                        formSelects
                                                            .prefectureCodes[k]
                                                    }
                                                </option>
                                            ))}
                                </select>
                            </div>
                            <input
                                type="text"
                                name="address1"
                                className="wd80"
                                value={address1}
                                onChange={e => setAddress1(e.target.value)}
                            />
                        </div>
                    </li>
                    <li>
                        <span className="inputLabel">ビル・建物名</span>
                        <input
                            type="text"
                            name="address2"
                            defaultValue={defaultValue.address2 ?? ""}
                        />
                    </li>
                </ul>
                <hr className="sepBorder" />
                <ul className="sideList half">
                    <li>
                        <span className="inputLabel">電話番号</span>
                        <input
                            type="text"
                            name="tel"
                            defaultValue={defaultValue.tel ?? ""}
                        />
                    </li>
                    <li>
                        <span className="inputLabel">FAX番号</span>
                        <input
                            type="text"
                            name="fax"
                            defaultValue={defaultValue.fax ?? ""}
                        />
                    </li>
                    <li>
                        <span className="inputLabel">メールアドレス</span>
                        <input
                            type="email"
                            name="email"
                            defaultValue={defaultValue.email ?? ""}
                        />
                    </li>
                    <li>
                        <span className="inputLabel">緊急連絡先</span>
                        <input
                            type="tel"
                            name="emergency_contact"
                            defaultValue={defaultValue.emergency_contact ?? ""}
                        />
                    </li>
                </ul>
                <hr className="sepBorder" />
                <ul className="sideList">
                    <li className="wd30">
                        <span className="inputLabel">設立年月日</span>
                        <div className="calendar">
                            <Flatpickr
                                theme="airbnb"
                                value={defaultValue.establishment_at ?? ""}
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
                                            name="establishment_at"
                                            defaultValue={value ?? ""}
                                            ref={ref}
                                        />
                                    );
                                }}
                            />
                        </div>
                    </li>
                    <li className="wd30">
                        <span className="inputLabel">資本金</span>
                        <div className="unit uPrice">
                            <OnlyNumberInput
                                type="text"
                                name="capital"
                                maxLength={10}
                                value={capital}
                                handleChange={e => setCapital(e.target.value)}
                            />
                        </div>
                    </li>
                    <li className="wd20">
                        <span className="inputLabel">従業員数</span>
                        <div className="unit uNum">
                            <OnlyNumberInput
                                type="text"
                                name="employees_number"
                                maxLength={10}
                                value={employeesNumber}
                                handleChange={e =>
                                    setEmployeesNumber(e.target.value)
                                }
                            />
                        </div>
                    </li>
                </ul>
                <hr className="sepBorder" />
                <ul className="sideList">
                    <li className="wd30">
                        <span className="inputLabel">旅行業登録年月日</span>
                        <div className="calendar">
                            <Flatpickr
                                theme="airbnb"
                                value={
                                    defaultValue.travel_agency_registration_at ??
                                    ""
                                }
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
                                            name="travel_agency_registration_at"
                                            defaultValue={value ?? ""}
                                            ref={ref}
                                        />
                                    );
                                }}
                            />
                        </div>
                    </li>
                    <li className="wd20">
                        <span className="inputLabel">業務範囲</span>
                        <div className="selectBox">
                            <select
                                name="business_scope"
                                value={businessScope}
                                onChange={e => setBusinessScope(e.target.value)}
                            >
                                {formSelects?.businessScopes &&
                                    Object.keys(formSelects.businessScopes)
                                        .sort()
                                        .map(k => (
                                            <option value={k} key={k}>
                                                {formSelects.businessScopes[k]}
                                            </option>
                                        ))}
                            </select>
                        </div>
                    </li>
                </ul>
                <ul className="sideList">
                    <li className="wd30">
                        <span className="inputLabel">登録行政庁名</span>
                        <input
                            type="text"
                            name="registered_administrative_agency"
                            defaultValue={
                                defaultValue.registered_administrative_agency ??
                                ""
                            }
                        />
                    </li>
                    <li className="wd20">
                        <span className="inputLabel">登録種別</span>
                        <div className="selectBox">
                            <select
                                name="registration_type"
                                value={registrationType}
                                onChange={e =>
                                    setRegistrationType(e.target.value)
                                }
                            >
                                {formSelects?.registrationTypes &&
                                    Object.keys(formSelects.registrationTypes)
                                        .sort()
                                        .map(k => (
                                            <option value={k} key={k}>
                                                {
                                                    formSelects
                                                        .registrationTypes[k]
                                                }
                                            </option>
                                        ))}
                            </select>
                        </div>
                    </li>
                    <li className="wd50 mr00">
                        <span className="inputLabel">登録番号</span>
                        <input
                            type="text"
                            name="registration_number"
                            defaultValue={
                                defaultValue.registration_number ?? ""
                            }
                        />
                    </li>
                </ul>
                <ul className="sideList">
                    <li className="wd20">
                        <span className="inputLabel">旅行業協会</span>
                        <div className="selectBox">
                            <select
                                name="travel_agency_association"
                                value={travelAgencyAssociation}
                                onChange={e =>
                                    setTravelAgencyAssociation(e.target.value)
                                }
                            >
                                {formSelects?.travelAgencyAssociations &&
                                    Object.keys(
                                        formSelects.travelAgencyAssociations
                                    )
                                        .sort()
                                        .map(k => (
                                            <option value={k} key={k}>
                                                {
                                                    formSelects
                                                        .travelAgencyAssociations[
                                                        k
                                                    ]
                                                }
                                            </option>
                                        ))}
                            </select>
                        </div>
                    </li>
                    <li className="wd20">
                        <span className="inputLabel">旅行取協</span>
                        <ul className="slideRadio">
                            <li>
                                <input
                                    id="fair_trade_council_1"
                                    type="radio"
                                    name="fair_trade_council"
                                    value="1"
                                    checked={fairTradeCouncil == 1}
                                    onChange={e => setFairTradeCouncil(1)}
                                />
                                <label htmlFor="fair_trade_council_1">
                                    あり
                                </label>
                            </li>
                            <li>
                                <input
                                    id="fair_trade_council_0"
                                    type="radio"
                                    name="fair_trade_council"
                                    value="0"
                                    checked={fairTradeCouncil == 0}
                                    onChange={e => setFairTradeCouncil(0)}
                                />
                                <label htmlFor="fair_trade_council_0">
                                    なし
                                </label>
                            </li>
                        </ul>
                    </li>
                    <li className="wd20">
                        <span className="inputLabel">IATA加入</span>
                        <ul className="slideRadio">
                            <li>
                                <input
                                    id="iata_1"
                                    type="radio"
                                    name="iata"
                                    value="1"
                                    checked={iata == 1}
                                    onChange={e => setIata(1)}
                                />
                                <label htmlFor="iata_1">あり</label>
                            </li>
                            <li>
                                <input
                                    id="iata_0"
                                    type="radio"
                                    name="iata"
                                    value="0"
                                    checked={iata == 0}
                                    onChange={e => setIata(0)}
                                />
                                <label htmlFor="iata_0">なし</label>
                            </li>
                        </ul>
                    </li>
                    <li className="wd20">
                        <span className="inputLabel">e-TBT加入</span>
                        <ul className="slideRadio">
                            <li>
                                <input
                                    id="etbt_1"
                                    type="radio"
                                    name="etbt"
                                    value="1"
                                    checked={etbt == 1}
                                    onChange={e => setEtbt(1)}
                                />
                                <label htmlFor="etbt_1">あり</label>
                            </li>
                            <li>
                                <input
                                    id="etbt_0"
                                    type="radio"
                                    name="etbt"
                                    value="0"
                                    checked={etbt == 0}
                                    onChange={e => setEtbt(0)}
                                />
                                <label htmlFor="etbt_0">なし</label>
                            </li>
                        </ul>
                    </li>
                    <li className="wd20 mr00">
                        <span className="inputLabel">ポンド保証制度</span>
                        <ul className="slideRadio">
                            <li>
                                <input
                                    id="bond_guarantee_1"
                                    type="radio"
                                    name="bond_guarantee"
                                    value="1"
                                    checked={bondGuarantee == 1}
                                    onChange={e => setBondGuarantee(1)}
                                />
                                <label htmlFor="bond_guarantee_1">あり</label>
                            </li>
                            <li>
                                <input
                                    id="bond_guarantee_0"
                                    type="radio"
                                    name="bond_guarantee"
                                    value="0"
                                    checked={bondGuarantee == 0}
                                    onChange={e => setBondGuarantee(0)}
                                />
                                <label htmlFor="bond_guarantee_0">なし</label>
                            </li>
                        </ul>
                    </li>
                </ul>
                <hr className="sepBorder" />
                <ul className="sideList half">
                    <li>
                        <span className="inputLabel">旅行業約款</span>
                        <input
                            type="file"
                            name="upload_agreement_file"
                            accept=".pdf"
                            id="agreementFile"
                        />
                        <label htmlFor="agreementFile"></label>
                    </li>
                    <li>
                        <span className="inputLabel">
                            取引条件説明書面（共通事項）
                        </span>
                        <input
                            type="file"
                            name="upload_terms_file"
                            accept=".pdf"
                            id="termsFile"
                        />
                        <label htmlFor="termsFile"></label>
                    </li>
                </ul>
            </div>
            <h2 className="subTit">
                <span className="material-icons">local_police</span>契約内容
            </h2>
            <div id="inputSubArea">
                <ul className="baseList">
                    <li className="wd30">
                        <span className="inputLabel">自社担当</span>
                        <input
                            type="text"
                            name="manager"
                            defaultValue={defaultValue.manager ?? ""}
                        />
                    </li>
                </ul>
                <hr className="sepBorder" />
                <ul className="sideList">
                    <li className="wd20">
                        <span className="inputLabel">トライアル</span>
                        <ul className="slideRadio">
                            <li>
                                <input
                                    id="trial_1"
                                    type="radio"
                                    name="trial"
                                    value="1"
                                    checked={trial == 1}
                                    onChange={e => setTrial(1)}
                                />
                                <label htmlFor="trial_1">あり</label>
                            </li>
                            <li>
                                <input
                                    id="trial_0"
                                    type="radio"
                                    name="trial"
                                    value="0"
                                    checked={trial == 0}
                                    onChange={e => setTrial(0)}
                                />
                                <label htmlFor="trial_0">なし</label>
                            </li>
                        </ul>
                    </li>
                    <li
                        className={classNames("wd25 period", {
                            disable: trial == 0
                        })}
                    >
                        <span className="inputLabel">開始日</span>
                        <div className="calendar">
                            <Flatpickr
                                theme="airbnb"
                                value={defaultValue.trial_start_at ?? ""}
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
                                            name="trial_start_at"
                                            defaultValue={value ?? ""}
                                            ref={ref}
                                        />
                                    );
                                }}
                            />
                        </div>
                    </li>
                    <li
                        className={classNames("wd25", {
                            disable: trial == 0
                        })}
                    >
                        <span className="inputLabel">終了日</span>
                        <div className="calendar">
                            <Flatpickr
                                theme="airbnb"
                                value={defaultValue.trial_end_at ?? ""}
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
                                            name="trial_end_at"
                                            defaultValue={value ?? ""}
                                            ref={ref}
                                        />
                                    );
                                }}
                            />
                        </div>
                    </li>
                </ul>
                {/* <hr className="sepBorder" />
                <ul className="sideList">
                    <li className="wd40">
                        <span className="inputLabel">契約プラン</span>
                        <ul className="sideList">
                            <li className="wd50">
                                <button
                                    className="blueBtn js-modal-open"
                                    data-target="mdPlan"
                                >
                                    プランA(1年)
                                </button>
                            </li>
                            <li className="wd50">
                                <button
                                    className="blueBtn js-modal-open"
                                    data-target="mdPlan"
                                >
                                    プランB(1年)
                                </button>
                            </li>
                        </ul>
                    </li>
                </ul>
                <ul className="sideList listBd">
                    <li className="wd15">
                        <span className="inputLabel">契約プラン</span>
                        <span className="confTxt">プランA</span>
                    </li>

                    <li className="wd25 period">
                        <span className="inputLabel">開始日</span>
                        <div className="calendar">
                            <input type="text" />
                        </div>
                    </li>
                    <li className="wd25">
                        <span className="inputLabel">終了日</span>
                        <div className="calendar">
                            <input type="text" />{" "}
                        </div>
                    </li>
                    <li className="wd20">
                        <span className="inputLabel">月額利用料</span>
                        <div className="unit uPrice">
                            <input type="text" />
                        </div>
                    </li>
                </ul>

                <div id="optionPlan">
                    <ul>
                        <li>
                            契約アカウント数&nbsp;
                            <div>
                                <p>
                                    <span className="material-icons">
                                        people
                                    </span>
                                    15
                                </p>
                                <ul>
                                    <li>
                                        <button
                                            className="blueBtn js-modal-open"
                                            data-target="mdAcAgree"
                                        >
                                            追加
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li>
                            契約総容量
                            <div>
                                <p>
                                    <span className="material-icons">
                                        storage
                                    </span>
                                    1000MB<span>(60%使用中)</span>
                                </p>
                                <ul>
                                    <li>
                                        <button
                                            className="blueBtn js-modal-open"
                                            data-target="mdDbAgree"
                                        >
                                            追加
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    </ul>
                </div>
                <div className="planPrice">
                    当月請求額<span>25,000</span>円
                </div> */}
            </div>
            <ul id="formControl">
                <li className="wd50">
                    <button
                        className="grayBtn"
                        onClick={e => {
                            e.preventDefault();
                            location.href = consts.agencyIndexUrl;
                        }}
                    >
                        <span className="material-icons">arrow_back_ios</span>
                        登録せずに戻る
                    </button>
                </li>
                <li className="wd50">
                    <button className="blueBtn">
                        <span className="material-icons">save</span>{" "}
                        この内容で登録する
                    </button>
                </li>
            </ul>
        </div>
    );
};

const Element = document.getElementById("agencyCreate");
if (Element) {
    const errors = Element.getAttribute("errors");
    const parsedErrors = errors && JSON.parse(errors);
    const defaultValue = Element.getAttribute("defaultValue");
    const parsedDefaultValue = defaultValue && JSON.parse(defaultValue);
    const formSelects = Element.getAttribute("formSelects");
    const parsedFormSelects = formSelects && JSON.parse(formSelects);
    const consts = Element.getAttribute("consts");
    const parsedConsts = consts && JSON.parse(consts);

    render(
        <AgencyCreate
            errors={parsedErrors}
            defaultValue={parsedDefaultValue}
            formSelects={parsedFormSelects}
            consts={parsedConsts}
        />,
        document.getElementById("agencyCreate")
    );
}
