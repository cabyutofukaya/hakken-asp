import React from "react";
import classNames from "classnames";

const StatusModal = ({
    id = "mdStatus",
    status,
    setStatus,
    statuses,
    handleUpdate,
    isUpdating
} = {}) => {
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
                        <select
                            value={status}
                            onChange={e => setStatus(e.target.value)}
                        >
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

export default StatusModal;
