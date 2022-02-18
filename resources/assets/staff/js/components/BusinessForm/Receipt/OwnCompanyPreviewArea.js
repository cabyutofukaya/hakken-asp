import React from "react";

const OwnCompanyPreviewArea = ({ company, manager }) => {
    return (
        <div className="dispCorp">
            <p className="dispCompany">{company.company_name ?? ""}</p>
            <p className="dispEtc01">{company.supplement1 ?? ""}</p>
            <p className="dispEtc02">{company.supplement2 ?? ""}</p>
            <p className="dispPostal">
                {company?.zip_code && (
                    <>
                        〒{company.zip_code.substr(0, 3)}-
                        {company.zip_code.substr(3)}
                    </>
                )}
            </p>
            <p className="dispCorpAddress">
                {company.address1 ?? ""}
                {company?.address2 && (
                    <>
                        <br />
                        {company.address2}
                    </>
                )}
            </p>
            <p className="dispCorpContact">
                {company?.tel && <>TEL:{company.tel}</>}{" "}
                {company?.fax && <>/ FAX:{company.fax}</>}
            </p>
            <p className="dispManager">{manager && <>担当 {manager}</>}</p>
        </div>
    );
};

export default OwnCompanyPreviewArea;
