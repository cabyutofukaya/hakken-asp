import React from "react";

/**
 * 自社情報表示エリア
 *
 * @param {*} param0
 * @returns
 */
const OwnCompanyPreviewArea = ({ showSetting, company, manager }) => {
    if (!showSetting) return null;

    return (
        <>
            <p className="dispCompany">
                {showSetting.includes("自社名") &&
                    company?.company_name &&
                    (company.company_name ?? "")}
            </p>
            <p className="dispEtc01">
                {showSetting.includes("補足情報1") &&
                    company?.supplement1 &&
                    (company.supplement1 ?? "")}
            </p>
            <p className="dispEtc02">
                {showSetting.includes("補足情報2") &&
                    company?.supplement2 &&
                    (company.supplement2 ?? "")}
            </p>
            <p className="dispPostal">
                {showSetting.includes("郵便番号") && company?.zip_code && (
                    <>
                        〒{company.zip_code.substr(0, 3)}-
                        {company.zip_code.substr(3)}
                    </>
                )}
            </p>
            <p className="dispCorpAddress">
                {showSetting.includes("住所1") &&
                    company?.address1 &&
                    (company.address1 ?? "")}
                <br />
                {showSetting.includes("住所2") && company?.address2 && (
                    <>
                        <br />
                        {company.address2}
                    </>
                )}
            </p>
            <p className="dispCorpContact">
                {showSetting.includes("TEL") &&
                    company?.tel &&
                    `TEL:${company.tel ?? ""}`}
                {showSetting.includes("FAX") && company?.fax && (
                    <> / FAX:${company.fax ?? ""}</>
                )}
            </p>
            <p className="dispManager">
                {showSetting.includes("担当者") && manager && `担当 ${manager}`}
            </p>
        </>
    );
};

export default OwnCompanyPreviewArea;
