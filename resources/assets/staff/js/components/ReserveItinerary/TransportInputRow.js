import React from "react";

const TransportInputRow = ({
    date,
    index,
    input,
    inputName,
    transportations,
    transportationTypes,
    handleChange
}) => {
    return (
        <div className="transport">
            <ul>
                <li>移動手段</li>
                <li className="selectBox">
                    <select
                        value={
                            input?.transportation ??
                            transportationTypes["default"]
                        }
                        name={`${inputName}[transportation]`}
                        onChange={e =>
                            handleChange(
                                {
                                    target: {
                                        name: "transportation",
                                        value: e.target.value
                                    }
                                },
                                date,
                                index
                            )
                        }
                    >
                        {transportations &&
                            Object.keys(transportations).map((val, index) => (
                                <option key={index} value={val}>
                                    {transportations[val]}
                                </option>
                            ))}
                    </select>
                </li>
                {/** 移動手段が「その他」の場合のみ表示 */}
                {input?.transportation === transportationTypes["others"] && (
                    <li>
                        <input
                            type="text"
                            value={input?.transportation_supplement ?? ""}
                            name={`${inputName}[transportation_supplement]`}
                            onChange={e =>
                                handleChange(
                                    {
                                        target: {
                                            name: "transportation_supplement",
                                            value: e.target.value
                                        }
                                    },
                                    date,
                                    index
                                )
                            }
                            placeholder="移動手段"
                        />
                    </li>
                )}
            </ul>
        </div>
    );
};

export default TransportInputRow;
