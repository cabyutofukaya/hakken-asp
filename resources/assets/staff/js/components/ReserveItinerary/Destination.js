import React, { useContext } from "react";
import { ConstContext } from "../../components/ConstApp";
import CancelSubtotalRow from "./CancelSubtotalRow";
import ScheduleInputRows from "./ScheduleInputRows";
import SubtotalRow from "./SubtotalRow";
import { existsIsAliveCancelRow } from "../../libs";

/**
 *
 * @param {int} index 行番号
 * @param {array} input 入力値
 * @param {string} date 日付
 * @param {Object} zeiKbns 税区分
 * @param {Object} handleChange 入力値変更制御
 * @param {Object} handleDelete 行の削除関数
 * @parma {Ojbect} setDeletePurchasingRowInfo 削除対象の仕入行情報をセット
 * @param {Object} targetPurchasingDispatch 仕入詳細データの入力制御
 * @returns
 */
const Destination = ({
    index,
    input,
    date,
    participants,
    zeiKbns,
    handleChange,
    handleDelete,
    setDeletePurchasingRowInfo,
    targetPurchasingDispatch
}) => {
    const { purchaseCancel } = useContext(ConstContext);

    const inputName = `dates[${date}][${index}]`;

    // 仕入追加ボタン押下時処理。編集対象の仕入詳細データを初期化
    const handleAddPurchasingModal = e => {
        e.preventDefault();
        targetPurchasingDispatch({
            type: "ADD_PURCHASING_MODAL",
            payload: {
                date,
                index
            }
        });
    };

    /**
     *
     * 編集リンク押下時処理。編集対象の仕入詳細データをセット
     * @param {*} e
     * @param {*} no 行番号
     */
    const handleEditPurchasingModal = (e, no) => {
        e.preventDefault();
        targetPurchasingDispatch({
            type: "INITIAL_EDIT",
            payload: {
                date,
                index,
                no
            }
        });
    };

    /**
     * キャンセル仕入行の編集リンク押下時
     * @param {*} e
     * @param {*} no
     */
    const handleEditCancelPurchasingModal = (e, no) => {
        e.preventDefault();
        targetPurchasingDispatch({
            type: "INITIAL_EDIT",
            payload: {
                date,
                index,
                no
            }
        });
    };

    /**
     * 仕入行削除
     * モーダル表示
     *
     * @param {*} e
     * @param {*} no 対象行番号
     */
    const handlePurchasingDeleteModal = (e, no) => {
        e.preventDefault();
        setDeletePurchasingRowInfo({ date, index, no }); // 削除対象の仕入行情報
    };

    return (
        <div className="spotBlock">
            <div className="spotControl">
                <ul>
                    <li>
                        <span
                            className="material-icons js-modal-open"
                            data-target="mdScheduleDelete"
                            onClick={handleDelete}
                        >
                            delete
                        </span>
                    </li>
                </ul>
            </div>
            <div className="spotInfo">
                <ul className="schedule">
                    <ScheduleInputRows
                        index={index}
                        date={date}
                        input={input}
                        inputName={inputName}
                        handleChange={handleChange}
                    />
                </ul>
                <div className="subjectList">
                    <h3>
                        仕入科目
                        <a
                            href="#"
                            className="js-modal-open"
                            data-target="mdSubject"
                            onClick={handleAddPurchasingModal}
                        >
                            <span className="material-icons">add_circle</span>
                            追加
                        </a>
                    </h3>
                    {input?.reserve_purchasing_subjects && (
                        <SubtotalRow
                            date={date}
                            reservePurchasingSubjects={
                                input.reserve_purchasing_subjects
                            }
                            inputName={inputName}
                            zeiKbns={zeiKbns}
                            handleEditPurchasingModal={
                                handleEditPurchasingModal
                            }
                            handlePurchasingDeleteModal={
                                handlePurchasingDeleteModal
                            }
                        />
                    )}
                </div>
                {/**キャンセル仕入行あり */}
                {existsIsAliveCancelRow(
                    input?.reserve_purchasing_subjects ?? []
                ) && (
                    <CancelSubtotalRow
                        date={date}
                        reservePurchasingSubjects={
                            input.reserve_purchasing_subjects
                        }
                        inputName={inputName}
                        zeiKbns={zeiKbns}
                        handleEditCancelPurchasingModal={
                            handleEditCancelPurchasingModal
                        }
                    />
                )}
            </div>
        </div>
    );
};

export default Destination;
