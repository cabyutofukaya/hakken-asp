import React from "react";

const WebReserveBasicInfoLeft = ({
    data,
    customFields,
    customFieldPositions
}) => {
    return (
        <table className="baseTable">
            <tbody>
                {data?.control_number && (
                    <tr>
                        <th>予約番号</th>
                        <td>{data.control_number}</td>
                    </tr>
                )}
                <tr>
                    <th>見積番号</th>
                    <td>{data?.estimate_number ?? "-"}</td>
                </tr>
                <tr>
                    <th>依頼番号</th>
                    <td>{data?.request_number ?? "-"}</td>
                </tr>
                <tr>
                    <th>受付番号</th>
                    <td>
                        {data?.web_reserve_ext?.web_consult?.receipt_number ??
                            "-"}
                    </td>
                </tr>
                <tr>
                    <th>顧客種別</th>
                    <td>{data?.applicant?.applicant_type_label ?? "-"}</td>
                </tr>
                <tr>
                    <th>申込者</th>
                    <td>
                        <>
                            {!data?.applicant?.is_deleted && (
                                <a href={data?.applicant?.detail_url}>
                                    {data?.applicant?.user_number ?? "-"}
                                </a>
                            )}
                            {data?.applicant?.is_deleted &&
                                (data?.applicant?.user_number ?? "-")}
                            <br />
                            {data?.applicant?.name ?? "-"}(
                            {data?.applicant?.name_kana ?? "-"})
                        </>
                    </td>
                </tr>
                <tr>
                    <th>旅行名</th>
                    <td>{data?.name ?? "-"}</td>
                </tr>
                <tr>
                    <th>旅行種別</th>
                    <td>{data?.travel_type?.val ?? "-"}</td>
                </tr>
                <tr>
                    <th>旅行目的</th>
                    <td>
                        {data?.web_reserve_ext?.web_consult?.purpose ?? "-"}
                    </td>
                </tr>
                <tr>
                    <th>出発日</th>
                    <td>{data?.departure_date ?? "-"}</td>
                </tr>
                <tr>
                    <th>帰着日</th>
                    <td>{data?.return_date ?? "-"}</td>
                </tr>
                <tr>
                    <th>出発地</th>
                    <td>{data?.departure ?? "-"}</td>
                </tr>
                <tr>
                    <th>目的地</th>
                    <td>{data?.destination ?? "-"}</td>
                </tr>
                <tr>
                    <th>人数</th>
                    <td>
                        <ul className="person">
                            <li>
                                大人{" "}
                                {(
                                    data?.web_reserve_ext?.web_consult?.adult ??
                                    0
                                ).toLocaleString()}
                                名
                            </li>
                            <li>
                                子供{" "}
                                {(
                                    data?.web_reserve_ext?.web_consult?.child ??
                                    0
                                ).toLocaleString()}
                                名
                            </li>
                            <li>
                                幼児{" "}
                                {(
                                    data?.web_reserve_ext?.web_consult
                                        ?.infant ?? 0
                                ).toLocaleString()}
                                名
                            </li>
                        </ul>
                    </td>
                </tr>
                <tr>
                    <th>予算の目安</th>
                    <td>
                        {data?.web_reserve_ext?.web_consult?.budget_label ??
                            "-"}
                    </td>
                </tr>
                <tr>
                    <th>興味があること</th>
                    <td>
                        <ul className="tagList">
                            {data?.web_reserve_ext?.web_consult?.web_consult &&
                                data.web_reserve_ext.web_consult.web_consult.map(
                                    (v, i) => <li key={i}>{v}</li>
                                )}
                        </ul>
                        {!data?.web_reserve_ext?.web_consult?.web_consult &&
                            "-"}
                    </td>
                </tr>
                {/**(システム管理コード無しの)基本情報のカスタム項目*/}
                {_.filter(customFields?.[customFieldPositions.estimates_base], {
                    code: null
                }) &&
                    _.filter(
                        customFields[customFieldPositions.estimates_base],
                        {
                            code: null
                        }
                    ).map((row, index) => (
                        <tr key={index}>
                            <th>{row.name ?? "-"}</th>
                            <td>{row.val ?? "-"}</td>
                        </tr>
                    ))}
                <tr>
                    <th>備考</th>
                    <td>{data?.note ?? "-"}</td>
                </tr>
            </tbody>
        </table>
    );
};

export default WebReserveBasicInfoLeft;
