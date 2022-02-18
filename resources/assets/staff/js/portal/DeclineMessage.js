import React from "react";
import ReactDOM from "react-dom";

const DeclineMessage = ({ show, message }) => {
    return ReactDOM.createPortal(
        <div id="declineMessage" style={{ display: show ? "" : "none" }}>
            <p>
                <span className="material-icons">do_not_disturb_on</span>
                &nbsp;
                {message}
            </p>
            <span className="material-icons closeIcon">cancel</span>
        </div>,
        document.getElementById("declineMessageArea")
    );
};

export default DeclineMessage;
