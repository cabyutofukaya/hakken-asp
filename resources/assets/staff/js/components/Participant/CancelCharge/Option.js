import React, { useContext } from "react";
import { ConstContext } from "../../ConstApp";
import { calcCancelProfit } from "./CancelChargeArea";
import ParticipantArea1 from "./ParticipantArea1";
import Price from "./Price";

const Option = ({
    data,
    setData,
    priceSetting,
    setPriceSetting,
    subjectInfo,
    handleRegist
}) => {
    const { subjectCategoryNames } = useContext(ConstContext);
    /**
     * 入力制御
     *
     * @param {*} index
     * @param {*} name
     * @param {*} value
     */
    const handleChange = (index, name, value) => {
        const row = _.cloneDeep(data.participants[index]);
        row[name] = value;

        if (name != "cancel_charge_profit") {
            // キャンセル料金 or 仕入先支払料金の場合は粗利を計算
            calcCancelProfit(row);
        }

        data.participants[index] = row;
        setData({ ...data });
    };

    // 料金一括変更
    const handleBulkChange = (kbn, name, value) => {
        const rows = _.cloneDeep(data.participants);
        for (const row of rows) {
            if (row?.age_kbn == kbn) {
                row[name] = value;
            }
            if (name != "cancel_charge_profit") {
                // キャンセル料金 or 仕入先支払料金の場合は粗利を計算
                calcCancelProfit(row);
            }
        }
        data.participants = rows;
        setData({ ...data });
    };

    const handleDetailRegist = e => {
        e.preventDefault();
        handleRegist(); // 登録処理
        $(".js-modal-close").trigger("click"); // モーダルclose
    };

    return (
        <>
            <div className="modal__content">
                <p className="mdTit mb20">キャンセル料設定&nbsp;</p>
                <ul className="sideList half">
                    <li>
                        <span className="inputLabel">科目</span>
                        <div className="selectBox">
                            <select value={data?.subject ?? ""} disabled={true}>
                                {subjectCategoryNames &&
                                    Object.keys(subjectCategoryNames).map(
                                        (val, index) => (
                                            <option key={index} value={val}>
                                                {subjectCategoryNames[val]}
                                            </option>
                                        )
                                    )}
                            </select>
                        </div>
                    </li>
                    <li>&nbsp;</li>
                    <li className="wd100 mr00">
                        <span className="inputLabel">商品名</span>
                        <input
                            type="text"
                            value={data.name ?? ""}
                            disabled={true}
                        />
                    </li>
                    <li className="wd50">
                        <span className="inputLabel">仕入れ先</span>
                        <div className="selectBox">
                            <select disabled={true}>
                                <option>{data.supplier_name ?? ""}</option>
                            </select>
                        </div>
                    </li>
                </ul>
                <hr className="sepBorder" />
                <Price
                    priceSetting={priceSetting}
                    setPriceSetting={setPriceSetting}
                    subjectInfo={subjectInfo}
                    handleBulkChange={handleBulkChange}
                />
                <hr className="sepBorder" />
                <ParticipantArea1
                    participants={data?.participants}
                    handleChange={handleChange}
                />
                <ul className="sideList mt40">
                    <li className="wd50">
                        <button
                            className="grayBtn"
                            onClick={e => {
                                {
                                    /** 科目を切り替えた時にjQueryのcloseボタンが動作しなくなるので、止むを得ずreactからclickイベント発火*/
                                }
                                $(".js-modal-close").trigger("click");
                            }}
                        >
                            閉じる
                        </button>
                    </li>
                    <li className="wd50 mr00">
                        <button className="redBtn" onClick={handleDetailRegist}>
                            登録する
                        </button>
                    </li>
                </ul>
            </div>
        </>
    );
};

export default Option;
