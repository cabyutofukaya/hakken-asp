import React from "react";
import moment from "moment";
import ReactDOM from "react-dom";
import classNames from "classnames";
// flatpickr
import "flatpickr/dist/themes/airbnb.css";
import { Japanese } from "flatpickr/dist/l10n/ja.js";
import Flatpickr from "react-flatpickr";

const OnlineRequestModal = ({
    id,
    values,
    input,
    handleAction,
    isActioning,
    handleChangeRequest,
    handleConsentRequest
}) => {
    return ReactDOM.createPortal(
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
                    <p className="mdTit mb20">オンライン相談依頼</p>
                    <ul className="onlineSelect">
                        {!input?.is_change && (
                            <li>
                                <span className="inputLabel">希望日時</span>
                                <p>
                                    {moment(
                                        values.consult_date,
                                        "YYYY/MM/DD HH:mm"
                                    ).format("YYYY/MM/DD HH:mm~")}
                                    <span
                                        className="change"
                                        onClick={e =>
                                            handleAction({
                                                target: {
                                                    name: "is_change",
                                                    value: true
                                                }
                                            })
                                        }
                                    >
                                        日時変更依頼
                                    </span>
                                </p>
                            </li>
                        )}
                        {input?.is_change && (
                            <li className="changeBox">
                                <span
                                    className="delete material-icons"
                                    onClick={e =>
                                        handleAction({
                                            target: {
                                                name: "is_change",
                                                value: false
                                            }
                                        })
                                    }
                                >
                                    close
                                </span>
                                <span className="inputLabel">変更依頼</span>
                                <ul className="sideList">
                                    <li className="wd40">
                                        <div className="calendar">
                                            <Flatpickr
                                                theme="airbnb"
                                                value={
                                                    input?.consult_date ?? ""
                                                }
                                                onChange={(date, dateStr) => {
                                                    handleAction({
                                                        target: {
                                                            name:
                                                                "consult_date",
                                                            value: dateStr
                                                        }
                                                    });
                                                }}
                                                options={{
                                                    dateFormat: "Y/m/d",
                                                    locale: {
                                                        ...Japanese
                                                    }
                                                }}
                                                render={(
                                                    {
                                                        defaultValue,
                                                        value,
                                                        ...props
                                                    },
                                                    ref
                                                ) => {
                                                    return (
                                                        <input
                                                            name="consult_date"
                                                            defaultValue={
                                                                value ?? ""
                                                            }
                                                            ref={ref}
                                                        />
                                                    );
                                                }}
                                            />
                                        </div>
                                    </li>
                                    <li className="wd20">
                                        <div className="selectBox">
                                            <select
                                                name="hour"
                                                value={input?.hour ?? ""}
                                                onChange={handleAction}
                                            >
                                                {_.range(6, 23).map((n, i) => {
                                                    const m = ("00" + n).slice(
                                                        -2
                                                    );
                                                    return (
                                                        <option
                                                            key={i}
                                                            value={m}
                                                        >
                                                            {m}
                                                        </option>
                                                    );
                                                })}
                                            </select>{" "}
                                        </div>
                                    </li>
                                    <li className="wd20">
                                        <div className="selectBox">
                                            <select
                                                name="minute"
                                                value={input?.minute ?? ""}
                                                onChange={handleAction}
                                            >
                                                {_.range(0, 60, 5).map(
                                                    (n, i) => {
                                                        const m = (
                                                            "00" + n
                                                        ).slice(-2);
                                                        return (
                                                            <option
                                                                key={i}
                                                                value={m}
                                                            >
                                                                {m}
                                                            </option>
                                                        );
                                                    }
                                                )}
                                            </select>
                                        </div>
                                    </li>
                                </ul>
                            </li>
                        )}
                    </ul>
                    <ul className="sideList">
                        <li className="wd50">
                            <button
                                className="grayBtn js-modal-close"
                                disabled={isActioning}
                            >
                                閉じる
                            </button>
                        </li>
                        <li className="wd50 mr00">
                            {!input?.is_change && (
                                <button
                                    className="blueBtn"
                                    disabled={isActioning}
                                    onClick={handleConsentRequest}
                                >
                                    承諾する
                                </button>
                            )}
                            {input?.is_change && (
                                <button
                                    className="blueBtn"
                                    disabled={isActioning}
                                    onClick={handleChangeRequest}
                                >
                                    変更依頼を送る
                                </button>
                            )}
                        </li>
                    </ul>
                </div>
            </div>
        </>,
        document.getElementById("onlineRequestModal")
    );
};

export default OnlineRequestModal;
