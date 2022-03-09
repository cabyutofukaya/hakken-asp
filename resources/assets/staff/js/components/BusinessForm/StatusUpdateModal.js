import React, { useState } from "react";
import classNames from "classnames";
import { useMountedRef } from "../../../../hooks/useMountedRef";

// APIを使ってステータス更新するモーダル
const StatusUpdateModal = ({
    id = "mdStatus",
    status,
    setStatus,
    statuses,
    handleUpdate,
    isUpdating
} = {}) => {
    const mounted = useMountedRef(); // マウント・アンマウント制御

    // 変更
    const handleChange = e => {
        if (mounted.current) {
            setStatus(e.target.value);
        }
    };

    return (
        <>
            <div
                id={id}
                className="modal js-modal"
                style={{ position: "fixed", left: 0, top: 0 }}
            >
                <div
                    className={classNames("modal__bg", {
                        "js-modal-close": !isUpdating
                    })}
                ></div>
                <div className="modal__content">
                    <p className="mdTit mb20">ステータス変更</p>
                    <div className="selectBox mb20">
                        <select value={status} onChange={handleChange}>
                            {Object.keys(statuses).map((k, i) => (
                                <option key={i} value={k}>
                                    {statuses[k]}
                                </option>
                            ))}
                        </select>
                    </div>
                    <ul className="sideList">
                        <li className="wd50">
                            <button
                                className="grayBtn js-modal-close"
                                disabled={isUpdating}
                            >
                                閉じる
                            </button>
                        </li>
                        <li className="wd50 mr00">
                            <button
                                className="blueBtn"
                                onClick={handleUpdate}
                                disabled={isUpdating}
                            >
                                更新する
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </>
    );
};

export default StatusUpdateModal;
