import React from "react";
import classNames from "classnames";
import OnlyNumberInput from "../OnlyNumberInput";

const ParticipantCreateModal = ({
    id = "mdAddPerson",
    input,
    handleChange,
    isCreating,
    handleSubmit,
    permission
} = {}) => {
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
                <p className="mdTit mb20">参加者追加</p>
                <ul className="sideList mb40">
                    <li>
                        <span className="inputLabel">大人</span>
                        <OnlyNumberInput
                            value={input.ad_number ?? 0}
                            handleChange={e =>
                                handleChange({
                                    target: {
                                        name: "ad_number",
                                        value: e.target.value
                                    }
                                })
                            }
                        />
                    </li>
                    <li>
                        <span className="inputLabel">子供</span>
                        <OnlyNumberInput
                            value={input.ch_number ?? 0}
                            handleChange={e =>
                                handleChange({
                                    target: {
                                        name: "ch_number",
                                        value: e.target.value
                                    }
                                })
                            }
                        />
                    </li>
                    <li>
                        <span className="inputLabel">幼児</span>
                        <OnlyNumberInput
                            value={input.inf_number ?? 0}
                            handleChange={e =>
                                handleChange({
                                    target: {
                                        name: "inf_number",
                                        value: e.target.value
                                    }
                                })
                            }
                        />
                    </li>
                </ul>
                <ul className="sideList">
                    <li className="wd50">
                        <button
                            className="grayBtn js-modal-close"
                            disabled={isCreating}
                        >
                            閉じる
                        </button>
                    </li>
                    <li className="wd50 mr00">
                        <button
                            className="blueBtn"
                            disabled={isCreating}
                            onClick={handleSubmit}
                        >
                            登録する
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    );
};

export default ParticipantCreateModal;
