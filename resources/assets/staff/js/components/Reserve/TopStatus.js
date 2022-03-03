import React from "react";

// 上部ステータス部(催行済み・キャンセル、それ以外で出し分け)
const TopStatus = ({ reserve, status, reserveStatus }) => {
    if (reserve?.is_departed) {
        // 催行済みの場合はreserveStatusを表示
        return <span className="status gray fix">{reserveStatus}</span>;
    } else {
        if (reserve?.cancel_at) {
            // キャンセルの場合はステータス変更不可
            return <span className="status gray fix">{status}</span>;
        } else {
            return (
                <span
                    className="status blue js-modal-open"
                    data-target="mdStatus"
                >
                    {status}
                </span>
            );
        }
    }
};

export default TopStatus;
