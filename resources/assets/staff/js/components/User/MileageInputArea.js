import React, { useState } from "react";
import MileageEditModal from "./MileageEditModal";
import SmallDangerModal from "../SmallDangerModal";

// マイレージ情報
const MileageInputArea = ({
    defaultValue,
    formSelects,
    customCategoryCode,
    consts,
    mileageUserCustomItems
}) => {
    const [rows, setRows] = useState(defaultValue ?? []); // リスト

    const [currentIndex, setCurrentIndex] = useState(0); // 編集対象行No.
    const [input, setInput] = useState({}); // 入力値
    const [editMode, setEditMode] = useState(null); // モーダル表示時の登録or編集を判定

    // 入力値変更制御
    const handleChange = e => {
        setInput({ ...input, [e.target.name]: e.target.value });
    };

    // 追加モーダル表示
    const handleModalAdd = e => {
        e.preventDefault();
        setEditMode("create");
        setInput({});
        setCurrentIndex(rows.length);
    };

    // 編集モーダル
    const handleModalEdit = (e, index) => {
        e.preventDefault();
        setEditMode("edit");
        setInput({ ...rows[index] });
        setCurrentIndex(index);
    };

    // 追加処理
    const handleEdit = e => {
        e.preventDefault();
        $(".js-modal-close").trigger("click"); // モーダルclose
        rows[currentIndex] = input;
        setRows([...rows]);
    };

    // 削除ボタン
    const handleModalDelete = (e, index) => {
        e.preventDefault();
        setCurrentIndex(index);
    };

    // 削除処理
    const handleDelete = e => {
        e.preventDefault();
        $(".js-modal-close").trigger("click"); // モーダルclose
        setRows(rows.filter((row, index) => index !== currentIndex));
    };

    return (
        <>
            <h2 className="optTit mt40">
                マイレージ
                <a
                    className="js-modal-open"
                    data-target="mdAddMile"
                    onClick={handleModalAdd}
                >
                    <span className="material-icons">add_circle</span>追加
                </a>
            </h2>
            <div className="tableWrap dragTable">
                <div className="tableCont">
                    <table>
                        <thead>
                            <tr>
                                <th>
                                    <span>航空会社</span>
                                </th>
                                <th>
                                    <span>カード番号</span>
                                </th>
                                <th>
                                    <span>備考</span>
                                </th>
                                <th className="txtalc wd10">
                                    <span>削除</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {rows &&
                                rows.map((row, index) => (
                                    <tr key={index}>
                                        <td>
                                            {/**入力された値を隠しフィールドにセット。idは設定されている？ */}
                                            {Object.keys(row).map((k, i) => (
                                                <input
                                                    key={i}
                                                    type="hidden"
                                                    name={`user_mileages[${index}][${k}]`}
                                                    value={row[k] ?? ""}
                                                />
                                            ))}
                                            <a
                                                className="js-modal-open"
                                                data-target="mdAddMile"
                                                onClick={e =>
                                                    handleModalEdit(e, index)
                                                }
                                            >
                                                {row[
                                                    consts
                                                        .codeUserCustomerAirplaneCompanyKey
                                                ] ?? "-"}
                                            </a>
                                        </td>
                                        <td>{row.card_number ?? "-"}</td>
                                        <td>{row.note ?? "-"}</td>
                                        <td className="txtalc">
                                            <span
                                                className="material-icons js-modal-open"
                                                data-target="mdDeleteMile"
                                                onClick={e =>
                                                    handleModalDelete(e, index)
                                                }
                                            >
                                                delete
                                            </span>
                                        </td>
                                    </tr>
                                ))}
                        </tbody>
                    </table>
                </div>
            </div>
            {/* 削除モーダル */}
            <SmallDangerModal
                id="mdDeleteMile"
                title="この情報を削除しますか？"
                handleAction={handleDelete}
            />
            {/* マイレージ情報追加・編集モーダル */}
            <MileageEditModal
                handleChange={handleChange}
                handleEdit={handleEdit}
                input={input}
                modalMode={editMode}
                customFields={mileageUserCustomItems}
                customCategoryCode={customCategoryCode}
            />
        </>
    );
};

export default MileageInputArea;
