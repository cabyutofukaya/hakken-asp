import React from "react";

const BusinessSuperscriptionPreviewArea = ({ documentAddress, honorifics }) => {
    return (
        <span>
            {documentAddress.company_name ?? ""}{" "}
            {documentAddress.department_name ?? ""} {documentAddress.name ?? ""}
            {honorifics?.[documentAddress?.honorific] ?? ""}
        </span>
    );
};

export default BusinessSuperscriptionPreviewArea;
