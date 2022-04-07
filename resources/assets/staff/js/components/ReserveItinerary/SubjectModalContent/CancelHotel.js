import React, { useContext } from "react";
import CustomField from "../../CustomField";
import CancelParticipantArea2 from "./CancelParticipantArea2";
import { ConstContext } from "../../ConstApp";
import { ReserveItineraryConstContext } from "../../ReserveItineraryConstApp"; // 下層コンポーネントに定数などを渡すコンテキスト

/**
 * ホテル科目(キャンセル用)
 * @param {*} param0
 * @returns
 */
const CancelHotel = ({
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

    const { modes, isCanceled } = useContext(ReserveItineraryConstContext);

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
            {/**料金以外のフィールドは変更不可 */}
            <div className="modal__content">
                <p className="mdTit mb20">キャンセルした仕入科目</p>
                <ul className="sideList half">
                    <li>
                        <span className="inputLabel">科目</span>
                        <div className="selectBox">
                            <select
                                value={input?.subject ?? ""}
                                disabled={true}
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
                        <input
                            type="text"
                            value={input?.name ?? ""}
                            disabled={true}
                        />
                    </li>
                    <li className="wd50">
                        <span className="inputLabel">仕入れ先</span>
                        <div className="selectBox">
                            <select
                                value={input?.supplier_id ?? ""}
                                disabled={true}
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
                            disabled={true}
                        />
                    </li>
                    <li>
                        <span className="inputLabel">住所</span>
                        <input
                            type="text"
                            value={input?.address ?? ""}
                            disabled={true}
                        />
                    </li>
                    <li className="wd40">
                        <span className="inputLabel">電話番号</span>
                        <input
                            type="tel"
                            value={input?.tel ?? ""}
                            disabled={true}
                        />
                    </li>
                    <li className="wd40">
                        <span className="inputLabel">FAX番号</span>
                        <input
                            type="tel"
                            value={input?.fax ?? ""}
                            disabled={true}
                        />
                    </li>
                    <li className="wd100">
                        <span className="inputLabel">URL</span>
                        <input
                            type="text"
                            value={input?.url ?? ""}
                            disabled={true}
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
                            handleChange={() => {}}
                            disabled={true}
                        />
                    ))}
                </ul>
                <hr className="sepBorder" />
                <CancelParticipantArea2
                    input={input}
                    participants={participants}
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
                <ul className="sideList mt40 central">
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
                    {!isCanceled && (
                        <li className="wd50 mr00">
                            {input.mode === modes.purchasing_mode_edit && (
                                <button
                                    className="blueBtn"
                                    onClick={handleEditBtn}
                                >
                                    更新する
                                </button>
                            )}
                        </li>
                    )}
                </ul>
            </div>
        </>
    );
};

export default CancelHotel;
