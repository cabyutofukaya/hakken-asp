import React, { useContext } from "react";
import { ReserveItineraryConstContext } from "../ReserveItineraryConstApp";
import CancelAirplane from "./SubjectModalContent/CancelAirplane";
import CancelHotel from "./SubjectModalContent/CancelHotel";
import CancelOption from "./SubjectModalContent/CancelOption";

/**
 * キャンセル仕入用
 *
 * @param {*} targetAddRow 追加対象行情報。日付、行番号
 * @param {Object} customFields カスタム項目情報
 * @param {*} customFieldCodes カスタム項目管理コード情報
 * @returns
 */
const CancelSubjectModal = ({
    input,
    participants,
    zeiKbns,
    suppliers,
    cities,
    targetAddRow,
    editPurchasingRowInfo,
    handleChange,
    rowDispatch,
    subjectCategories,
    customFields,
    subjectCustomCategoryCode,
    customFieldCodes,
    defaultSubjectHotels,
    defaultSubjectOptions,
    defaultSubjectAirplanes
}) => {
    const { subjectCategoryTypes } = useContext(ReserveItineraryConstContext);

    return (
        <>
            <div
                id="mdSubjectCancel"
                className="wideModal modal js-modal"
                style={{ position: "fixed", left: 0, top: 0 }}
            >
                <div className="modal__bg js-modal-close"></div>
                {/** オプション科目*/}
                {input.subject === subjectCategoryTypes?.option && (
                    <CancelOption
                        input={input}
                        participants={participants}
                        zeiKbns={zeiKbns}
                        suppliers={suppliers}
                        targetAddRow={targetAddRow}
                        editPurchasingRowInfo={editPurchasingRowInfo}
                        handleChange={handleChange}
                        rowDispatch={rowDispatch}
                        subjectCategories={subjectCategories}
                        subjectCustomCategoryCode={subjectCustomCategoryCode}
                        customFields={
                            customFields?.[subjectCategoryTypes?.option]
                        }
                        customFieldCodes={customFieldCodes}
                        defaultSubjectOptions={defaultSubjectOptions}
                    />
                )}
                {/** 航空券科目*/}
                {input.subject === subjectCategoryTypes?.airplane && (
                    <CancelAirplane
                        input={input}
                        participants={participants}
                        zeiKbns={zeiKbns}
                        suppliers={suppliers}
                        cities={cities}
                        targetAddRow={targetAddRow}
                        editPurchasingRowInfo={editPurchasingRowInfo}
                        handleChange={handleChange}
                        rowDispatch={rowDispatch}
                        subjectCategories={subjectCategories}
                        subjectCustomCategoryCode={subjectCustomCategoryCode}
                        customFields={
                            customFields?.[subjectCategoryTypes?.airplane]
                        }
                        customFieldCodes={customFieldCodes}
                        defaultSubjectAirplanes={defaultSubjectAirplanes}
                    />
                )}
                {/** ホテル科目*/}
                {input.subject === subjectCategoryTypes?.hotel && (
                    <CancelHotel
                        input={input}
                        participants={participants}
                        zeiKbns={zeiKbns}
                        suppliers={suppliers}
                        targetAddRow={targetAddRow}
                        editPurchasingRowInfo={editPurchasingRowInfo}
                        handleChange={handleChange}
                        rowDispatch={rowDispatch}
                        subjectCategories={subjectCategories}
                        subjectCustomCategoryCode={subjectCustomCategoryCode}
                        customFields={
                            customFields?.[subjectCategoryTypes?.hotel]
                        }
                        customFieldCodes={customFieldCodes}
                        defaultSubjectHotels={defaultSubjectHotels}
                    />
                )}
            </div>
        </>
    );
};

export default CancelSubjectModal;
