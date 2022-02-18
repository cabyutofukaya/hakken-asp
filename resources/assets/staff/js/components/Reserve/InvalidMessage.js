import React from "react";
import moment from "moment";

const InvalidMessage = ({ webReserveExt }) => {
    moment.locale("ja");
    let errors = [];

    if (webReserveExt?.rejection_at) {
        errors.push(
            `辞退済み相談案件です(${moment(webReserveExt.rejection_at).format(
                "YYYY/MM/DD HH:mm"
            )})。`
        );
    }
    if (webReserveExt?.web_consult?.cancel_at) {
        errors.push(
            `ユーザーにより取り消しされました(${moment(
                webReserveExt.web_consult.rejection_at
            ).format("YYYY/MM/DD HH:mm")})。`
        );
    }
    if (errors.length > 0) {
        return (
            <div id="errorMessage">
                {errors.map((str, i) => (
                    <p>{str}</p>
                ))}
            </div>
        );
    }
    return null;
};

export default InvalidMessage;
