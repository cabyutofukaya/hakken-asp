import React from "react";
import classNames from "classnames";

/**
 * 削除ボタン、権限により出し分け
 *
 */
const TopDeleteBox = ({ isDeleting }) => {
    return (
        <div className="deleteControl">
            <button
                className={classNames("redBtn", {
                    "js-modal-open": !isDeleting
                })}
                data-target="mdDelete"
            >
                削除
            </button>
        </div>
    );
};

export default TopDeleteBox;
