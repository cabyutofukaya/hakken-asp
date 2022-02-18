import React from "react";
import { PARTICIPANT } from "../../constants";

// 参加者表示・非表示のチェックボックス
const ParticipantCheckSettingArea = ({
    participants,
    checkIds,
    handleChange
}) => {
    return (
        <>
            <span className="inputLabel">参加者</span>
            <ul className="checkList">
                {participants &&
                    Object.keys(participants).map((k, i) => (
                        <li className="checkBox" key={i}>
                            <input
                                type="checkbox"
                                id={`participant${i}`}
                                value={participants[k]["id"]}
                                onChange={handleChange}
                                checked={checkIds.includes(
                                    participants[k]["id"]
                                )}
                            />
                            <label htmlFor={`participant${i}`}>
                                {participants[k]["name"]}
                                {participants[k]?.["cancel"] &&
                                    PARTICIPANT.CANCEL_SUFFIX}
                            </label>
                        </li>
                    ))}
            </ul>
        </>
    );
};

export default ParticipantCheckSettingArea;
