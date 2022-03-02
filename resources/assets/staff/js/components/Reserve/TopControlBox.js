import React from "react";
import classNames from "classnames";

// キャンセル・削除ボタン。権限により出し分け
const TopControlBox = ({
    isCanceling,
    isDeleting,
    updatePermission,
    deletePermission
}) => {
    if (updatePermission || deletePermission) {
        return (
            <ul className="estimateControl">
                {updatePermission && (
                    <li>
                        <button
                            className={classNames("grayBtn", {
                                "js-modal-open": !isCanceling
                            })}
                            data-target="mdCxl"
                        >
                            キャンセル
                        </button>
                    </li>
                )}
                {deletePermission && (
                    <li>
                        <button
                            className={classNames("redBtn", {
                                "js-modal-open": !isDeleting
                            })}
                            data-target="mdDelete"
                        >
                            削除
                        </button>
                    </li>
                )}
            </ul>
        );
    }
    return null;
};

export default TopControlBox;
