import React from "react";
import CustomField from "../../CustomField";
import Price from "./Price";
import ProductNameInput from "./ProductNameInput";
import OnlyNumberInput from "../../OnlyNumberInput";
import { RESERVE_ITINERARY } from "../../../constants";
import { getNameExObj } from "../../../libs";

/**
 *
 * @param {*} subject 科目名
 * @returns
 */
const Option = ({
    input,
    zeiKbns,
    participants,
    suppliers,
    targetAddRow,
    editPurchasingRowInfo,
    handleChange,
    rowDispatch,
    subjectCategories,
    customFields,
    subjectCustomCategoryCode,
    customFieldCodes,
    defaultSubjectOptions
}) => {
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
                            >
                                {subjectCategories &&
                                    Object.keys(subjectCategories).map(
                                        (val, index) => (
                                            <option key={index} value={val}>
                                                {subjectCategories[val]}
                                            </option>
                                        )
                                    )}
                            </select>
                        </div>
                    </li>
                    {/**カスタム項目を出力（区分） */}
                    {_.filter(customFields, {
                        code: customFieldCodes.subject_option_kbn
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
                            defaultOptions={defaultSubjectOptions}
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
                <hr className="sepBorder" />
                <Price
                    input={input}
                    handleChange={handleChange}
                    zeiKbns={zeiKbns}
                />
                <hr className="sepBorder" />
                <div className="modalPriceList mt20">
                    <table className="baseTable">
                        <thead>
                            <tr>
                                <th className="wd10">有効</th>
                                <th>座席</th>
                                <th>REF番号</th>
                                <th>氏名</th>
                                <th className="txtalc">性別</th>
                                <th className="txtalc">年齢</th>
                                <th className="txtalc">年齢区分</th>
                                <th>税抜単価</th>
                                <th className="txtalc">税区分</th>
                                <th>税込GROSS単価</th>
                                <th>仕入れ額</th>
                                <th>手数料率</th>
                                <th>NET単価</th>
                                <th>粗利</th>
                            </tr>
                        </thead>
                        <tbody>
                            {participants &&
                                participants.map((participant, index) => (
                                    <tr key={index}>
                                        <td className="wd10">
                                            {participant.cancel == 0 && (
                                                <div className="checkBox">
                                                    <input
                                                        type="checkbox"
                                                        value={
                                                            input
                                                                ?.participants?.[
                                                                index
                                                            ]?.valid ?? "0"
                                                        }
                                                        id={`participant${index}`}
                                                        onChange={e =>
                                                            handleChange({
                                                                type:
                                                                    "CHANGE_PARTICIPANT_CHECKBOX",
                                                                index,
                                                                name: "valid",
                                                                payload:
                                                                    e.target
                                                                        .value
                                                            })
                                                        }
                                                        checked={
                                                            input
                                                                .participants?.[
                                                                index
                                                            ]?.valid
                                                        }
                                                    />
                                                    <label
                                                        htmlFor={`participant${index}`}
                                                    ></label>
                                                </div>
                                            )}
                                        </td>
                                        <td>
                                            <input
                                                type="text"
                                                value={
                                                    input?.participants?.[index]
                                                        ?.seat ?? ""
                                                }
                                                onChange={e =>
                                                    handleChange({
                                                        type:
                                                            "CHANGE_PARTICIPANT_INPUT",
                                                        index,
                                                        name: "seat",
                                                        payload: e.target.value
                                                    })
                                                }
                                            />
                                        </td>
                                        <td>
                                            <input
                                                type="text"
                                                value={
                                                    input?.participants?.[index]
                                                        ?.reference_number ?? ""
                                                }
                                                onChange={e =>
                                                    handleChange({
                                                        type:
                                                            "CHANGE_PARTICIPANT_INPUT",
                                                        index,
                                                        name:
                                                            "reference_number",
                                                        payload: e.target.value
                                                    })
                                                }
                                            />
                                        </td>
                                        <td>
                                            {participant?.name ?? "-"}(
                                            {participant?.name_kana ?? "-"})
                                        </td>
                                        <td className="txtalc">
                                            {participant?.sex_label ?? "-"}
                                        </td>
                                        <td className="txtalc">
                                            {participant?.age ?? "-"}
                                        </td>
                                        <td className="txtalc">
                                            {participant?.age_kbn_label ?? "-"}
                                        </td>
                                        <td>
                                            <OnlyNumberInput
                                                value={
                                                    input?.participants?.[index]
                                                        ?.gross_ex ?? 0
                                                }
                                                handleChange={e =>
                                                    handleChange({
                                                        type:
                                                            "CHANGE_PARTICIPANT_PRICE_INPUT",
                                                        index,
                                                        name: "gross_ex",
                                                        payload: e.target.value
                                                    })
                                                }
                                            />
                                        </td>
                                        <td className="txtalc taxTd">
                                            <div className="selectBox">
                                                <select
                                                    value={
                                                        input?.participants?.[
                                                            index
                                                        ]?.zei_kbn ?? 0
                                                    }
                                                    onChange={e =>
                                                        handleChange({
                                                            type:
                                                                "CHANGE_PARTICIPANT_PRICE_INPUT",
                                                            index,
                                                            name: "zei_kbn",
                                                            payload:
                                                                e.target.value
                                                        })
                                                    }
                                                >
                                                    {zeiKbns &&
                                                        Object.keys(
                                                            zeiKbns
                                                        ).map((val, index) => (
                                                            <option
                                                                key={index}
                                                                value={val}
                                                            >
                                                                {zeiKbns[val]}
                                                            </option>
                                                        ))}
                                                </select>
                                            </div>
                                        </td>
                                        <td>
                                            <OnlyNumberInput
                                                value={
                                                    input?.participants?.[index]
                                                        ?.gross ?? 0
                                                }
                                                handleChange={e =>
                                                    handleChange({
                                                        type:
                                                            "CHANGE_PARTICIPANT_PRICE_INPUT",
                                                        index,
                                                        name: "gross",
                                                        payload: e.target.value
                                                    })
                                                }
                                            />
                                        </td>
                                        <td>
                                            <OnlyNumberInput
                                                value={
                                                    input?.participants?.[index]
                                                        ?.cost ?? 0
                                                }
                                                handleChange={e =>
                                                    handleChange({
                                                        type:
                                                            "CHANGE_PARTICIPANT_PRICE_INPUT",
                                                        index,
                                                        name: "cost",
                                                        payload: e.target.value
                                                    })
                                                }
                                            />
                                        </td>
                                        <td className="txtalc">
                                            <div className="priceInput per">
                                                <OnlyNumberInput
                                                    value={
                                                        input?.participants?.[
                                                            index
                                                        ]?.commission_rate ?? 0
                                                    }
                                                    handleChange={e =>
                                                        handleChange({
                                                            type:
                                                                "CHANGE_PARTICIPANT_PRICE_INPUT",
                                                            index,
                                                            name:
                                                                "commission_rate",
                                                            payload:
                                                                e.target.value
                                                        })
                                                    }
                                                />
                                            </div>
                                        </td>
                                        <td>
                                            <OnlyNumberInput
                                                value={
                                                    input?.participants?.[index]
                                                        ?.net ?? 0
                                                }
                                                handleChange={e =>
                                                    handleChange({
                                                        type:
                                                            "CHANGE_PARTICIPANT_PRICE_INPUT",
                                                        index,
                                                        name: "net",
                                                        payload: e.target.value
                                                    })
                                                }
                                            />
                                        </td>
                                        <td>
                                            <OnlyNumberInput
                                                value={
                                                    input?.participants?.[index]
                                                        ?.gross_profit ?? 0
                                                }
                                                handleChange={e =>
                                                    handleChange({
                                                        type:
                                                            "CHANGE_PARTICIPANT_PRICE_INPUT",
                                                        index,
                                                        name: "gross_profit",
                                                        payload: e.target.value
                                                    })
                                                }
                                            />
                                        </td>
                                    </tr>
                                ))}
                        </tbody>
                    </table>
                </div>
                <hr className="sepBorder" />

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
                        return f.code !== customFieldCodes.subject_option_kbn;
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
                        {input.mode === RESERVE_ITINERARY.MODE_CREATE && (
                            <button
                                className="blueBtn"
                                onClick={handleRegistBtn}
                            >
                                登録する
                            </button>
                        )}
                        {input.mode === RESERVE_ITINERARY.MODE_EDIT && (
                            <button className="blueBtn" onClick={handleEditBtn}>
                                登録する
                            </button>
                        )}
                    </li>
                </ul>
            </div>
        </>
    );
};

export default Option;
