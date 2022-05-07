import React from "react";
import TimeInput from "../TimeInput";

const ScheduleInputRows = ({ index, date, input, inputName, handleChange }) => {
    return (
        <>
            <li>
                <span className="inputLabel">到着時間</span>
                <TimeInput
                    name={`${inputName}[arrival_time]`}
                    value={input?.arrival_time ?? ""}
                    handleChange={e =>
                        handleChange(
                            {
                                target: {
                                    name: "arrival_time",
                                    value: e.target.value
                                }
                            },
                            date,
                            index
                        )
                    }
                    placeholder={index !== 0 ? "例）10:00" : ""}
                    disabled={index === 0}
                />
            </li>
            <li>
                <span className="inputLabel">滞在時間</span>
                <input
                    type="text"
                    value={input?.staying_time ?? ""}
                    name={`${inputName}[staying_time]`}
                    onChange={e =>
                        handleChange(
                            {
                                target: {
                                    name: "staying_time",
                                    value: e.target.value
                                }
                            },
                            date,
                            index
                        )
                    }
                    disabled={index === 0}
                />
            </li>
            <li>
                <span className="inputLabel">出発時間</span>
                <TimeInput
                    name={`${inputName}[departure_time]`}
                    value={input?.departure_time ?? ""}
                    handleChange={e =>
                        handleChange(
                            {
                                target: {
                                    name: "departure_time",
                                    value: e.target.value
                                }
                            },
                            date,
                            index
                        )
                    }
                    placeholder="例）10:00"
                />
            </li>
            <li>
                <span className="inputLabel">場所</span>
                <input
                    type="text"
                    value={input?.place ?? ""}
                    name={`${inputName}[place]`}
                    onChange={e =>
                        handleChange(
                            {
                                target: {
                                    name: "place",
                                    value: e.target.value
                                }
                            },
                            date,
                            index
                        )
                    }
                />
            </li>
            <li>
                <span className="inputLabel">説明</span>
                <input
                    type="text"
                    value={input?.explanation ?? ""}
                    name={`${inputName}[explanation]`}
                    onChange={e =>
                        handleChange(
                            {
                                target: {
                                    name: "explanation",
                                    value: e.target.value
                                }
                            },
                            date,
                            index
                        )
                    }
                />
            </li>
        </>
    );
};

export default ScheduleInputRows;
