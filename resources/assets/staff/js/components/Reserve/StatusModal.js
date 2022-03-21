import React, { useState } from "react";
import { useMountedRef } from "../../../../hooks/useMountedRef";
import classNames from "classnames";

/**
 *
 * @param {string} updatedAt 予約情報更新日時
 * @returns
 */
const StatusModal = ({
    id = "mdStatus",
    apiUrl,
    status,
    statuses,
    updatedAt,
    setUpdatedAt,
    changeStatus
} = {}) => {
    const mounted = useMountedRef(); // マウント・アンマウント制御

    const [value, setValue] = useState(status ?? "");
    const [isChanging, setIsChanging] = useState(false); // ステータス変更中か否か

    // 変更
    const handleChange = e => {
        if (mounted.current) {
            setValue(e.target.value);
        }
    };

    // 更新ボタン
    const handleUpdate = async e => {
        if (!mounted.current || value === status || isChanging) {
            // アンマウント、値が変わっていない、処理中の場合は処理ナシ
            return;
        }

        setIsChanging(true); // 処理が完了するまでクリック禁止

        const response = await axios
            .post(apiUrl, {
                status: value,
                updated_at: updatedAt,
                _method: "put"
            })
            .finally(() => {
                $(".js-modal-close").trigger("click"); // モーダルclose
                setTimeout(function() {
                    if (mounted.current) {
                        setIsChanging(false);
                    }
                }, 3000);
            });

        if (mounted.current && response?.data?.data) {
            // ステータス更新成功
            changeStatus(value);
            setUpdatedAt(response.data.data.updated_at); // 予約情報更新日時も更新。ステータスの同時編集制御に使用
        }
    };

    // 閉じるボタン
    const handleClose = e => {
        $(".js-modal-close").trigger("click"); // モーダルclose
        if (mounted.current) {
            setValue(status); // select選択値を元に戻しておく
        }
    };

    return (
        <div
            id={id}
            className="modal js-modal"
            style={{ position: "fixed", left: 0, top: 0 }}
        >
            {/**.js-modal-closeをはずしてもjquery側からレイヤーclickでレイヤーが消えてまうのでやむを得ずfalseで固定 */}
            <div
                className={classNames("modal__bg", {
                    "js-modal-close": false
                })}
            ></div>
            <div className="modal__content">
                <p className="mdTit mb20">ステータス変更</p>
                <div className="selectBox mb20">
                    <select value={value} onChange={handleChange}>
                        {Object.keys(statuses)
                            .sort((a, b) => a - b)
                            .map((k, index) => (
                                <option key={index} value={statuses[k]}>
                                    {statuses[k]}
                                </option>
                            ))}
                    </select>
                </div>
                <ul className="sideList">
                    <li className="wd50">
                        <button
                            className="grayBtn"
                            onClick={handleClose}
                            disabled={isChanging}
                        >
                            閉じる
                        </button>
                    </li>
                    <li className="wd50 mr00">
                        <button
                            className="blueBtn"
                            onClick={handleUpdate}
                            disabled={isChanging}
                        >
                            更新する
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    );
};

export default StatusModal;
