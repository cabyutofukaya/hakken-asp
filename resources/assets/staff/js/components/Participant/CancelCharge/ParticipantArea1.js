import React, { useContext } from "react";
import OnlyNumberInput from "../../OnlyNumberInput";
import { ConstContext } from "../../ConstApp";

const ParticipantArea1 = ({ participants, handleChange }) => {
    const { documentZeiKbns } = useContext(ConstContext);

    return (
        <div className="modalPriceList mt20">
            <table className="baseTable cancelTable">
                <thead>
                    <tr>
                        <th>座席</th>
                        <th>REF番号</th>
                        {/* <th>氏名</th>
                        <th className="txtalc">性別</th>
                        <th className="txtalc">年齢</th>
                        <th className="txtalc">年齢区分</th> */}
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
                        participants.map((row, index) => (
                            <tr key={index}>
                                <td>{row.seat ?? "-"}</td>
                                <td>{row.reference_number ?? "-"}</td>
                                {/* <td>
                                    {row.name ?? "-"}
                                    {row.name_kana && <>({row.name_kana})</>}
                                </td>
                                <td className="txtalc">
                                    {row.sex_label ?? "-"}
                                </td>
                                <td className="txtalc">{row.age ?? "-"}</td>
                                <td className="txtalc">
                                    {row.age_kbn_label ?? "-"}
                                </td> */}
                                <td>
                                    <OnlyNumberInput
                                        value={row?.cancel_charge ?? 0}
                                        handleChange={e =>
                                            handleChange(
                                                index,
                                                "cancel_charge",
                                                e.target.value
                                            )
                                        }
                                    />
                                </td>
                                <td>
                                    <OnlyNumberInput
                                        value={row?.cancel_charge_net ?? 0}
                                        handleChange={e =>
                                            handleChange(
                                                index,
                                                "cancel_charge_net",
                                                e.target.value
                                            )
                                        }
                                    />
                                </td>
                                <td>
                                    ￥{(row.gross_ex ?? 0).toLocaleString()}
                                </td>
                                <td>{documentZeiKbns[row.zei_kbn] ?? "-"}</td>
                                <td>￥{(row.cost ?? 0).toLocaleString()}</td>
                                <td className="txtalc">
                                    {row.commission_rate ?? 0}%
                                </td>
                                <td>
                                    <OnlyNumberInput
                                        value={row?.cancel_charge_profit ?? 0}
                                        handleChange={e =>
                                            handleChange(
                                                index,
                                                "cancel_charge_profit",
                                                e.target.value
                                            )
                                        }
                                    />
                                </td>
                            </tr>
                        ))}
                </tbody>
            </table>
        </div>
    );
};

export default ParticipantArea1;
