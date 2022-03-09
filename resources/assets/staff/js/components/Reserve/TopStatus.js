import React from "react";

// 上部ステータス部(催行済み・キャンセル、それ以外で出し分け)
const TopStatus = ({ reserve, status, reserveStatus }) => {
    if (reserve?.is_departed || reserve?.cancel_at) {
        // 催行済みの場合はreserveStatusを表示
        return <span className="status gray fix">{reserveStatus}</span>;
    } else {
        return (
            <span className="status blue js-modal-open" data-target="mdStatus">
                {status}
            </span>
        );
    }
};

export default TopStatus;
