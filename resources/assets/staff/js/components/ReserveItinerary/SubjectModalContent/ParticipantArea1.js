import React, { useContext } from "react";
import { ConstContext } from "../../ConstApp";
import OnlyNumberInput from "../../OnlyNumberInput";

/**
 * オプション科目、航空券科目用
 * 通常仕入用
 *
 * @param {*} param0
 * @returns
 */
const ParticipantArea1 = ({ input, participants, zeiKbns, handleChange }) => {
    const { purchaseNormal } = useContext(ConstContext);

    return (
        <>
            <div className="modalPriceList mt20">
                <table className="baseTable">
                    <thead>
                        <tr>
                            <th className="wd10">有効</th>
                            <th>座席</th>
                            <th>REF番号</th>
                            <th>氏名</th>
                            <th className="txtalc">性別</th>
                            <th className="txtalc">年齢</th>
                            <th className="txtalc">年齢区分</th>
                            <th>税抜単価</th>
                            <th className="txtalc">税区分</th>
                            <th>税込GROSS単価</th>
                            <th>仕入れ額</th>
                            <th>手数料率</th>
                            <th>NET単価</th>
                            <th>粗利</th>
                        </tr>
                    </thead>
                    <tbody>
                        {/**未キャンセル参加者が存在する場合の出力 */}
                        {participants &&
                            _.findIndex(participants, { cancel: false }) !=
                                -1 &&
                            participants.map((participant, index) => {
                                {
                                    /**通常仕入行のみ抽出 */
                                }
                                if (
                                    input?.participants?.[index]
                                        ?.purchase_type == purchaseNormal
                                ) {
                                    return (
                                        <tr key={index}>
                                            <td className="wd10">
                                                {participant.cancel == 0 && (
                                                    <div className="checkBox">
                                                        <input
                                                            type="checkbox"
                                                            value={
                                                                input
                                                                    ?.participants?.[
                                                                    index
                                                                ]?.valid ?? "0"
                                                            }
                                                            id={`participant${index}`}
                                                            onChange={e =>
                                                                handleChange({
                                                                    type:
                                                                        "CHANGE_PARTICIPANT_CHECKBOX",
                                                                    index,
                                                                    name:
                                                                        "valid",
                                                                    payload:
                                                                        e.target
                                                                            .value
                                                                })
                                                            }
                                                            checked={
                                                                input
                                                                    .participants?.[
                                                                    index
                                                                ]?.valid
                                                            }
                                                        />
                                                        <label
                                                            htmlFor={`participant${index}`}
                                                        ></label>
                                                    </div>
                                                )}
                                            </td>
                                            <td>
                                                <input
                                                    type="text"
                                                    value={
                                                        input?.participants?.[
                                                            index
                                                        ]?.seat ?? ""
                                                    }
                                                    onChange={e =>
                                                        handleChange({
                                                            type:
                                                                "CHANGE_PARTICIPANT_INPUT",
                                                            index,
                                                            name: "seat",
                                                            payload:
                                                                e.target.value
                                                        })
                                                    }
                                                />
                                            </td>
                                            <td>
                                                <input
                                                    type="text"
                                                    value={
                                                        input?.participants?.[
                                                            index
                                                        ]?.reference_number ??
                                                        ""
                                                    }
                                                    onChange={e =>
                                                        handleChange({
                                                            type:
                                                                "CHANGE_PARTICIPANT_INPUT",
                                                            index,
                                                            name:
                                                                "reference_number",
                                                            payload:
                                                                e.target.value
                                                        })
                                                    }
                                                />
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
                                                        ]?.gross_ex ?? 0
                                                    }
                                                    handleChange={e =>
                                                        handleChange({
                                                            type:
                                                                "CHANGE_PARTICIPANT_PRICE_INPUT",
                                                            index,
                                                            name: "gross_ex",
                                                            payload:
                                                                e.target.value
                                                        })
                                                    }
                                                />
                                            </td>
                                            <td className="txtalc taxTd">
                                                <div className="selectBox">
                                                    <select
                                                        value={
                                                            input
                                                                ?.participants?.[
                                                                index
                                                            ]?.zei_kbn ?? 0
                                                        }
                                                        onChange={e =>
                                                            handleChange({
                                                                type:
                                                                    "CHANGE_PARTICIPANT_PRICE_INPUT",
                                                                index,
                                                                name: "zei_kbn",
                                                                payload:
                                                                    e.target
                                                                        .value
                                                            })
                                                        }
                                                    >
                                                        {zeiKbns &&
                                                            Object.keys(
                                                                zeiKbns
                                                            ).map(
                                                                (
                                                                    val,
                                                                    index
                                                                ) => (
                                                                    <option
                                                                        key={
                                                                            index
                                                                        }
                                                                        value={
                                                                            val
                                                                        }
                                                                    >
                                                                        {
                                                                            zeiKbns[
                                                                                val
                                                                            ]
                                                                        }
                                                                    </option>
                                                                )
                                                            )}
                                                    </select>
                                                </div>
                                            </td>
                                            <td>
                                                <OnlyNumberInput
                                                    value={
                                                        input?.participants?.[
                                                            index
                                                        ]?.gross ?? 0
                                                    }
                                                    handleChange={e =>
                                                        handleChange({
                                                            type:
                                                                "CHANGE_PARTICIPANT_PRICE_INPUT",
                                                            index,
                                                            name: "gross",
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
                                                        ]?.cost ?? 0
                                                    }
                                                    handleChange={e =>
                                                        handleChange({
                                                            type:
                                                                "CHANGE_PARTICIPANT_PRICE_INPUT",
                                                            index,
                                                            name: "cost",
                                                            payload:
                                                                e.target.value
                                                        })
                                                    }
                                                />
                                            </td>
                                            <td className="txtalc">
                                                <div className="priceInput per">
                                                    <OnlyNumberInput
                                                        value={
                                                            input
                                                                ?.participants?.[
                                                                index
                                                            ]
                                                                ?.commission_rate ??
                                                            0
                                                        }
                                                        handleChange={e =>
                                                            handleChange({
                                                                type:
                                                                    "CHANGE_PARTICIPANT_PRICE_INPUT",
                                                                index,
                                                                name:
                                                                    "commission_rate",
                                                                payload:
                                                                    e.target
                                                                        .value
                                                            })
                                                        }
                                                    />
                                                </div>
                                            </td>
                                            <td>
                                                <OnlyNumberInput
                                                    value={
                                                        input?.participants?.[
                                                            index
                                                        ]?.net ?? 0
                                                    }
                                                    handleChange={e =>
                                                        handleChange({
                                                            type:
                                                                "CHANGE_PARTICIPANT_PRICE_INPUT",
                                                            index,
                                                            name: "net",
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
                                                        ]?.gross_profit ?? 0
                                                    }
                                                    handleChange={e =>
                                                        handleChange({
                                                            type:
                                                                "CHANGE_PARTICIPANT_PRICE_INPUT",
                                                            index,
                                                            name:
                                                                "gross_profit",
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

export default ParticipantArea1;
