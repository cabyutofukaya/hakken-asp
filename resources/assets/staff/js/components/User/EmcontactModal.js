import React from "react";

const EmcontactModal = ({ handleClick }) => {
    return (
        <div
            id="mdEmcontact"
            className="modal js-modal"
            style={{ position: "fixed", left: 0, top: 0 }}
        >
            <div className="modal__bg js-modal-close"></div>
            <div className="modal__content">
                <p className="mdTit mb20">緊急連絡先に設定しますか？</p>
                <ul className="sideList">
                    <li className="wd50">
                        <button className="grayBtn js-modal-close">
                            キャンセル
                        </button>
                    </li>
                    <li className="wd50 mr00">
                        <button className="blueBtn" onClick={handleClick}>
                            設定する
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    );
};

export default EmcontactModal;
