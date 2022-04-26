import _ from "lodash";
import React, { useState } from "react";
import ConstApp from "./components/ConstApp";
import { render } from "react-dom";
import WebBasicInfoInputArea from "./components/Reserve/WebBasicInfoInputArea";
import CustomFieldInputArea from "./components/Reserve/CustomFieldInputArea";
import { useMountedRef } from "../../hooks/useMountedRef";

const EstimateEditArea = ({
    applicationStep,
    defaultValue,
    applicant,
    formSelects,
    consts,
    customCategoryCode,
    customFields
}) => {
    const mounted = useMountedRef(); // マウント・アンマウント制御

    const csrfToken = document.head.querySelector('meta[name="csrf-token"]')
        .content;

    const [input, setInput] = useState(defaultValue);

    const [isSubmitting, setIsSubmitting] = useState(false); // フォーム送信中か否か

    // 入力値変更
    const handleChange = e => {
        setInput({ ...input, [e.target.name]: e.target.value });
    };

    // 出発地・目的地変更（AreaInputから呼び出される）
    const handleAreaChange = ({ name, value }) => {
        setInput({ ...input, [name]: value });
    };

    // 送信制御
    const handleSubmit = async e => {
        e.preventDefault();

        if (
            defaultValue?.departure_date &&
            defaultValue?.return_date &&
            $("[name=departure_date]").val() &&
            $("[name=return_date]").val()
        ) {
            // 出発日が後ろ倒し or 帰着日が前倒しの場合は警告を出す
            if (
                defaultValue.departure_date <
                    $("[name=departure_date]").val() ||
                defaultValue.return_date > $("[name=return_date]").val()
            ) {
                if (
                    confirm(
                        "旅行日が変更されています。行程を作成している場合、旅行日から外れた日程は削除されます。よろしいですか?"
                    )
                ) {
                    setIsSubmitting(true);
                    document.reserveForm.submit();
                    return;
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
        location.href = consts.estimateDetailUrl;
    };

    return (
        <>
            <form
                name="reserveForm"
                action={consts.estimateUpdateUrl}
                method="post"
                onSubmit={handleSubmit}
            >
                <input type="hidden" name="_token" value={csrfToken} />
                <input type="hidden" name="_method" value="PUT" />

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
