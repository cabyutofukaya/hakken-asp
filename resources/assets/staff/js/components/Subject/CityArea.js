import React, { useState } from "react";
import CityInput from "./CityInput";

const CityArea = ({ defaultValue }) => {
    const [input, setInput] = useState({ ...defaultValue });

    // 出発地・目的地変更
    const handleChange = ({ name, value }) => {
        setInput({ ...input, [name]: value });
    };

    return (
        <>
            <li>
                <span className="inputLabel">出発地</span>
                <CityInput
                    name="departure_id"
                    defaultValue={input?.departure}
                    defaultOptions={[]}
                    handleAreaChange={handleChange}
                />
            </li>
            <li>
                <span className="inputLabel">目的地</span>
                <CityInput
                    name="destination_id"
                    defaultValue={input?.destination}
                    defaultOptions={[]}
                    handleAreaChange={handleChange}
                />
            </li>
        </>
    );
};

export default CityArea;
