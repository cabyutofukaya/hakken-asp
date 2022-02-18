import React from "react";
import classNames from "classnames";

const ConsultTd = ({ consultationUrl, agencyUnreadCount }) => {
    return (
        <td
            className={classNames("txtalc", {
                alart: agencyUnreadCount > 0
            })}
        >
            {consultationUrl && (
                <>
                    <a href={consultationUrl}>
                        <span className="material-icons">textsms</span>
                    </a>
                    {agencyUnreadCount > 0 && (
                        <span className="icoNoti">{agencyUnreadCount}</span>
                    )}
                </>
            )}
            {!consultationUrl && <>-</>}
        </td>
    );
};

export default ConsultTd;
