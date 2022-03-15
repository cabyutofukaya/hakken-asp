import React from "react";

const ScheduleInputRows = ({ index, date, input, inputName, handleChange }) => {
    return (
        <>
            {/**日付はPOSTで渡すname属性値になるが、name属性のバリデーションはできないので同じ値を隠しフィールドにセットして、その値を検証する(travel_date) */}
            {/* <input
                type="hidden"
                name={`${inputName}[id]`}
                value={input?.id ?? ""}
            />
            <input
                type="hidden"
                name={`${inputName}[type]`}
                value={input?.type ?? ""}
            />
            <input
                type="hidden"
                name={`${inputName}[seq]]`}
                value={index ?? ""}
            />
            <input
                type="hidden"
                name={`${inputName}[travel_date]]`}
                value={date ?? ""}
            /> */}
            <li>
                <span className="inputLabel">到着時間</span>
                <input
                    type="text"
                    value={input?.arrival_time ?? ""}
                    name={`${inputName}[arrival_time]`}
                    onChange={e =>
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
                    disabled={index === 0}
                />
                {/* <input
                    type="hidden"
                    name={`${inputName}[arrival_time]`}
                    value={index !== 0 ? input?.arrival_time ?? "" : ""}
                /> */}
                {/** indexが0だった場合は空フィールドがpostされるようにhiddenで上書き */}
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
                {/* <input
                    type="hidden"
                    name={`${inputName}[staying_time]`}
                    value={index !== 0 ? input?.staying_time ?? "" : ""}
                /> */}
                {/** indexが0だった場合は空フィールドがpostされるようにhiddenで上書き */}
            </li>
            <li>
                <span className="inputLabel">出発時間</span>
                <input
                    type="text"
                    value={input?.departure_time ?? ""}
                    name={`${inputName}[departure_time]`}
                    onChange={e =>
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
