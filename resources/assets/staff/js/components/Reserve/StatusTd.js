import React from "react";
import classNames from "classnames";

const StatusTd = ({ status }) => {
    if (!status) {
        return <>-</>;
    } else {
        return (
            <span
                className={classNames("status", {
                    gray: status === "見積",
                    blue: status === "予約",
                    orange: status === "手配中",
                    green: status === "手配完了",
                    red: status === "CXL"
                })}
            >
                {status}
            </span>
        );
    }
};

export default StatusTd;
