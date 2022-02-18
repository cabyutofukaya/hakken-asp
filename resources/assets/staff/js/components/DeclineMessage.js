import React from "react";

const DeclineMessage = ({ message }) => {
    return (
        <>
            {message ? (
                <div id="declineMessage">
                    <p>
                        <span className="material-icons">
                            do_not_disturb_on
                        </span>
                        &nbsp;
                        {message}
                    </p>
                    <span className="material-icons closeIcon">cancel</span>
                </div>
            ) : null}
        </>
    );
};

export default DeclineMessage;
