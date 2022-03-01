import React from "react";
import ConstApp from "./components/ConstApp";
import { render } from "react-dom";
import CancelChargeArea from "./components/Reserve/CancelChargeArea";

const Element = document.getElementById("cancelChargeArea");
if (Element) {
    const jsVars = Element.getAttribute("jsVars");
    const parsedJsVars = jsVars && JSON.parse(jsVars);
    const consts = Element.getAttribute("consts");
    const parsedConsts = consts && JSON.parse(consts);
    const defaultValue = Element.getAttribute("defaultValue");
    const parsedDefaultValue = defaultValue && JSON.parse(defaultValue);
    const errors = Element.getAttribute("errors");
    const parsedErrors = errors && JSON.parse(errors);

    render(
        <ConstApp jsVars={parsedJsVars}>
            <CancelChargeArea
                consts={parsedConsts}
                defaultValue={parsedDefaultValue}
                errors={parsedErrors}
            />
        </ConstApp>,
        document.getElementById("cancelChargeArea")
    );
}
