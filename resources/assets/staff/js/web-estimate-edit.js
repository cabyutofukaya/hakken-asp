import _ from "lodash";
import React, { useState } from "react";
import ConstApp from "./components/ConstApp";
import { render } from "react-dom";
import WebBasicInfoInputArea from "./components/Reserve/WebBasicInfoInputArea";
import CustomFieldInputArea from "./components/Reserve/CustomFieldInputArea";

const EstimateEditArea = ({
    applicationStep,
    defaultValue,
    applicant,
    formSelects,
    consts,
    customCategoryCode,
    customFields
}) => {
    const [input, setInput] = useState(defaultValue);

    // 入力値変更
    const handleChange = e => {
        setInput({ ...input, [e.target.name]: e.target.value });
    };

    // 出発地・目的地変更（AreaInputから呼び出される）
    const handleAreaChange = ({ name, value }) => {
        setInput({ ...input, [name]: value });
    };

    return (
        <>
            <WebBasicInfoInputArea
                applicant={applicant}
                applicationStep={applicationStep}
                customCategoryCode={customCategoryCode}
                customFieldCodes={consts.customFieldCodes}
                customFieldPositions={consts.customFieldPositions}
                customFields={customFields}
                defaultAreas={formSelects.defaultAreas}
                handleAreaChange={handleAreaChange}
                handleChange={handleChange}
                input={input}
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
const Element = document.getElementById("estimateEditArea");
if (Element) {
    const jsVars = Element.getAttribute("jsVars");
    const parsedJsVars = jsVars && JSON.parse(jsVars);
    const applicationStep = Element.getAttribute("applicationStep");
    const defaultValue = Element.getAttribute("defaultValue");
    const parsedDefaultValue = defaultValue && JSON.parse(defaultValue);
    const applicant = Element.getAttribute("applicant");
    const parsedApplicant = applicant && JSON.parse(applicant);
    const customCategoryCode = Element.getAttribute("customCategoryCode");
    const formSelects = Element.getAttribute("formSelects");
    const parsedFormSelects = formSelects && JSON.parse(formSelects);
    const consts = Element.getAttribute("consts");
    const parsedConsts = consts && JSON.parse(consts);
    const customFields = Element.getAttribute("customFields");
    const parsedCustomFields = customFields && JSON.parse(customFields);

    render(
        <ConstApp jsVars={parsedJsVars}>
            <EstimateEditArea
                applicationStep={applicationStep}
                formSelects={parsedFormSelects}
                defaultValue={parsedDefaultValue}
                applicant={parsedApplicant}
                consts={parsedConsts}
                customFields={parsedCustomFields}
                customCategoryCode={customCategoryCode}
            />
        </ConstApp>,
        document.getElementById("estimateEditArea")
    );
}
