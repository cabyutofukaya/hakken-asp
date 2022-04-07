import React, { useState } from "react";
import classNames from "classnames";
import ParticipantArea from "./ParticipantArea";
import ItineraryArea from "./ItineraryArea";
import DocumentArea from "./DocumentArea";
import AccountPayableArea from "./AccountPayableArea";

const WebEstimateDetail = ({
    isShow,
    applicationStep,
    applicationStepList,
    estimateNumber,
    reserveNumber,
    defaultValue,
    formSelects,
    consts,
    constsCommon,
    permission,
    setSuccessMessage,
    itineraryErrorMessage,
    setItineraryErrorMessage,
    documentErrorMessage,
    setDocumentErrorMessage,
    updatedAt,
    setUpdatedAt
}) => {
    // 現在、有効化中の行程番号
    const [currentItineraryNumber, setCurrentItineraryNumber] = useState(null);

    // 参加者削除リクエストID(リスト更新のトリガーに使用)
    const [
        participantDeleteRequestId,
        setParticipantDeleteRequestId
    ] = useState(0);

    // 参加者取り消しリクエストID(リスト更新のトリガーに使用)
    const [
        participantCancelRequestId,
        setParticipantCancelRequestId
    ] = useState(0);

    return (
        <div
            className={classNames("userList", {
                show: isShow
            })}
        >
            <ParticipantArea
                isShow={isShow}
                reception="web"
                applicationStep={applicationStep}
                applicationStepList={applicationStepList}
                estimateNumber={estimateNumber}
                reserveNumber={reserveNumber}
                defaultValue={defaultValue}
                sexList={consts?.sexList}
                sexes={formSelects?.sexes}
                ageKbns={formSelects?.ageKbns}
                birthdayYears={formSelects.birthdayYears}
                birthdayMonths={formSelects.birthdayMonths}
                birthdayDays={formSelects.birthdayDays}
                countries={formSelects?.countries}
                setDeleteRequestId={setParticipantDeleteRequestId}
                setCancelRequestId={setParticipantCancelRequestId}
                permission={permission}
                constsCommon={constsCommon}
                setSuccessMessage={setSuccessMessage}
                updatedAt={updatedAt}
                setUpdatedAt={setUpdatedAt}
            />
            <ItineraryArea
                isShow={isShow}
                reception="web"
                applicationStep={applicationStep}
                applicationStepList={applicationStepList}
                estimateNumber={estimateNumber}
                reserveNumber={reserveNumber}
                currentItineraryNumber={currentItineraryNumber}
                setCurrentItineraryNumber={setCurrentItineraryNumber}
                participantDeleteRequestId={participantDeleteRequestId}
                participantCancelRequestId={participantCancelRequestId}
                permission={permission}
                errorMessage={itineraryErrorMessage}
                setErrorMessage={setItineraryErrorMessage}
            />
            <DocumentArea
                isShow={isShow}
                reception="web"
                applicationStep={applicationStep}
                applicationStepList={applicationStepList}
                estimateNumber={estimateNumber}
                reserveNumber={reserveNumber}
                currentItineraryNumber={currentItineraryNumber}
                participantDeleteRequestId={participantDeleteRequestId}
                participantCancelRequestId={participantCancelRequestId}
                hasOriginalDocumentQuoteTemplate={
                    consts?.hasOriginalDocumentQuoteTemplate
                }
                constsCommon={constsCommon}
                permission={permission}
                errorMessage={documentErrorMessage}
                setErrorMessage={setDocumentErrorMessage}
            />
            {permission.management_read && (
                <AccountPayableArea
                    isShow={isShow}
                    reception="web"
                    applicationStep={applicationStep}
                    applicationStepList={applicationStepList}
                    estimateNumber={estimateNumber}
                    reserveNumber={reserveNumber}
                    currentItineraryNumber={currentItineraryNumber}
                    participantDeleteRequestId={participantDeleteRequestId}
                    participantCancelRequestId={participantCancelRequestId}
                />
            )}
        </div>
    );
};

export default WebEstimateDetail;
