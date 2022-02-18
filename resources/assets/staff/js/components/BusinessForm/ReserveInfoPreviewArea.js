import React from "react";
import { SEX } from "../../constants";

const ReserveInfoPreviewArea = ({
    reserveSetting,
    name,
    departureDate,
    returnDate,
    representative,
    participants
}) => {
    return (
        <>
            {reserveSetting.includes("件名") && name && (
                <p className="dispTitle">件名 {name}</p>
            )}
            {reserveSetting.includes("期間") && (
                <p className="dispPeriod">
                    {(departureDate || returnDate) && (
                        <>
                            期間 {departureDate}
                            {departureDate && returnDate && "〜"}
                            {returnDate}
                        </>
                    )}
                </p>
            )}
            <p className="dispParticipant">
                {reserveSetting.includes("代表者") && representative?.name && (
                    <>
                        代表者 {representative?.name}{" "}
                        {reserveSetting.includes("代表者(ローマ字)") &&
                            representative?.name_roman && (
                                <>
                                    (
                                    {reserveSetting.includes(
                                        "代表者(ローマ字)_Mr/Ms"
                                    ) && (
                                        <>
                                            {representative?.sex === SEX.MALE &&
                                                "Mr."}
                                            {representative?.sex ===
                                                SEX.FEMALE && "Ms."}
                                        </>
                                    )}
                                    {representative?.name_roman})
                                </>
                            )}
                        {reserveSetting.includes("代表者_代表者(敬称)") && (
                            <>様</>
                        )}
                        <br />
                    </>
                )}
                {reserveSetting.includes("参加者") && participants.length > 0 && (
                    <>
                        参加者{" "}
                        {Object.keys(participants).map((k, i) => {
                            return (
                                <React.Fragment key={i}>
                                    {participants[k]["name"]}
                                    {reserveSetting.includes(
                                        "参加者(ローマ字)"
                                    ) &&
                                        participants[k]?.["name_roman"] && (
                                            <>
                                                (
                                                {reserveSetting.includes(
                                                    "参加者(ローマ字)_Mr/Ms"
                                                ) && (
                                                    <>
                                                        {participants[k][
                                                            "sex"
                                                        ] === SEX.MALE && "Mr."}
                                                        {participants[k][
                                                            "sex"
                                                        ] === SEX.FEMALE &&
                                                            "Ms."}
                                                    </>
                                                )}
                                                {participants[k]["name_roman"]})
                                            </>
                                        )}
                                    {reserveSetting.includes(
                                        "参加者_参加者(敬称)"
                                    ) && " 様"}
                                    {i !==
                                        Object.keys(participants).length - 1 &&
                                        " / "}
                                </React.Fragment>
                            );
                        })}
                    </>
                )}
            </p>
        </>
    );
};

export default ReserveInfoPreviewArea;
