import React from "react";

// キャンセル・削除ボタン。権限、催行済みか否かの状態により出し分け
const TopControlBox = ({
    reserve,
    isCanceling,
    isDeleting,
    updatePermission,
    deletePermission
}) => {
    if (reserve?.is_departed) {
        return null;
    } else {
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
    }
    return null;
};

export default TopControlBox;
