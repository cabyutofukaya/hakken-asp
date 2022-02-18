import React, { useState } from "react";
import VisaEditModal from "./VisaEditModal";
import SmallDangerModal from "../SmallDangerModal";

// ビザ情報
const VisaInputArea = ({
    defaultValue,
    formSelects,
    customCategoryCode,
    visaUserCustomItems,
    consts
}) => {
    const [rows, setRows] = useState(defaultValue ?? []); // ビザ情報リスト

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
            <h2 className="optTit">
                ビザ情報
                <a
                    className="js-modal-open"
                    data-target="mdAddVisa"
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
                                    <span>番号</span>
                                </th>
                                <th>
                                    <span>国</span>
                                </th>
                                <th>
                                    <span>種別</span>
                                </th>
                                <th>
                                    <span>発行地</span>
                                </th>
                                <th>
                                    <span>発行日</span>
                                </th>
                                <th>
                                    <span>有効期限</span>
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
                                                    name={`user_visas[${index}][${k}]`}
                                                    value={row[k] ?? ""}
                                                />
                                            ))}
                                            <a
                                                className="js-modal-open"
                                                data-target="mdAddVisa"
                                                onClick={e =>
                                                    handleModalEdit(e, index)
                                                }
                                            >
                                                {row.number ?? "-"}
                                            </a>
                                        </td>
                                        <td>
                                            {formSelects.countries[
                                                row.country_code
                                            ] ?? "-"}
                                        </td>
                                        <td>{row.kind ?? "-"}</td>
                                        <td>
                                            {formSelects.countries[
                                                row.issue_place_code
                                            ] ?? "-"}
                                        </td>
                                        <td>{row.issue_date ?? "-"}</td>
                                        <td>{row.expiration_date ?? "-"}</td>
                                        <td>{row.note ?? "-"}</td>
                                        <td className="txtalc">
                                            <span
                                                className="material-icons js-modal-open"
                                                data-target="mdDeleteVisa"
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
            {/* ビザ情報削除モーダル */}
            <SmallDangerModal
                id="mdDeleteVisa"
                title="この情報を削除しますか？"
                handleAction={handleDelete}
            />
            {/* ビザ情報追加・編集モーダル */}
            <VisaEditModal
                handleChange={handleChange}
                handleEdit={handleEdit}
                countries={formSelects.countries}
                input={input}
                modalMode={editMode}
                // visaUserCustomItems={visaUserCustomItems}
                // customCategoryCode={customCategoryCode}
                // customFieldTypes={consts?.customFieldTypes}
                // customFieldInputTypes={consts?.customFieldInputTypes}
            />
        </>
    );
};

export default VisaInputArea;
