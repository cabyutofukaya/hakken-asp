import React from "react";

const InvoiceInfoPreviewArea = ({
    reserveSetting,
    name,
    periodFrom,
    periodTo,
    partnerManagers
}) => {
    return (
        <>
            {reserveSetting.includes("件名") && name && (
                <p className="dispTitle">件名 {name}</p>
            )}
            {reserveSetting.includes("期間") && (
                <p className="dispPeriod">
                    {(periodFrom || periodTo) && (
                        <>
                            期間 {periodFrom}
                            {periodFrom && periodTo && "〜"}
                            {periodTo}分
                        </>
                    )}
                </p>
            )}
            <p className="dispParticipant">
                {reserveSetting.includes("御社担当") &&
                    partnerManagers.length > 0 && (
                        <>
                            御社担当{" "}
                            {Object.keys(partnerManagers).map((k, i) => {
                                return (
                                    <React.Fragment key={i}>
                                        {partnerManagers[k]["org_name"]}
                                        {reserveSetting.includes(
                                            "御社担当_御社担当(敬称)"
                                        ) && " 様"}
                                        {i !==
                                            Object.keys(partnerManagers)
                                                .length -
                                                1 && " / "}
                                    </React.Fragment>
                                );
                            })}
                        </>
                    )}
            </p>
        </>
    );
};

export default InvoiceInfoPreviewArea;
