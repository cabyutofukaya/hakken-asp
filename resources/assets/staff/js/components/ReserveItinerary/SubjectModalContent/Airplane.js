import React, { useContext } from "react";
import CustomField from "../../CustomField";
import Price from "./Price";
import ProductNameInput from "./ProductNameInput";
import { getNameExObj } from "../../../libs";
import { ConstContext } from "../../ConstApp";
import { ReserveItineraryConstContext } from "../../ReserveItineraryConstApp"; // 下層コンポーネントに定数などを渡すコンテキスト
import ParticipantArea1 from "./ParticipantArea1";

/**
 * 航空券科目
 * @param {*} param0
 * @returns
 */
const Airplane = ({
    input,
    zeiKbns,
    participants,
    suppliers,
    cities,
    targetAddRow,
    editPurchasingRowInfo,
    handleChange,
    rowDispatch,
    customFields,
    subjectCustomCategoryCode,
    customFieldCodes,
    defaultSubjectAirplanes
}) => {
    const { subjectCategoryNames } = useContext(ConstContext);

    const { modes } = useContext(ReserveItineraryConstContext);
    /**
     * 登録ボタン押下
     * @param {*} e
     */
    const handleRegistBtn = e => {
        e.preventDefault();

        const errors = [];
        if (!input?.name) {
            errors.push("商品名が設定されていません。");
        }
        if (!input?.supplier_id) {
            errors.push("仕入先が設定されていません。");
        }
        if (errors.length > 0) {
            alert(errors.join("\n"));
            return;
        }

        $(".js-modal-close").trigger("click"); // モーダルclose

        rowDispatch({
            type: "ADD_PURCHASING_ROW",
            payload: {
                data: input,
                date: targetAddRow?.date,
                index: targetAddRow?.index
            }
        });
    };

    /**
     * 更新ボタン押下
     */
    const handleEditBtn = e => {
        e.preventDefault();

        const errors = [];
        if (!input?.name) {
            errors.push("商品名が設定されていません。");
        }
        if (!input?.supplier_id) {
            errors.push("仕入先が設定されていません。");
        }
        if (errors.length > 0) {
            alert(errors.join("\n"));
            return;
        }

        $(".js-modal-close").trigger("click"); // モーダルclose

        rowDispatch({
            type: "UPDATE_PURCHASING_ROW",
            payload: {
                data: input,
                date: editPurchasingRowInfo?.date,
                index: editPurchasingRowInfo?.index,
                no: editPurchasingRowInfo?.no
            }
        });
    };

    return (
        <>
            <div className="modal__content">
                <p className="mdTit mb20">仕入科目追加</p>
                <ul className="sideList half">
                    <li>
                        <span className="inputLabel">科目</span>
                        <div className="selectBox">
                            <select
                                value={input?.subject ?? ""}
                                onChange={e =>
                                    handleChange({
                                        type: "CHANGE_SUBJECT",
                                        payload: { subject: e.target.value }
                                    })
                                }
                                disabled={input?.purchasingLock}
                            >
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
                        {/**商品名&商品コードからサジェスト */}
                        <ProductNameInput
                            handleChange={handleChange}
                            subject={input?.subject ?? ""}
                            value={
                                input?.name_ex
                                    ? getNameExObj(input.name_ex)
                                    : { label: "", value: "" }
                            }
                            name="name"
                            defaultOptions={defaultSubjectAirplanes}
                            readOnly={input?.purchasingLock}
                        />
                    </li>
                    <li className="wd50">
                        <span className="inputLabel">仕入れ先</span>
                        <div className="selectBox">
                            <select
                                value={input?.supplier_id ?? ""}
                                onChange={e =>
                                    handleChange({
                                        type: "CHANGE_INPUT",
                                        payload: { supplier_id: e.target.value }
                                    })
                                }
                                disabled={input?.purchasingLock}
                            >
                                {suppliers &&
                                    Object.keys(suppliers)
                                        .sort((a, b) => {
                                            // 数字ソート
                                            return a - b;
                                        })
                                        .map((val, index) => (
                                            <option key={index} value={val}>
                                                {suppliers[val]}
                                            </option>
                                        ))}
                            </select>
                        </div>
                    </li>
                </ul>
                <ul className="sideList half mt30">
                    {/**カスタム項目を出力（航空会社） */}
                    {_.filter(customFields, {
                        code: customFieldCodes.subject_airplane_company
                    }).map((row, index) => (
                        <CustomField
                            key={row.id}
                            customCategoryCode={subjectCustomCategoryCode}
                            type={row?.type}
                            inputType={row?.input_type}
                            label={row?.name}
                            value={input?.[row?.key] ?? ""}
                            list={row?.list}
                            uneditItem={row?.unedit_item}
                            handleChange={e =>
                                handleChange({
                                    type: "CHANGE_INPUT",
                                    payload: {
                                        [row?.key]: e.target.value
                                    }
                                })
                            }
                        />
                    ))}
                    <li>
                        <span className="inputLabel">予約クラス</span>
                        <input
                            type="text"
                            value={input?.booking_class ?? ""}
                            onChange={e =>
                                handleChange({
                                    type: "CHANGE_INPUT",
                                    payload: { booking_class: e.target.value }
                                })
                            }
                        />
                    </li>
                    <li>
                        <span className="inputLabel">出発地</span>
                        <div className="selectBox">
                            <select
                                value={input?.departure_id ?? ""}
                                onChange={e =>
                                    handleChange({
                                        type: "CHANGE_INPUT",
                                        payload: {
                                            departure_id: e.target.value
                                        }
                                    })
                                }
                            >
                                {cities &&
                                    Object.keys(cities)
                                        .sort((a, b) => {
                                            // 数字ソート
                                            return a - b;
                                        })
                                        .map((val, index) => (
                                            <option key={index} value={val}>
                                                {cities[val]}
                                            </option>
                                        ))}
                            </select>
                        </div>
                    </li>
                    <li>
                        <span className="inputLabel">目的地</span>
                        <div className="selectBox">
                            <select
                                value={input?.destination_id ?? ""}
                                onChange={e =>
                                    handleChange({
                                        type: "CHANGE_INPUT",
                                        payload: {
                                            destination_id: e.target.value
                                        }
                                    })
                                }
                            >
                                {cities &&
                                    Object.keys(cities)
                                        .sort((a, b) => {
                                            // 数字ソート
                                            return a - b;
                                        })
                                        .map((val, index) => (
                                            <option key={index} value={val}>
                                                {cities[val]}
                                            </option>
                                        ))}
                            </select>
                        </div>
                    </li>
                </ul>
                <hr className="sepBorder" />
                <Price
                    input={input}
                    handleChange={handleChange}
                    zeiKbns={zeiKbns}
                />
                <hr className="sepBorder" />
                {/**参加者リスト */}
                <ParticipantArea1
                    input={input}
                    participants={participants}
                    zeiKbns={zeiKbns}
                    handleChange={handleChange}
                />
                <ul className="baseList mt20 mb40">
                    <li>
                        <span className="inputLabel">備考</span>
                        <input
                            type="text"
                            value={input?.note ?? ""}
                            onChange={e =>
                                handleChange({
                                    type: "CHANGE_INPUT",
                                    payload: { note: e.target.value }
                                })
                            }
                        />
                    </li>
                    {/**管理コードが設定されていないカスタム項目を出力 */}
                    {_.filter(customFields, f => {
                        return (
                            f.code !== customFieldCodes.subject_airplane_company
                        );
                    }).map((row, index) => (
                        <CustomField
                            key={index}
                            customCategoryCode={subjectCustomCategoryCode}
                            type={row?.type}
                            inputType={row?.input_type}
                            label={row?.name}
                            value={input?.[row?.key] ?? ""}
                            list={row?.list}
                            uneditItem={row?.unedit_item}
                            handleChange={e =>
                                handleChange({
                                    type: "CHANGE_INPUT",
                                    payload: {
                                        [row?.key]: e.target.value
                                    }
                                })
                            }
                        />
                    ))}
                </ul>
                <ul className="sideList">
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
                        {input.mode === modes.purchasing_mode_create && (
                            <button
                                className="blueBtn"
                                onClick={handleRegistBtn}
                            >
                                登録する
                            </button>
                        )}
                        {input.mode === modes.purchasing_mode_edit && (
                            <button className="blueBtn" onClick={handleEditBtn}>
                                更新する
                            </button>
                        )}
                    </li>
                </ul>
            </div>
        </>
    );
};

export default Airplane;
