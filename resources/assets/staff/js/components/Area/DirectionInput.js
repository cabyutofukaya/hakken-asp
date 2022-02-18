import React, { useState, useContext } from "react";
import { ConstContext } from "../ConstApp";
import AsyncSelect from "react-select/async";

function DirectionInput({
    name,
    defaultValue,
    defaultOptions,
    handleAreaChange
}) {
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
            .get(`/api/${agencyAccount}/v_direction/search`, {
                params: {
                    v_direction: inputValue
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
                placeholder="方面"
            />
        </>
    );
}

export default DirectionInput;
