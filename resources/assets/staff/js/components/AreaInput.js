import React, { useState, useContext } from "react";
import { ConstContext } from "./ConstApp";
import AsyncSelect from "react-select/async";

function AreaInput({ name, defaultValue, defaultOptions, handleAreaChange }) {
    const { agencyAccount } = useContext(ConstContext);

    const [selectedOption, setSelectedOption] = useState(defaultValue);

    const onChange = value => {
        setSelectedOption(value);
        handleAreaChange({ name, value: value.value }); // 呼び出し元の入力値も更新
    };

    const loadOptions = (inputValue, callback) => {
        if (!inputValue) return;

        // setTimeout(() => {
        axios
            .get(`/api/${agencyAccount}/v_area/search`, {
                params: {
                    area: inputValue
                }
            })
            .then(response => {
                callback(
                    response.data.data.map(({ uuid, code, name }) => {
                        return { label: `${code}${name}`, value: uuid };
                    })
                );
            });
        // }, 1000);
    };

    return (
        <>
            <AsyncSelect
                cacheOptions
                name={name}
                value={selectedOption}
                onChange={onChange}
                defaultOptions={defaultOptions}
                loadOptions={loadOptions}
                placeholder="国・地域"
            />
        </>
    );
}

export default AreaInput;
