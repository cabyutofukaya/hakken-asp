import React, { useContext } from "react";
import CustomField from "../../CustomField";
import Price from "./Price";
import ProductNameInput from "./ProductNameInput";
import { getNameExObj } from "../../../libs";
import { ConstContext } from "../../ConstApp";
import { ReserveItineraryConstContext } from "../../ReserveItineraryConstApp"; // 下層コンポーネントに定数などを渡すコンテキスト
import ParticipantArea2 from "./ParticipantArea2";

/**
 * ホテル科目
 * @param {*} param0
 * @returns
 */
const Hotel = ({
    input,
    zeiKbns,
    participants,
    suppliers,
    targetAddRow,
    editPurchasingRowInfo,
    handleChange,
    rowDispatch,
    customFields,
    subjectCustomCategoryCode,
    customFieldCodes,
    defaultSubjectHotels
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
                    {/**カスタム項目を出力（区分） */}
                    {_.filter(customFields, {
                        code: customFieldCodes.subject_hotel_kbn
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
                        {/*商品名&商品コードからサジェスト*/}
                        <ProductNameInput
                            handleChange={handleChange}
                            subject={input?.subject ?? ""}
                            value={
                                input?.name_ex
                                    ? getNameExObj(input.name_ex)
                                    : { label: "", value: "" }
                            }
                            name="name"
                            defaultOptions={defaultSubjectHotels}
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
                <ul className="baseList mt30">
                    <li>
                        <span className="inputLabel">ホテル名</span>
                        <input
                            type="text"
                            value={input?.hotel_name ?? ""}
                            onChange={e =>
                                handleChange({
                                    type: "CHANGE_INPUT",
                                    payload: { hotel_name: e.target.value }
                                })
                            }
                        />
                    </li>
                    <li>
                        <span className="inputLabel">住所</span>
                        <input
                            type="text"
                            value={input?.address ?? ""}
                            onChange={e =>
                                handleChange({
                                    type: "CHANGE_INPUT",
                                    payload: { address: e.target.value }
                                })
                            }
                        />
                    </li>
                    <li className="wd40">
                        <span className="inputLabel">電話番号</span>
                        <input
                            type="tel"
                            value={input?.tel ?? ""}
                            onChange={e =>
                                handleChange({
                                    type: "CHANGE_INPUT",
                                    payload: { tel: e.target.value }
                                })
                            }
                        />
                    </li>
                    <li className="wd40">
                        <span className="inputLabel">FAX番号</span>
                        <input
                            type="tel"
                            value={input?.fax ?? ""}
                            onChange={e =>
                                handleChange({
                                    type: "CHANGE_INPUT",
                                    payload: { fax: e.target.value }
                                })
                            }
                        />
                    </li>
                    <li className="wd100">
                        <span className="inputLabel">URL</span>
                        <input
                            type="text"
                            value={input?.url ?? ""}
                            onChange={e =>
                                handleChange({
                                    type: "CHANGE_INPUT",
                                    payload: { url: e.target.value }
                                })
                            }
                        />
                    </li>
                </ul>
                <hr className="sepBorder" />
                <ul className="sideList mt20">
                    {/**カスタム項目（部屋タイプ、食事タイプ）。マスターの金額とは連動しないので変更不可 */}
                    {_.filter(customFields, f => {
                        return (
                            f.code ===
                                customFieldCodes.subject_hotel_room_type ||
                            f.code === customFieldCodes.subject_hotel_meal_type
                        );
                    }).map((row, index) => (
                        <CustomField
                            liClass={"wd30"}
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
                            disabled={true}
                        />
                    ))}
                </ul>
                <Price
                    input={input}
                    handleChange={handleChange}
                    zeiKbns={zeiKbns}
                />
                <hr className="sepBorder" />
                {/**参加者リスト */}
                <ParticipantArea2
                    input={input}
                    participants={participants}
                    zeiKbns={zeiKbns}
                    handleChange={handleChange}
                />
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
                    {_.filter(customFields, { code: null }).map(
                        (row, index) => (
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
                        )
                    )}
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

export default Hotel;
