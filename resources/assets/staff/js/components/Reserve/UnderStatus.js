import React from "react";

// 下部ステータス部。キャンセルか否かによって表示を出し分け
const UnderStatus = ({ isCancel, status }) => {
    return (
        <span className="status blue js-modal-open" data-target="mdStatus">
            {status}
            <span className="material-icons settingIcon">settings</span>
        </span>
    );
};

export default UnderStatus;
