import _ from "lodash";
import React, { useState } from "react";
import ConstApp from "./components/ConstApp";
import { render } from "react-dom";
import BasicInfoInputArea from "./components/Reserve/BasicInfoInputArea";
import CustomFieldInputArea from "./components/Reserve/CustomFieldInputArea";
import SuccessMessage from "./components/SuccessMessage";

const EstimateInputArea = ({
    defaultValue,
    userAddModalDefaultValue,
    formSelects,
    consts,
    customCategoryCode,
    customFields,
    flashMessage
}) => {
    const [input, setInput] = useState(defaultValue);

    const [successMessage, setSuccessMessage] = useState(
        flashMessage?.success_message ?? ""
    ); // 成功メッセージ。ページ遷移時のフラッシュメッセージがあれば初期状態でセット

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

    return (
        <>
            {/** API絡みのサクセスメッセージ */}
            <SuccessMessage
                message={successMessage}
                setMessage={setSuccessMessage}
            />

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
                見積管理情報(カスタムフィールド)
            </h2>

            <CustomFieldInputArea
                input={input}
                handleChange={handleChange}
                customFields={customFields}
                customFieldPositions={consts.customFieldPositions}
                staffs={formSelects?.staffs}
                customCategoryCode={customCategoryCode}
            />
        </>
    );
};
// 入力画面
const Element = document.getElementById("estimateInputArea");
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
            <EstimateInputArea
                formSelects={parsedFormSelects}
                defaultValue={parsedDefaultValue}
                userAddModalDefaultValue={parsedUserAddModalDefaultValue}
                consts={parsedConsts}
                customFields={parsedCustomFields}
                customCategoryCode={customCategoryCode}
                flashMessage={parsedFlashMessage}
            />
        </ConstApp>,
        document.getElementById("estimateInputArea")
    );
}
