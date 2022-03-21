import React from "react";
import classNames from "classnames";

const SmallDangerModal = ({
    id,
    title,
    handleAction,
    isActioning = false,
    actionLabel = "削除する",
    cancelLabel = "閉じる"
} = {}) => {
    return (
        <>
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
                    <p className="mdTit mb20">{title}</p>
                    <ul className="sideList">
                        <li className="wd50">
                            <button
                                className="grayBtn js-modal-close"
                                disabled={isActioning}
                            >
                                {cancelLabel}
                            </button>
                        </li>
                        <li className="wd50 mr00">
                            <button
                                className="redBtn"
                                onClick={e => handleAction(e)}
                                disabled={isActioning}
                            >
                                {actionLabel}
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </>
    );
};

export default SmallDangerModal;
