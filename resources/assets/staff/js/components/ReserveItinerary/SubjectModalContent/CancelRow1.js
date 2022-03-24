import React from "react";
import OnlyNumberInput from "../../OnlyNumberInput";

/**
 * 仕入科目モーダルのキャンセル枠
 * オプション科目、航空券科目用
 *
 * @param {int} index 参加者リストの行番号
 * @returns
 */
const CancelRow1 = ({ index, data, participant, handleChange }) => {
    return (
        <tr>
            <td>
                <div className="checkBox">
                    <input
                        type="checkbox"
                        value={data?.is_cancel ?? "0"}
                        id={`cancel_participant${index}`}
                        onChange={e =>
                            handleChange({
                                type: "CHANGE_PARTICIPANT_CHECKBOX",
                                index,
                                name: "is_cancel",
                                payload: e.target.value
                            })
                        }
                        checked={data?.is_cancel}
                    />
                    <label htmlFor={`cancel_participant${index}`}></label>
                </div>
            </td>
            <td>{data?.seat ?? "-"}</td>
            <td>{data?.reference_number ?? "-"}</td>
            <td>
                {participant?.name ?? "-"}
                {participant?.name_kana && <>({participant.name_kana})</>}
            </td>
            <td className="txtalc">{participant?.sex_label ?? "-"}</td>
            <td className="txtalc">{participant?.age ?? "-"}</td>
            <td className="txtalc">{participant?.age_kbn_label ?? "-"}</td>
            <td>
                <OnlyNumberInput
                    value={data?.cancel_charge ?? 0}
                    handleChange={e =>
                        handleChange({
                            type: "CHANGE_PARTICIPANT_PRICE_INPUT",
                            index,
                            name: "cancel_charge",
                            payload: e.target.value
                        })
                    }
                />
            </td>
            <td>
                <OnlyNumberInput
                    value={data?.cancel_charge_net ?? 0}
                    handleChange={e =>
                        handleChange({
                            type: "CHANGE_PARTICIPANT_PRICE_INPUT",
                            index,
                            name: "cancel_charge_net",
                            payload: e.target.value
                        })
                    }
                />
            </td>
            <td>-</td>
            <td>-</td>
            <td>{data?.cost ?? 0}</td>
            <td className="txtalc">{data?.commission_rate ?? 0}%</td>
            <td>
                <OnlyNumberInput
                    value={data?.cancel_charge_profit ?? 0}
                    handleChange={e =>
                        handleChange({
                            type: "CHANGE_PARTICIPANT_PRICE_INPUT",
                            index,
                            name: "cancel_charge_profit",
                            payload: e.target.value
                        })
                    }
                />
            </td>
        </tr>
    );
};

export default CancelRow1;
