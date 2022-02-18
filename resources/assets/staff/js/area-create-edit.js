import React, { useState } from "react";
import { render } from "react-dom";
import DirectionInput from "./components/Area/DirectionInput";
import ConstApp from "./components/ConstApp";

const DirectionArea = ({ defaultValue }) => {
    const [input, setInput] = useState({ ...defaultValue });

    // 方面変更
    const handleChange = ({ name, value }) => {
        setInput({ ...input, [name]: value });
    };

    return (
        <>
            <span className="inputLabel">方面</span>
            <DirectionInput
                name="v_direction_uuid"
                defaultValue={input?.v_direction}
                defaultOptions={[]}
                handleAreaChange={handleChange}
            />
        </>
    );
};

// 方面
const DirectionElm = document.getElementById("directionArea");
if (DirectionElm) {
    const jsVars = DirectionElm.getAttribute("jsVars");
    const parsedJsVars = jsVars && JSON.parse(jsVars);
    const defaultValue = DirectionElm.getAttribute("defaultValue");
    const parsedDefaultValue = defaultValue && JSON.parse(defaultValue);

    render(
        <ConstApp jsVars={parsedJsVars}>
            <DirectionArea defaultValue={parsedDefaultValue} />
        </ConstApp>,
        document.getElementById("directionArea")
    );
}
