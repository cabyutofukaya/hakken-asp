import React from "react";
import { render } from "react-dom";
import ManagerInputArea from "./components/BusinessUser/ManagerInputArea";
import AddressInputArea from "./components/BusinessUser/AddressInputArea";

// 住所入力エリア
const AdrElm = document.getElementById("addressInputArea");
if (AdrElm) {
    const defaultValue = AdrElm.getAttribute("defaultValue");
    const parsedDefaultValue = defaultValue && JSON.parse(defaultValue);
    const formSelects = AdrElm.getAttribute("formSelects");
    const parsedFormSelects = formSelects && JSON.parse(formSelects);
    render(
        <AddressInputArea
            defaultValue={parsedDefaultValue}
            formSelects={parsedFormSelects}
        />,
        document.getElementById("addressInputArea")
    );
}

// 担当者入力エリア
const ManagerElm = document.getElementById("managerInputArea");
if (ManagerElm) {
    const defaultValue = ManagerElm.getAttribute("defaultValue");
    const parsedDefaultValue = defaultValue && JSON.parse(defaultValue);
    const formSelects = ManagerElm.getAttribute("formSelects");
    const parsedFormSelects = formSelects && JSON.parse(formSelects);
    render(
        <ManagerInputArea
            defaultValue={parsedDefaultValue}
            formSelects={parsedFormSelects}
        />,
        document.getElementById("managerInputArea")
    );
}
