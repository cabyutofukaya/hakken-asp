import React, { useState } from "react";
import { render } from "react-dom";
import AreaInput from "./components/AreaInput";
import ConstApp from "./components/ConstApp";

const AreaArea = ({ defaultValue, defaultOptions }) => {
    const [input, setInput] = useState({ ...defaultValue });

    // 国・地域変更
    const handleChange = ({ name, value }) => {
        setInput({ ...input, [name]: value });
    };

    return (
        <>
            <span className="inputLabel">国・地域</span>
            <AreaInput
                name="v_area_uuid"
                defaultValue={input?.v_area}
                defaultOptions={defaultOptions}
                handleAreaChange={handleChange}
            />
        </>
    );
};

// 国・地域
const AreaElm = document.getElementById("areaArea");
if (AreaElm) {
    const jsVars = AreaElm.getAttribute("jsVars");
    const parsedJsVars = jsVars && JSON.parse(jsVars);
    const defaultValue = AreaElm.getAttribute("defaultValue");
    const parsedDefaultValue = defaultValue && JSON.parse(defaultValue);
    const formSelects = AreaElm.getAttribute("formSelects");
    const parsedFormSelects = formSelects && JSON.parse(formSelects);

    render(
        <ConstApp jsVars={parsedJsVars}>
            <AreaArea
                defaultValue={parsedDefaultValue}
                defaultOptions={parsedFormSelects?.defaultAreas}
            />
        </ConstApp>,
        document.getElementById("areaArea")
    );
}
