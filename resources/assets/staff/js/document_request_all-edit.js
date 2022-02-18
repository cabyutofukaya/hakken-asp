import React from "react";
import { render } from "react-dom";
import SealRow from "./components/SealRow";

const Element = document.getElementById("sealRow");
if (Element) {
    const defaultValue = Element.getAttribute("defaultValue");
    const parsedDefaultValue = defaultValue && JSON.parse(defaultValue);
    const formSelects = Element.getAttribute("formSelects");
    const parsedFormSelects = defaultValue && JSON.parse(formSelects);
    render(
        <SealRow
            defaultValue={parsedDefaultValue}
            formSelects={parsedFormSelects}
        />,
        document.getElementById("sealRow")
    );
}
