import React from "react";

// 予約情報基本情報タブ用のエラー
const BaseErrorMessage = ({ message }) => {
    return (
        <>
            {message ? (
                <div id="errorMessage">
                    <p>{message}</p>
                </div>
            ) : null}
        </>
    );
};

export default BaseErrorMessage;
