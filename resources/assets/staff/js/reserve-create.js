import _ from "lodash";
import React, { useState, useContext } from "react";
import ConstApp from "./components/ConstApp";
import { ConstContext } from "./components/ConstApp";
import { render } from "react-dom";
import BasicInfoInputArea from "./components/Reserve/BasicInfoInputArea";
import CustomFieldInputArea from "./components/Reserve/CustomFieldInputArea";

const ReserveInputArea = ({
    defaultValue,
    userAddModalDefaultValue,
    formSelects,
    consts,
    customCategoryCode,
    customFields
}) => {
    const { agencyAccount } = useContext(ConstContext);

    const [input, setInput] = useState(defaultValue);

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

    render(
        <ConstApp jsVars={parsedJsVars}>
            <ReserveInputArea
                formSelects={parsedFormSelects}
                defaultValue={parsedDefaultValue}
                userAddModalDefaultValue={parsedUserAddModalDefaultValue}
                consts={parsedConsts}
                customFields={parsedCustomFields}
                customCategoryCode={customCategoryCode}
            />
        </ConstApp>,
        document.getElementById("reserveInputArea")
    );
}
