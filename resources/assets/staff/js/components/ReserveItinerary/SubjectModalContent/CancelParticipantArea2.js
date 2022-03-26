import React from "react";
import OnlyNumberInput from "../../OnlyNumberInput";

/**
 * ホテル科目用
 * キャンセル用
 *
 * @param {*} param0
 * @returns
 */
const CancelParticipantArea2 = ({ input, participants, handleChange }) => {
    return (
        <>
            <h3 className="subjectTit cancel">キャンセルした参加者</h3>
            <div className="modalPriceList mt20">
                <table className="baseTable cancelTable">
                    <thead>
                        <tr>
                            <th>有効</th>
                            <th>部屋番号</th>
                            <th>氏名</th>
                            <th className="txtalc">性別</th>
                            <th className="txtalc">年齢</th>
                            <th className="txtalc">年齢区分</th>
                            <th>キャンセル料金</th>
                            <th>仕入先支払料金</th>
                            <th>税抜単価</th>
                            <th className="txtalc">税区分</th>
                            <th>仕入れ額</th>
                            <th>手数料率</th>
                            <th>粗利</th>
                        </tr>
                    </thead>
                    <tbody>
                        {participants &&
                            participants.map((participant, index) => {
                                {
                                    /**通常仕入行のみ抽出 */
                                }
                                if (
                                    input?.participants?.[index]
                                        ?.is_alive_cancel == 1
                                ) {
                                    return (
                                        <tr key={index}>
                                            <td>
                                                <div className="checkBox">
                                                    <input
                                                        type="checkbox"
                                                        value={
                                                            input
                                                                ?.participants?.[
                                                                index
                                                            ]?.is_cancel ?? "0"
                                                        }
                                                        id={`cancel_participant${index}`}
                                                        onChange={e =>
                                                            handleChange({
                                                                type:
                                                                    "CHANGE_PARTICIPANT_CHECKBOX",
                                                                index,
                                                                name:
                                                                    "is_cancel",
                                                                payload:
                                                                    e.target
                                                                        .value
                                                            })
                                                        }
                                                        checked={
                                                            input
                                                                ?.participants?.[
                                                                index
                                                            ]?.is_cancel
                                                        }
                                                    />
                                                    <label
                                                        htmlFor={`cancel_participant${index}`}
                                                    ></label>
                                                </div>
                                            </td>
                                            <td>
                                                {input?.participants?.[index]
                                                    ?.room_number ?? "-"}
                                            </td>
                                            <td>
                                                {participant?.name ?? "-"}
                                                {participant?.name_kana && (
                                                    <>
                                                        ({participant.name_kana}
                                                        )
                                                    </>
                                                )}
                                            </td>
                                            <td className="txtalc">
                                                {participant?.sex_label ?? "-"}
                                            </td>
                                            <td className="txtalc">
                                                {participant?.age ?? "-"}
                                            </td>
                                            <td className="txtalc">
                                                {participant?.age_kbn_label ??
                                                    "-"}
                                            </td>
                                            <td>
                                                <OnlyNumberInput
                                                    value={
                                                        input?.participants?.[
                                                            index
                                                        ]?.cancel_charge ?? 0
                                                    }
                                                    handleChange={e =>
                                                        handleChange({
                                                            type:
                                                                "CHANGE_PARTICIPANT_PRICE_INPUT",
                                                            index,
                                                            name:
                                                                "cancel_charge",
                                                            payload:
                                                                e.target.value
                                                        })
                                                    }
                                                />
                                            </td>
                                            <td>
                                                <OnlyNumberInput
                                                    value={
                                                        input?.participants?.[
                                                            index
                                                        ]?.cancel_charge_net ??
                                                        0
                                                    }
                                                    handleChange={e =>
                                                        handleChange({
                                                            type:
                                                                "CHANGE_PARTICIPANT_PRICE_INPUT",
                                                            index,
                                                            name:
                                                                "cancel_charge_net",
                                                            payload:
                                                                e.target.value
                                                        })
                                                    }
                                                />
                                            </td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>
                                                {input?.participants?.[index]
                                                    ?.cost ?? 0}
                                            </td>
                                            <td className="txtalc">
                                                {input?.participants?.[index]
                                                    ?.commission_rate ?? 0}
                                                %
                                            </td>
                                            <td>
                                                <OnlyNumberInput
                                                    value={
                                                        input?.participants?.[
                                                            index
                                                        ]
                                                            ?.cancel_charge_profit ??
                                                        0
                                                    }
                                                    handleChange={e =>
                                                        handleChange({
                                                            type:
                                                                "CHANGE_PARTICIPANT_PRICE_INPUT",
                                                            index,
                                                            name:
                                                                "cancel_charge_profit",
                                                            payload:
                                                                e.target.value
                                                        })
                                                    }
                                                />
                                            </td>
                                        </tr>
                                    );
                                }
                            })}
                    </tbody>
                </table>
            </div>
        </>
    );
};

export default CancelParticipantArea2;
