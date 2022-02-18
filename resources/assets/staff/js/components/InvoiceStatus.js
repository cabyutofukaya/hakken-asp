import React from "react";
import classNames from "classnames";

// 請求管理リストのステータスカラム
const InvoiceStatus = ({ isDeposited, status, status_label, statusVals }) => {
    return (
        <span
            className={classNames("status", {
                blue: !isDeposited && status == statusVals.status_billed,
                red: !isDeposited && status == statusVals.status_unclaimed,
                gray: isDeposited
            })}
        >
            {isDeposited && "入金済み"}
            {!isDeposited && (status_label ?? "-")}
        </span>
    );
};

export default InvoiceStatus;
