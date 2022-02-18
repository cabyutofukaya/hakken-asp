import React from "react";
import { DOCUMENT_COMMON } from "../../constants";

const CompanySuperscriptionPreviewArea = ({
    commonSetting,
    documentAddress,
    honorifics
}) => {
    if (!commonSetting?.[DOCUMENT_COMMON.ADDRESS_BUSINESS]) return null;

    return (
        <>
            <div className="dispSign">
                <p className="dispPostal">
                    {commonSetting?.[DOCUMENT_COMMON.ADDRESS_BUSINESS].includes(
                        "郵便番号"
                    ) &&
                        documentAddress.zip_code && (
                            <>
                                〒{documentAddress.zip_code.substr(0, 3)}-
                                {documentAddress.zip_code.substr(3)}
                            </>
                        )}
                </p>
                <p className="dispAddress">
                    {commonSetting?.[DOCUMENT_COMMON.ADDRESS_BUSINESS].includes(
                        "都道府県"
                    ) && <>{documentAddress.prefecture}</>}
                    {commonSetting?.[DOCUMENT_COMMON.ADDRESS_BUSINESS].includes(
                        "住所1"
                    ) && <>{documentAddress.address1}</>}
                    {commonSetting?.[DOCUMENT_COMMON.ADDRESS_BUSINESS].includes(
                        "住所2"
                    ) && <>{documentAddress.address2}</>}
                </p>
            </div>
            <p className="dispName">
                {commonSetting?.[DOCUMENT_COMMON.ADDRESS_BUSINESS].includes(
                    "法人名"
                ) &&
                    documentAddress?.company_name && (
                        <>
                            {documentAddress.company_name}
                            {honorifics?.[documentAddress?.honorific] ?? ""}
                        </>
                    )}
            </p>
        </>
    );
};

export default CompanySuperscriptionPreviewArea;
