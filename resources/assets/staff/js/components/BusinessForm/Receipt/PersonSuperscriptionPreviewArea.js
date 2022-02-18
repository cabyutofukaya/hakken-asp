import React from "react";

const PersonSuperscriptionPreviewArea = ({ documentAddress, honorifics }) => {
    return (
        <span>
            {documentAddress.name ?? ""}
            {honorifics?.[documentAddress?.honorific] ?? ""}
        </span>
    );
};

export default PersonSuperscriptionPreviewArea;
