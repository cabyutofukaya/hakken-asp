import React from "react";
import classNames from "classnames";
import TopDeleteBox from "./TopDeleteBox";

/**
 * キャンセル・削除ボタン
 *
 * キャンセル&仕入状態、権限により出し分け
 *
 * @param {bool} isCanceled キャンセル予約の場合はtrue
 * @param {bool} isCanceling キャンセル処理中か否か
 * @param {bool} existPurchaseData 仕入データがある場合はtrue
 * @param {bool} isDeleting 削除処理中か否か
 * @param {bool} updatePermission 更新権限
 * @param {bool} deletePermission 削除権限
 * @returns
 */
const TopControlBox = ({
    isCanceled,
    isCanceling,
    existPurchaseData,
    isDeleting,
    updatePermission, // キャンセルボタンを表示するかどうかの判定に使う予定だったが、キャンセルボタンの表示・非表示条件はupdate権限と連動できないので本パラメータはひとまず使用ナシ
    deletePermission
}) => {
    if (deletePermission && isCanceled && !existPurchaseData) {
        // キャンセル予約で仕入情報が無い場合は削除ボタンのみ表示
        return <TopDeleteBox isDeleting={isDeleting} />;
    } else {
        return (
            <ul className="estimateControl">
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
