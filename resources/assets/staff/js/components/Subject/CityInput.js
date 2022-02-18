import React, { useState, useContext } from "react";
import { ConstContext } from "../ConstApp";
import AsyncSelect from "react-select/async";

function CityInput({ name, defaultValue, defaultOptions, handleAreaChange }) {
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
            .get(`/api/${agencyAccount}/city/search`, {
                params: {
                    city: inputValue
                }
            })
            .then(response => {
                callback(
                    response.data.data.map(({ id, code, name }) => {
                        return { label: `${code}${name}`, value: id };
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
                placeholder="都市・空港"
            />
        </>
    );
}

export default CityInput;
