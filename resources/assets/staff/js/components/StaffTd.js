import React from "react";
import classNames from "classnames";

// 自社担当のtableカラム（TD）
const StaffTd = ({ name, isDeleted }) => {
    return (
        <td
            className={classNames({
                txcGray: isDeleted
            })}
        >
            {name ?? "-"}
        </td>
    );
};

export default StaffTd;
