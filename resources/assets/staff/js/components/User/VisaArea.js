import React, { useEffect, useState, useContext } from "react";
import { ConstContext } from "../ConstApp";
import VisaEditModal from "./VisaEditModal";
import SmallDangerModal from "../SmallDangerModal";
import { useMountedRef } from "../../../../hooks/useMountedRef";
import classNames from "classnames";

const VisaArea = ({ userNumber, countries, customCategoryCode }) => {
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
        if (isLoading) return;

        setIsLoading(true); // 二重読み込み禁止

        const response = await axios
            .get(`/api/${agencyAccount}/client/person/${userNumber}/visa/list`)
            .finally(() => {
                setIsLoading(false);
            });
        if (mounted.current && response?.data?.data) {
            setRows([...response.data.data]);
        }
    };

    useEffect(() => {
        fetch(); // 一覧取得
    }, []);

    // 入力値変更制御
    const handleChange = e => {
        setInput({ ...input, [e.target.name]: e.target.value });
    };

    // 追加モーダル表示
    const handleModalAdd = e => {
        e.preventDefault();
        setEditMode("create");
        setInput({});
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
                    `/api/${agencyAccount}/client/person/${userNumber}/visa`,
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
                    `/api/${agencyAccount}/client/person/${userNumber}/visa/${input.id}`,
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
                `/api/${agencyAccount}/client/person/${userNumber}/visa/${deleteId}`
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
            <h2 className="optTit mt40">
                ビザ情報
                <a
                    data-target="mdAddVisa"
                    onClick={handleModalAdd}
                    className={classNames({
                        "js-modal-open": !isEditing
                    })}
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
                                rows.map(row => (
                                    <tr key={row.id}>
                                        <td>
                                            <a
                                                className="js-modal-open"
                                                data-target="mdAddVisa"
                                                onClick={e =>
                                                    handleModalEdit(e, row.id)
                                                }
                                            >
                                                {row.number ?? "-"}
                                            </a>
                                        </td>
                                        <td>{row.country.name ?? "-"}</td>
                                        <td>{row.kind ?? "-"}</td>
                                        <td>{row.issue_place.name ?? "-"}</td>
                                        <td>{row.issue_date ?? "-"}</td>
                                        <td>{row.expiration_date ?? "-"}</td>
                                        <td>{row.note ?? "-"}</td>
                                        <td className="txtalc">
                                            <span
                                                className="material-icons js-modal-open"
                                                data-target="mdDeleteVisa"
                                                onClick={e =>
                                                    handleModalDelete(e, row.id)
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
                isActioning={isDeleting}
            />
            {/* ビザ情報追加・編集モーダル */}
            <VisaEditModal
                handleChange={handleChange}
                handleEdit={handleEdit}
                input={input}
                countries={countries}
                modalMode={editMode}
                isEditing={isEditing}
                customCategoryCode={customCategoryCode}
            />
        </>
    );
};

export default VisaArea;
