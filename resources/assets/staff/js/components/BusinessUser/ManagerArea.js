import React, { useEffect, useState, useContext } from "react";
import { ConstContext } from "../ConstApp";
import ManagerEditModal from "./ManagerEditModal";
import SmallDangerModal from "../SmallDangerModal";
import { useMountedRef } from "../../../../hooks/useMountedRef";
import { SEX } from "../../constants";

/**
 *
 * @param {array} dms DM選択値
 * @returns
 */
const ManagerArea = ({ isShow, userNumber, dms, sexes }) => {
    const { agencyAccount } = useContext(ConstContext);

    const mounted = useMountedRef(); // マウント・アンマウント制御

    const [rows, setRows] = useState([]);

    const [deleteId, setDeleteId] = useState(null); // 削除対象ID
    const [input, setInput] = useState({}); // 入力値
    const [editMode, setEditMode] = useState(null); // モーダル表示時の登録or編集を判定

    const [isLoading, setIsLoading] = useState(false); // リスト取得中
    const [isEditing, setIsEditing] = useState(false); // 編集処理中
    const [isDeleting, setIsDeleting] = useState(false); // 削除処理中

    // 一覧取得
    const fetch = async () => {
        if (!mounted.current) return;
        if (!isShow) return;
        if (isLoading) return;

        setIsLoading(true); // 二重読み込み禁止

        const response = await axios
            .get(
                `/api/${agencyAccount}/client/business/${userNumber}/manager/list`
            )
            .finally(() => {
                setIsLoading(false);
            });
        if (mounted.current && response?.data?.data) {
            setRows([...response.data.data]);
        }
    };

    useEffect(() => {
        fetch(); // 一覧取得
    }, [isShow]);

    // 入力値変更制御
    const handleChange = e => {
        setInput({ ...input, [e.target.name]: e.target.value });
    };

    // 追加モーダル表示
    const handleModalAdd = e => {
        e.preventDefault();
        setEditMode("create");
        setInput({ sex: SEX.MALE }); // 性別は「男性」で初期化
    };

    // 編集モーダル
    const handleModalEdit = (e, id) => {
        e.preventDefault();
        setEditMode("edit");
        setInput({ ...rows.find(row => row.id === id) });
    };

    // 追加処理
    const handleEdit = async e => {
        e.preventDefault();

        if (!mounted.current) return;
        if (isEditing) return;
        setIsEditing(true); // 多重処理制御

        let response = null;
        if (editMode === "create") {
            // 新規登録
            response = await axios
                .post(
                    `/api/${agencyAccount}/client/business/${userNumber}/manager`,
                    {
                        ...input
                    }
                )
                .finally(() => {
                    $(".js-modal-close").trigger("click"); // モーダルclose
                    setTimeout(function() {
                        if (mounted.current) {
                            setIsEditing(false);
                        }
                    }, 3000);
                });
        } else if (editMode === "edit") {
            // 編集
            response = await axios
                .post(
                    `/api/${agencyAccount}/client/business/${userNumber}/manager/${input.id}`,
                    {
                        ...input,
                        _method: "put"
                    }
                )
                .finally(() => {
                    $(".js-modal-close").trigger("click"); // モーダルclose
                    setTimeout(function() {
                        if (mounted.current) {
                            setIsEditing(false);
                        }
                    }, 3000);
                });
        }

        if (response) {
            fetch(); // リスト再取得
        }
    };

    // 削除ボタン
    const handleModalDelete = (e, id) => {
        e.preventDefault();
        setDeleteId(id);
    };

    // 削除処理
    const handleDelete = async e => {
        e.preventDefault();

        if (!mounted.current) return;
        if (isDeleting) return;
        setIsDeleting(true); // 多重処理制御

        const response = await axios
            .delete(
                `/api/${agencyAccount}/client/business/${userNumber}/manager/${deleteId}`
            )
            .finally(() => {
                $(".js-modal-close").trigger("click"); // 削除モーダルclose
                setTimeout(function() {
                    if (mounted.current) {
                        setIsDeleting(false);
                    }
                }, 3000);
            });

        if (response) {
            fetch(); // リスト再取得
        }
    };

    return (
        <>
            <h2 className="optTit mt60">
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
                                rows.map(row => (
                                    <tr key={row.id}>
                                        <td>
                                            <a
                                                className="js-modal-open"
                                                data-target="mdAddManager"
                                                onClick={e =>
                                                    handleModalEdit(e, row.id)
                                                }
                                            >
                                                {row.user_number ?? "-"}
                                            </a>
                                        </td>
                                        <td>{row.name ?? "-"}</td>
                                        <td>{row.department_name ?? "-"}</td>
                                        <td>{row.email ?? "-"}</td>
                                        <td>{row.tel ?? "-"}</td>
                                        <td>{row.dm_label ?? "-"}</td>
                                        <td>{row.note ?? "-"}</td>
                                        <td className="txtalc">
                                            {/** 最後の一人は削除不可 */}
                                            {rows.length > 1 && (
                                                <span
                                                    className="material-icons js-modal-open"
                                                    data-target="mdDeleteManager"
                                                    onClick={e =>
                                                        handleModalDelete(
                                                            e,
                                                            row.id
                                                        )
                                                    }
                                                >
                                                    delete
                                                </span>
                                            )}
                                        </td>
                                    </tr>
                                ))}
                        </tbody>
                    </table>
                </div>
            </div>
            {/* 取引先担当者情報削除モーダル */}
            <SmallDangerModal
                id="mdDeleteManager"
                title="この情報を削除しますか？"
                handleAction={handleDelete}
                isActioning={isDeleting}
            />
            {/* 取引先担当者情報追加・編集モーダル */}
            <ManagerEditModal
                handleChange={handleChange}
                handleEdit={handleEdit}
                input={input}
                modalMode={editMode}
                isEditing={isEditing}
                dms={dms}
                sexes={sexes}
            />
        </>
    );
};

export default ManagerArea;
