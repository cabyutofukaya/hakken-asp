import _ from "lodash";
import React, { useState } from "react";
import ConstApp from "./components/ConstApp";
import { render } from "react-dom";
import BasicInfoInputArea from "./components/Reserve/BasicInfoInputArea";
import CustomFieldInputArea from "./components/Reserve/CustomFieldInputArea";
import { checkReturnDate } from "./libs";
import SuccessMessage from "./components/SuccessMessage";

const ReserveInputArea = ({
    defaultValue,
    userAddModalDefaultValue,
    formSelects,
    consts,
    customCategoryCode,
    customFields,
    flashMessage
}) => {
    const csrfToken = document.head.querySelector('meta[name="csrf-token"]')
        .content;

    const [input, setInput] = useState(defaultValue);

    const [successMessage, setSuccessMessage] = useState(
        flashMessage?.success_message ?? ""
    ); // 成功メッセージ。ページ遷移時のフラッシュメッセージがあれば初期状態でセット

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
            if (!checkReturnDate(input.return_date)) {
                // 帰着日が本日よりも前の日付の場合は警告
                if (
                    !confirm(
                        "帰着日が過去の日付で登録すると催行済に移動します。\nよろしいですか?"
                    )
                ) {
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
        history.back();
    };

    return (
        <>
            {/** API絡みのサクセスメッセージ */}
            <SuccessMessage
                message={successMessage}
                setMessage={setSuccessMessage}
            />

            <form
                name="reserveForm"
                action={consts.reserveStoreUrl}
                method="post"
                onSubmit={handleSubmit}
            >
                <input type="hidden" name="_token" value={csrfToken} />
                <BasicInfoInputArea
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
                    setSuccessMessage={setSuccessMessage}
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
                            登録せずに戻る
                        </button>
                    </li>
                    <li className="wd50">
                        <button className="blueBtn" disabled={isSubmitting}>
                            <span className="material-icons">save</span>{" "}
                            この内容で登録する
                        </button>
                    </li>
                </ul>
            </form>
        </>
    );
};
// 入力画面
const Element = document.getElementById("reserveInputArea");
if (Element) {
    const jsVars = Element.getAttribute("jsVars");
    const parsedJsVars = jsVars && JSON.parse(jsVars);
    const defaultValue = Element.getAttribute("defaultValue");
    const parsedDefaultValue = defaultValue && JSON.parse(defaultValue);
    const userAddModalDefaultValue = Element.getAttribute(
        "userAddModalDefaultValue"
    );
    const parsedUserAddModalDefaultValue =
        userAddModalDefaultValue && JSON.parse(userAddModalDefaultValue);
    const customCategoryCode = Element.getAttribute("customCategoryCode");
    const formSelects = Element.getAttribute("formSelects");
    const parsedFormSelects = formSelects && JSON.parse(formSelects);
    const consts = Element.getAttribute("consts");
    const parsedConsts = consts && JSON.parse(consts);
    const customFields = Element.getAttribute("customFields");
    const parsedCustomFields = customFields && JSON.parse(customFields);
    const flashMessage = Element.getAttribute("flashMessage");
    const parsedFlashMessage = flashMessage && JSON.parse(flashMessage);

    render(
        <ConstApp jsVars={parsedJsVars}>
            <ReserveInputArea
                formSelects={parsedFormSelects}
                defaultValue={parsedDefaultValue}
                userAddModalDefaultValue={parsedUserAddModalDefaultValue}
                consts={parsedConsts}
                customFields={parsedCustomFields}
                customCategoryCode={customCategoryCode}
                flashMessage={parsedFlashMessage}
            />
        </ConstApp>,
        document.getElementById("reserveInputArea")
    );
}
