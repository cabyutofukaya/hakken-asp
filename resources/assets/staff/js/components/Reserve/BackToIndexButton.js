import React from "react";

// 詳細ページの「indexへ戻る」ボタン。催行済みか否かでURLを出し分け
const BackToIndexButton = ({
    isDeparted,
    reserveIndexUrl,
    departedIndexUrl
}) => {
    return (
        <button
            className="grayBtn"
            onClick={e => {
                e.preventDefault();
                if (isDeparted) {
                    window.location.href = departedIndexUrl;
                } else {
                    window.location.href = reserveIndexUrl;
                }
            }}
        >
            <span className="material-icons">arrow_back_ios</span>
            一覧に戻る
        </button>
    );
};

export default BackToIndexButton;
