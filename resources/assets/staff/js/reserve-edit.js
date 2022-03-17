import _ from "lodash";
import ConstApp from "./components/ConstApp";
import React, { useState } from "react";
import { render } from "react-dom";
import BasicInfoInputArea from "./components/Reserve/BasicInfoInputArea";
import CustomFieldInputArea from "./components/Reserve/CustomFieldInputArea";
import { checkReturnDate } from "./libs";

const ReserveEditArea = ({
    isCanceled,
    applicationStep,
    defaultValue,
    userAddModalDefaultValue,
    formSelects,
    consts,
    customCategoryCode,
    customFields
}) => {
    const csrfToken = document.head.querySelector('meta[name="csrf-token"]')
        .content;

    const [input, setInput] = useState(defaultValue);

    const [isSubmitting, setIsSubmitting] = useState(false); // フォーム送信中か否か

    // 入力値変更
    const handleChange = e => {
        setInput({ ...input, [e.target.name]: e.target.value });
    };

    // 顧客番号クリア処理
    const clearApplicantUserNumber = () => {
        setInput({ ...input, applicant_user_number: "" });
    };

    // 出発地・目的地変更（AreaInputから呼び出される）
    const handleAreaChange = ({ name, value }) => {
        setInput({ ...input, [name]: value });
    };

    // 送信制御
    const handleSubmit = e => {
        e.preventDefault();
        if (input?.return_date) {
            if (!isCanceled && !checkReturnDate(input.return_date)) {
                // 帰着日が本日よりも前の日付の場合は警告（キャンセル予約の場合は催行済みに移動しないのでチェック不要）
                if (
                    confirm(
                        "帰着日が過去の日付で登録すると催行済に移動します。\nよろしいですか?"
                    )
                ) {
                    setIsSubmitting(true);
                    document.reserveForm.submit();
                } else {
                    setIsSubmitting(false);
                    return;
                }
            }
        }
        setIsSubmitting(true);
        document.reserveForm.submit();
    };

    // 戻る
    const handleBack = e => {
        e.preventDefault();
        location.href = consts.reserveDetailUrl;
    };

    return (
        <>
            <form
                name="reserveForm"
                action={consts.reserveUpdateUrl}
                method="post"
                onSubmit={handleSubmit}
            >
                <input type="hidden" name="_token" value={csrfToken} />
                <input type="hidden" name="_method" value="PUT" />
                <BasicInfoInputArea
                    applicationStep={applicationStep}
                    input={input}
                    setInput={setInput}
                    participantTypes={formSelects.participantTypes}
                    customerKbns={consts.customerKbns}
                    countries={formSelects.countries}
                    sexes={formSelects.sexes}
                    ageKbns={formSelects.ageKbns}
                    birthdayYears={formSelects.birthdayYears}
                    birthdayMonths={formSelects.birthdayMonths}
                    birthdayDays={formSelects.birthdayDays}
                    prefectures={formSelects.prefectures}
                    defaultAreas={formSelects.defaultAreas}
                    customFields={customFields}
                    customFieldPositions={consts.customFieldPositions}
                    customFieldCodes={consts.customFieldCodes}
                    customCategoryCode={customCategoryCode}
                    handleChange={handleChange}
                    handleAreaChange={handleAreaChange}
                    clearApplicantUserNumber={clearApplicantUserNumber}
                    userAddModalDefaultValue={userAddModalDefaultValue}
                />
                <h2 className="subTit">
                    <span className="material-icons"> playlist_add_check </span>
                    予約管理情報(カスタムフィールド)
                </h2>
                <CustomFieldInputArea
                    input={input}
                    handleChange={handleChange}
                    customFields={customFields}
                    customFieldPositions={consts.customFieldPositions}
                    staffs={formSelects?.staffs}
                    customCategoryCode={customCategoryCode}
                />
                <ul id="formControl">
                    <li className="wd50">
                        <button className="grayBtn" onClick={handleBack}>
                            <span className="material-icons">
                                arrow_back_ios
                            </span>
                            更新せずに戻る
                        </button>
                    </li>
                    <li className="wd50">
                        <button className="blueBtn" disabled={isSubmitting}>
                            <span className="material-icons">save</span>{" "}
                            この内容で更新する
                        </button>
                    </li>
                </ul>
            </form>
        </>
    );
};

// 入力画面
const Element = document.getElementById("reserveEditArea");
if (Element) {
    const jsVars = Element.getAttribute("jsVars");
    const parsedJsVars = jsVars && JSON.parse(jsVars);
    const isCanceled = Element.getAttribute("isCanceled");
    const applicationStep = Element.getAttribute("applicationStep");
    const defaultValue = Element.getAttribute("defaultValue");
    const parsedDefaultValue = defaultValue && JSON.parse(defaultValue);
    const customCategoryCode = Element.getAttribute("customCategoryCode");
    const userAddModalDefaultValue = Element.getAttribute(
        "userAddModalDefaultValue"
    );
    const parsedUserAddModalDefaultValue =
        userAddModalDefaultValue && JSON.parse(userAddModalDefaultValue);
    const formSelects = Element.getAttribute("formSelects");
    const parsedFormSelects = formSelects && JSON.parse(formSelects);
    const consts = Element.getAttribute("consts");
    const parsedConsts = consts && JSON.parse(consts);
    const customFields = Element.getAttribute("customFields");
    const parsedCustomFields = customFields && JSON.parse(customFields);

    render(
        <ConstApp jsVars={parsedJsVars}>
            <ReserveEditArea
                isCanceled={isCanceled}
                applicationStep={applicationStep}
                formSelects={parsedFormSelects}
                defaultValue={parsedDefaultValue}
                userAddModalDefaultValue={parsedUserAddModalDefaultValue}
                consts={parsedConsts}
                customFields={parsedCustomFields}
                customCategoryCode={customCategoryCode}
            />
        </ConstApp>,
        document.getElementById("reserveEditArea")
    );
}
