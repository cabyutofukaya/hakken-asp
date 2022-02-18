import React from "react";

const SuccessMessage = ({ message }) => {
    return (
        <>
            {message ? (
                <div id="successMessage">
                    <p>
                        <span className="material-icons">check_circle</span>
                        {message}
                    </p>
                    <span className="material-icons closeIcon">cancel</span>
                </div>
            ) : null}
        </>
    );
};

export default SuccessMessage;
