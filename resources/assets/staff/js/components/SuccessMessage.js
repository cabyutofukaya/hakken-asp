import React from "react";
import { useMountedRef } from "../../../hooks/useMountedRef";

const SuccessMessage = ({ message, setMessage }) => {
    const mounted = useMountedRef(); // マウント・アンマウント制御

    const handleClick = e => {
        window.setTimeout(function() {
            if (mounted.current) {
                setMessage("");
            }
        }, 1000); // スライドdownしてからメッセージを消す
    };
    return (
        <>
            {message ? (
                <div id="successMessage">
                    <p>
                        <span className="material-icons">check_circle</span>
                        {message}
                    </p>
                    <span
                        className="material-icons closeIcon"
                        onClick={handleClick}
                    >
                        cancel
                    </span>
                </div>
            ) : null}
        </>
    );
};

export default SuccessMessage;
