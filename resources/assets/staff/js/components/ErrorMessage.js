import React from "react";
import BrText from "../BrText";
import { isEmptyObject } from "../libs";

const ErrorMessage = ({ errorObj }) => {
    let msgs = [];
    if (!isEmptyObject(errorObj)) {
        Object.keys(errorObj).map(k => {
            msgs = [...msgs, errorObj[k]];
        });
    }

    return (
        <>
            {msgs.length > 0 ? (
                <div id="errorMessage">
                    <p>
                        <BrText text={msgs.join("\n")} />
                    </p>
                </div>
            ) : null}
        </>
    );
};

export default ErrorMessage;
