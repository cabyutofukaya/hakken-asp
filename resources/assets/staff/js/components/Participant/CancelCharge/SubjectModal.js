import React, { useContext } from "react";
import { ConstContext } from "../../ConstApp";
import Option from "./Option";
import Airplane from "./Airplane";
import Hotel from "./Hotel";

/**
 *
 * @param {*} subjectInfo 最新の仕入金額情報
 * @returns
 */
const SubjectModal = ({
    id,
    data,
    setData,
    priceSetting,
    setPriceSetting,
    subjectInfo,
    handleRegist
}) => {
    const { subjectCategories } = useContext(ConstContext);

    return (
        <>
            <div
                id={id}
                className="wideModal modal js-modal"
                style={{ position: "fixed", left: 0, top: 0 }}
            >
                <div className="modal__bg js-modal-close"></div>
                {/** オプション科目*/}
                {data?.subject ===
                    subjectCategories?.subject_category_option && (
                    <Option
                        data={data}
                        setData={setData}
                        priceSetting={priceSetting}
                        setPriceSetting={setPriceSetting}
                        subjectInfo={subjectInfo}
                        handleRegist={handleRegist}
                    />
                )}
                {/** 航空券科目*/}
                {data?.subject ===
                    subjectCategories?.subject_category_airplane && (
                    <Airplane
                        data={data}
                        setData={setData}
                        priceSetting={priceSetting}
                        setPriceSetting={setPriceSetting}
                        subjectInfo={subjectInfo}
                        handleRegist={handleRegist}
                    />
                )}
                {/** ホテル科目*/}
                {data?.subject ===
                    subjectCategories?.subject_category_hotel && (
                    <Hotel
                        data={data}
                        setData={setData}
                        priceSetting={priceSetting}
                        setPriceSetting={setPriceSetting}
                        subjectInfo={subjectInfo}
                        handleRegist={handleRegist}
                    />
                )}
            </div>
        </>
    );
};

export default SubjectModal;
