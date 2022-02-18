import React from "react";
import ConstApp from "./components/ConstApp";
import { render } from "react-dom";
import VisaInputArea from "./components/User/VisaInputArea";
import MileageInputArea from "./components/User/MileageInputArea";
import MemberCardInputArea from "./components/User/MemberCardInputArea";
import ContactInputArea from "./components/User/ContactInputArea";
import DisableContactInputArea from "./components/User/DisableContactInputArea";
import AddressInputArea from "./components/User/AddressInputArea";

// 住所入力エリア
const AdrElm = document.getElementById("addressInputArea");
if (AdrElm) {
    const jsVars = AdrElm.getAttribute("jsVars");
    const parsedJsVars = jsVars && JSON.parse(jsVars);
    const defaultValue = AdrElm.getAttribute("defaultValue");
    const parsedDefaultValue = defaultValue && JSON.parse(defaultValue);
    const formSelects = AdrElm.getAttribute("formSelects");
    const parsedFormSelects = formSelects && JSON.parse(formSelects);
    render(
        <ConstApp jsVars={parsedJsVars}>
            <AddressInputArea
                defaultValue={parsedDefaultValue}
                formSelects={parsedFormSelects}
            />
        </ConstApp>,
        document.getElementById("addressInputArea")
    );
}

// 連絡先入力エリア
const ContactElm = document.getElementById("contactInputArea");
if (ContactElm) {
    const jsVars = ContactElm.getAttribute("jsVars");
    const parsedJsVars = jsVars && JSON.parse(jsVars);
    const defaultValue = ContactElm.getAttribute("defaultValue");
    const parsedDefaultValue = defaultValue && JSON.parse(defaultValue);
    render(
        <ConstApp jsVars={parsedJsVars}>
            <ContactInputArea defaultValue={parsedDefaultValue} />
        </ConstApp>,
        document.getElementById("contactInputArea")
    );
}

// 編集不可の連絡先入力エリア(WebUser用)
const DisableContactElm = document.getElementById("disableContactInputArea");
if (DisableContactElm) {
    const jsVars = DisableContactElm.getAttribute("jsVars");
    const parsedJsVars = jsVars && JSON.parse(jsVars);
    const defaultValue = DisableContactElm.getAttribute("defaultValue");
    const parsedDefaultValue = defaultValue && JSON.parse(defaultValue);
    render(
        <ConstApp jsVars={parsedJsVars}>
            <DisableContactInputArea defaultValue={parsedDefaultValue} />
        </ConstApp>,
        document.getElementById("disableContactInputArea")
    );
}

// ビザ入力エリア
const VisaElm = document.getElementById("visaInputArea");
if (VisaElm) {
    const jsVars = VisaElm.getAttribute("jsVars");
    const parsedJsVars = jsVars && JSON.parse(jsVars);
    const defaultValue = VisaElm.getAttribute("defaultValue");
    const parsedDefaultValue = defaultValue && JSON.parse(defaultValue);
    const formSelects = VisaElm.getAttribute("formSelects");
    const parsedFormSelects = formSelects && JSON.parse(formSelects);
    const customCategoryCode = VisaElm.getAttribute("customCategoryCode");
    const visaUserCustomItems = VisaElm.getAttribute("visaUserCustomItems");
    const parsedVisaUserCustomItems =
        visaUserCustomItems && JSON.parse(visaUserCustomItems);
    const consts = VisaElm.getAttribute("consts");
    const parsedConsts = consts && JSON.parse(consts);

    render(
        <ConstApp jsVars={parsedJsVars}>
            <VisaInputArea
                defaultValue={parsedDefaultValue}
                formSelects={parsedFormSelects}
                customCategoryCode={customCategoryCode}
                visaUserCustomItems={parsedVisaUserCustomItems}
                consts={parsedConsts}
            />
        </ConstApp>,
        document.getElementById("visaInputArea")
    );
}

// マイレージ入力エリア
const MileageElm = document.getElementById("mileageInputArea");
if (MileageElm) {
    const jsVars = MileageElm.getAttribute("jsVars");
    const parsedJsVars = jsVars && JSON.parse(jsVars);
    const defaultValue = MileageElm.getAttribute("defaultValue");
    const parsedDefaultValue = defaultValue && JSON.parse(defaultValue);
    const customCategoryCode = MileageElm.getAttribute("customCategoryCode");
    const formSelects = MileageElm.getAttribute("formSelects");
    const parsedFormSelects = formSelects && JSON.parse(formSelects);
    const mileageUserCustomItems = MileageElm.getAttribute(
        "mileageUserCustomItems"
    );
    const parsedMileageUserCustomItems =
        mileageUserCustomItems && JSON.parse(mileageUserCustomItems);
    const consts = MileageElm.getAttribute("consts");
    const parsedConsts = consts && JSON.parse(consts);

    render(
        <ConstApp jsVars={parsedJsVars}>
            <MileageInputArea
                defaultValue={parsedDefaultValue}
                formSelects={parsedFormSelects}
                mileageUserCustomItems={parsedMileageUserCustomItems}
                customCategoryCode={customCategoryCode}
                consts={parsedConsts}
            />
        </ConstApp>,
        document.getElementById("mileageInputArea")
    );
}

// メンバーカード入力エリア
const MemberCardElm = document.getElementById("memberCardInputArea");
if (MemberCardElm) {
    const jsVars = MemberCardElm.getAttribute("jsVars");
    const parsedJsVars = jsVars && JSON.parse(jsVars);
    const defaultValue = MemberCardElm.getAttribute("defaultValue");
    const parsedDefaultValue = defaultValue && JSON.parse(defaultValue);
    const customCategoryCode = MemberCardElm.getAttribute("customCategoryCode");
    const formSelects = MemberCardElm.getAttribute("formSelects");
    const parsedFormSelects = formSelects && JSON.parse(formSelects);
    const memberCardUserCustomItems = MemberCardElm.getAttribute(
        "memberCardUserCustomItems"
    );
    const parsedMemberCardUserCustomItems =
        memberCardUserCustomItems && JSON.parse(memberCardUserCustomItems);
    const consts = MemberCardElm.getAttribute("consts");
    const parsedConsts = consts && JSON.parse(consts);

    render(
        <ConstApp jsVars={parsedJsVars}>
            <MemberCardInputArea
                defaultValue={parsedDefaultValue}
                formSelects={parsedFormSelects}
                customCategoryCode={customCategoryCode}
                memberCardUserCustomItems={parsedMemberCardUserCustomItems}
                consts={parsedConsts}
            />
        </ConstApp>,
        document.getElementById("memberCardInputArea")
    );
}
