import React, { useState } from "react";
import ManagerEditModal from "./ManagerEditModal";
import SmallDangerModal from "../SmallDangerModal";
import { SEX } from "../../constants";

// 担当者情報
const ManagerInputArea = ({ defaultValue, formSelects }) => {
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
        setInput({ sex: SEX.MALE }); // 性別は「男性」で初期化
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
                取引先担当者情報
                <a
                    className="js-modal-open"
                    data-target="mdAddManager"
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
                                    <span>ID</span>
                                </th>
                                <th>
                                    <span>担当者名</span>
                                </th>
                                <th>
                                    <span>部署名</span>
                                </th>
                                <th>
                                    <span>メールアドレス</span>
                                </th>
                                <th>
                                    <span>電話番号</span>
                                </th>
                                <th>
                                    <span>DM</span>
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
                                            {/**入力値をhiddenフィールドにセット */}
                                            {Object.keys(row).map((k, i) => {
                                                return (
                                                    <input
                                                        key={i}
                                                        type="hidden"
                                                        name={`business_user_managers[${index}][${k}]`}
                                                        value={row[k] ?? ""}
                                                    />
                                                );
                                            })}
                                            <a
                                                className="js-modal-open"
                                                data-target="mdAddManager"
                                                onClick={e =>
                                                    handleModalEdit(e, index)
                                                }
                                            >
                                                {row.user_number ?? "-"}
                                                {/* <input
                                                    type="hidden"
                                                    name={`business_user_managers[${index}][user_number]`}
                                                    value={
                                                        row.user_number ?? ""
                                                    }
                                                /> */}
                                            </a>
                                        </td>
                                        <td>
                                            {row.name ?? "-"}
                                            {/* <input
                                                type="hidden"
                                                name={`business_user_managers[${index}][name]`}
                                                value={row.name ?? ""}
                                            /> */}
                                        </td>
                                        <td>
                                            {row.department_name ?? "-"}
                                            {/* <input
                                                type="hidden"
                                                name={`business_user_managers[${index}][department_name]`}
                                                value={
                                                    row.department_name ?? ""
                                                }
                                            /> */}
                                        </td>
                                        <td>
                                            {row.email ?? "-"}
                                            {/* <input
                                                type="hidden"
                                                name={`business_user_managers[${index}][email]`}
                                                value={row.email ?? ""}
                                            /> */}
                                        </td>
                                        <td>
                                            {row.tel ?? "-"}
                                            {/* <input
                                                type="hidden"
                                                name={`business_user_managers[${index}][tel]`}
                                                value={row.tel ?? ""}
                                            /> */}
                                        </td>
                                        <td>
                                            {formSelects.dms[row.dm] ?? "-"}
                                            {/* <input
                                                type="hidden"
                                                name={`business_user_managers[${index}][dm]`}
                                                value={row.dm ?? ""}
                                            /> */}
                                        </td>
                                        <td>
                                            {row.note ?? "-"}
                                            {/* <input
                                                type="hidden"
                                                name={`business_user_managers[${index}][note]`}
                                                value={row.note ?? ""}
                                            /> */}
                                        </td>
                                        <td className="txtalc">
                                            {/**最後の一人は削除不可 */}
                                            {rows.length > 1 && (
                                                <span
                                                    className="material-icons js-modal-open"
                                                    data-target="mdDeleteManager"
                                                    onClick={e =>
                                                        handleModalDelete(
                                                            e,
                                                            index
                                                        )
                                                    }
                                                >
                                                    delete
                                                </span>
                                            )}

                                            {/* <input
                                                type="hidden"
                                                name={`business_user_managers[${index}][id]`}
                                                value={row.id ?? ""}
                                            /> */}
                                        </td>
                                    </tr>
                                ))}
                        </tbody>
                    </table>
                </div>
            </div>
            {/* 削除モーダル */}
            <SmallDangerModal
                id="mdDeleteManager"
                title="この情報を削除しますか？"
                handleAction={handleDelete}
            />
            {/* 担当者情報追加・編集モーダル */}
            <ManagerEditModal
                handleChange={handleChange}
                handleEdit={handleEdit}
                input={input}
                dms={formSelects?.dms}
                sexes={formSelects?.sexes}
                modalMode={editMode}
            />
        </>
    );
};

export default ManagerInputArea;
