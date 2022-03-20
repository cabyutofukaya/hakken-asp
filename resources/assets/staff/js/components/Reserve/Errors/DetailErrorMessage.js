import React from "react";

//行程、帳票エラー
const DetailErrorMessage = ({
    itineraryErrorMessage,
    documentErrorMessage
}) => {
    return (
        <>
            {itineraryErrorMessage || documentErrorMessage ? (
                <div id="errorMessage">
                    {itineraryErrorMessage && <p>{itineraryErrorMessage}</p>}
                    {documentErrorMessage && <p>{documentErrorMessage}</p>}
                </div>
            ) : null}
        </>
    );
};

export default DetailErrorMessage;
