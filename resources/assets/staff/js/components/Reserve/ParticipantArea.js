import React, { useEffect, useState, useContext } from "react";
import { ConstContext } from "../ConstApp";
import { useMountedRef } from "../../../../hooks/useMountedRef";
import SmallDangerModal from "../SmallDangerModal";
import ParticipantEditModal from "./ParticipantEditModal";
import ReactLoading from "react-loading";
import classNames from "classnames";

// 一覧取得API URL
const getListApiUrl = (
    reception,
    step,
    types,
    agencyAccount,
    estimateNumber,
    reserveNumber
) => {
    switch (step) {
        case types.application_step_draft: // 見積
            return `/api/${agencyAccount}/estimate/${reception}/${step}/${estimateNumber}/participant/list`;
        case types.application_step_reserve: // 予約
            return `/api/${agencyAccount}/estimate/${reception}/${step}/${reserveNumber}/participant/list`;
        default:
            return null;
    }
};

// 登録API URL
const getStoreApiUrl = (
    reception,
    step,
    types,
    agencyAccount,
    estimateNumber,
    reserveNumber
) => {
    switch (step) {
        case types.application_step_draft: // 見積
            return `/api/${agencyAccount}/estimate/${reception}/${step}/${estimateNumber}/participant`;
        case types.application_step_reserve: // 予約
            return `/api/${agencyAccount}/estimate/${reception}/${step}/${reserveNumber}/participant`;
        default:
            return null;
    }
};

// 更新API URL
const getUpdateApiUrl = (
    reception,
    step,
    types,
    agencyAccount,
    estimateNumber,
    reserveNumber,
    id
) => {
    switch (step) {
        case types.application_step_draft: // 見積
            return `/api/${agencyAccount}/estimate/${reception}/${step}/${estimateNumber}/participant/${id}`;
        case types.application_step_reserve: // 予約
            return `/api/${agencyAccount}/estimate/${reception}/${step}/${reserveNumber}/participant/${id}`;
        default:
            return null;
    }
};

// 代表者設定API URL
const getRepresentativeUpdateApiUrl = (
    reception,
    step,
    types,
    agencyAccount,
    estimateNumber,
    reserveNumber
) => {
    switch (step) {
        case types.application_step_draft: // 見積
            return `/api/${agencyAccount}/estimate/${reception}/${step}/${estimateNumber}/representative`;
        case types.application_step_reserve: // 予約
            return `/api/${agencyAccount}/estimate/${reception}/${step}/${reserveNumber}/representative`;
        default:
            return null;
    }
};

// 取消API URL
const getCancelApiUrl = (
    reception,
    step,
    types,
    agencyAccount,
    estimateNumber,
    reserveNumber,
    id
) => {
    switch (step) {
        case types.application_step_draft: // 見積
            return `/api/${agencyAccount}/estimate/${reception}/${step}/${estimateNumber}/participant/${id}/cancel`;
        case types.application_step_reserve: // 予約
            return `/api/${agencyAccount}/estimate/${reception}/${step}/${reserveNumber}/participant/${id}/cancel`;
        default:
            return null;
    }
};

// 削除API URL
const getDeleteApiUrl = (
    reception,
    step,
    types,
    agencyAccount,
    estimateNumber,
    reserveNumber,
    id
) => {
    switch (step) {
        case types.application_step_draft: // 見積
            return `/api/${agencyAccount}/estimate/${reception}/${step}/${estimateNumber}/participant/${id}`;
        case types.application_step_reserve: // 予約
            return `/api/${agencyAccount}/estimate/${reception}/${step}/${reserveNumber}/participant/${id}`;
        default:
            return null;
    }
};

/**
 *
 * @param {boolean} isShow 表示状態
 * @param {string} reception 種別(asp or web)
 * @returns
 */
const ParticipantArea = ({
    isShow,
    reception,
    applicationStep,
    applicationStepList,
    estimateNumber,
    reserveNumber,
    defaultValue,
    sexes,
    ageKbns,
    birthdayYears,
    birthdayMonths,
    birthdayDays,
    countries,
    setDeleteRequestId,
    setCancelRequestId,
    permission
}) => {
    const { agencyAccount } = useContext(ConstContext);

    const mounted = useMountedRef(); // マウント・アンマウント制御

    /////////// API URLを定義 ///////////
    // 一覧取得API
    const listApiUrl = getListApiUrl(
        reception,
        applicationStep,
        applicationStepList,
        agencyAccount,
        estimateNumber,
        reserveNumber
    );
    // 登録API
    const storeApiUrl = getStoreApiUrl(
        reception,
        applicationStep,
        applicationStepList,
        agencyAccount,
        estimateNumber,
        reserveNumber
    );
    // 代表者設定API
    const representativeUpdateApiUrl = getRepresentativeUpdateApiUrl(
        reception,
        applicationStep,
        applicationStepList,
        agencyAccount,
        estimateNumber,
        reserveNumber
    );

    const [lists, setLists] = useState([]);

    const [input, setInput] = useState({}); // 入力値
    const [editMode, setEditMode] = useState(null); // モーダル表示時の登録or編集を判定

    const [representative, setRepresentative] = useState(null); // 代表
    const [cancelId, setCancelId] = useState(null); // 取消対象の参加者ID
    const [deleteId, setDeletelId] = useState(null); // 削除対象の参加者ID

    const [isLoading, setIsLoading] = useState(false); // リスト取得中
    const [isEditing, setIsEditing] = useState(false); // 編集処理中
    const [isCanceling, setIsCanceling] = useState(false); // 取消処理中
    const [isDeleting, setIsDeleting] = useState(false); // 削除処理中

    const fetch = async () => {
        if (!mounted.current) return;
        if (isLoading) return;

        setIsLoading(true); // 二重読み込み禁止

        const response = await axios
            .get(listApiUrl, {
                params: {
                    sort: "created_at",
                    direction: "asc"
                }
            })
            .finally(() => {
                setIsLoading(false);
            });

        if (mounted.current && response?.data?.data) {
            const rows = response.data.data;
            setLists([...rows]);
            const repRow = _.find(rows, { representative: 1 });
            repRow && setRepresentative(repRow.id); // 代表者をセット
        }
    };

    useEffect(() => {
        if (isShow) {
            // 表示に切り替わったらリスト取得
            fetch();
        }
    }, [isShow]);

    // 代表者切替
    const handleSetRepresentative = async (e, id) => {
        if (!mounted.current) return;
        if (isEditing) return;

        setIsEditing(true); // 二重読み込み禁止

        const response = await axios
            .post(representativeUpdateApiUrl, {
                participant_id: id,
                _method: "put"
            })
            .finally(() => {
                setIsEditing(false);
            });

        if (mounted.current && response?.data?.data) {
            setRepresentative(id); //代表者をチェック
        }
    };

    // 登録・編集におけるinput制御
    const handleChange = e => {
        setInput({ ...input, [e.target.name]: e.target.value });
    };

    // 取り消しモーダル
    const handleModalCancel = (e, id) => {
        e.preventDefault();
        setCancelId(id);
    };

    // 新規登録モーダル
    const handleModalAdd = e => {
        e.preventDefault();
        setEditMode("create");
        setInput({ ...defaultValue }); // 初期値をセット
    };

    // 編集モーダル
    const handleModalEdit = (e, id) => {
        e.preventDefault();
        setEditMode("edit");

        // 選択行データからユーザー情報を取得
        const row = lists.find(row => row.id === id);

        setInput({
            ...defaultValue, // radioボタン等のデフォルト値
            ...row
        });
    };

    // 登録・編集処理
    const handleSubmit = async e => {
        e.preventDefault();

        if (!mounted.current) return;
        if (isEditing) return;
        setIsEditing(true); // 多重処理制御

        let response = null;
        if (editMode === "create") {
            // 新規登録
            response = await axios
                .post(storeApiUrl, {
                    ...input
                })
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
                    getUpdateApiUrl(
                        reception,
                        applicationStep,
                        applicationStepList,
                        agencyAccount,
                        estimateNumber,
                        reserveNumber,
                        input?.id
                    ),
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

        if (response?.data?.data) {
            if (
                editMode === "create" &&
                response.data.data.reserve?.reserve_itinerary_exists == 1
            ) {
                // 新規登録で行程情報がある場合は行程の更新と書類設定の見直しを促す
                alert(
                    "参加者を追加しました。\n追加された参加者の料金設定を更新する為、行程および各種帳票の更新を行ってください。"
                );
            }
            fetch(); // リスト再取得。TODO 再取得は負荷が高いので更新した行のみ変更するような処理を検討する
        }
    };

    // 取消を押した時の挙動
    const handleCancel = async e => {
        if (!mounted.current) return;
        if (isCanceling) return;

        setIsCanceling(true);

        const response = await axios
            .post(
                getCancelApiUrl(
                    reception,
                    applicationStep,
                    applicationStepList,
                    agencyAccount,
                    estimateNumber,
                    reserveNumber,
                    cancelId
                ),
                {
                    _method: "put"
                }
            )
            .finally(() => {
                $(".js-modal-close").trigger("click"); // 削除モーダルclose
                setCancelRequestId(Date.now()); // 取り消しID更新
                setTimeout(function() {
                    if (mounted.current) {
                        setIsCanceling(false);
                    }
                }, 3000);
            });
        if (response) {
            alert(
                "参加者を取り消しました。\n作成済み行程がある場合は参加者情報を更新するため対象行程の更新を行ってください。"
            );
            fetch(); // リスト再取得。TODO 再取得は負荷が高いので更新した行のみ変更するような処理を検討する → 代表者を取り消した時に代表者フラグをoffにする処理が必要なので、やはり一覧取得したほうがよいかも
        }
    };

    // 削除ボタン
    const handleModalDelete = (e, id) => {
        e.preventDefault();
        setDeletelId(id);
    };

    // 削除処理
    const handleDelete = async e => {
        e.preventDefault();
        if (!mounted.current) return;
        if (isDeleting) return;

        setIsDeleting(true);

        const response = await axios
            .delete(
                getDeleteApiUrl(
                    reception,
                    applicationStep,
                    applicationStepList,
                    agencyAccount,
                    estimateNumber,
                    reserveNumber,
                    deleteId
                )
            )
            .finally(() => {
                $(".js-modal-close").trigger("click"); // 削除モーダルclose
                setDeleteRequestId(Date.now()); // 削除ID更新
                setTimeout(function() {
                    if (mounted.current) {
                        setIsDeleting(false);
                    }
                }, 3000);
            });
        if (response) {
            alert(
                "参加者を削除しました。\n作成済み行程がある場合は参加者情報を更新するため対象行程の更新を行ってください。"
            );
            // ページネーションがないので削除後は特にリストの再取得は必要ないと思われるので、listsから不要行のカットで良いと思われる
            setLists([...lists.filter(row => row.id !== deleteId)]);
        }
    };

    // 通常行（未取消）
    const ParticipantRow = ({ row, index }) => {
        return (
            <tr key={index}>
                <td className="txtalc checkBox">
                    <input
                        type="radio"
                        id={`daihyou${index}`}
                        value={row?.id}
                        onChange={e => handleSetRepresentative(e, row?.id)}
                        checked={representative === row?.id}
                    />
                    <label htmlFor={`daihyou${index}`}>&nbsp;</label>
                </td>
                <td
                    className={classNames({
                        txcGray: row.user.is_deleted
                    })}
                >
                    <a
                        data-target="mdAddUser"
                        className={classNames({
                            "js-modal-open": !row.user.is_deleted
                        })}
                        onClick={e => handleModalEdit(e, row?.id)}
                    >
                        {row.user.user_number ?? "-"}
                    </a>
                </td>
                <td
                    className={classNames({
                        txcGray: row.user.is_deleted
                    })}
                >
                    {row.state_inc_name ?? "-"}
                </td>
                <td>{row.name_kana ?? "-"}</td>
                <td>{row.name_roman ?? "-"}</td>
                <td className="txtalc">{row.sex_label ?? "-"}</td>
                <td className="txtalc">{row.age ?? "-"}</td>
                <td className="txtalc">{row.age_kbn_label ?? "-"}</td>
                <td>{row.passport_number ?? "-"}</td>
                <td>{row.passport_expiration_date ?? "-"}</td>
                <td>{row.mobile_phone ?? "-"}</td>
                <td className="txtalc">
                    <span
                        className="material-icons js-modal-open"
                        data-target="mdParticipantCancelCard"
                        onClick={e => handleModalCancel(e, row?.id)}
                    >
                        not_interested
                    </span>
                </td>
                <td className="txtalc">
                    <span
                        className="material-icons js-modal-open"
                        data-target="mdParticipantDeleteCard"
                        onClick={e => handleModalDelete(e, row?.id)}
                    >
                        delete
                    </span>
                </td>
            </tr>
        );
    };

    // 取消行
    const ParticipantCancelRow = ({ row, index }) => {
        return (
            <tr key={index} className="cancel">
                <td className="txtalc">
                    <span className="material-icons calcenIcon">
                        not_interested
                    </span>
                </td>
                <td
                    className={classNames({
                        txcGray: row.user.is_deleted
                    })}
                >
                    <a
                        data-target="mdAddUser"
                        className={classNames({
                            "js-modal-open": !row.user.is_deleted
                        })}
                        onClick={e => handleModalEdit(e, row?.id)}
                    >
                        {row.user.user_number ?? "-"}
                    </a>
                </td>
                <td>{row.name ?? "-"}</td>
                <td>{row.name_kana ?? "-"}</td>
                <td>{row.name_roman ?? "-"}</td>
                <td className="txtalc">{row.sex_label ?? "-"}</td>
                <td className="txtalc">{row.age ?? "-"}</td>
                <td className="txtalc">{row.age_kbn_label ?? "-"}</td>
                <td>{row.passport_number ?? "-"}</td>
                <td>{row.passport_expiration_date ?? "-"}</td>
                <td>{row.mobile_phone ?? "-"}</td>
                <td className="txtalc"> -</td>
                <td className="txtalc">
                    <span
                        className="material-icons js-modal-open"
                        data-target="mdParticipantDeleteCard"
                        onClick={e => handleModalDelete(e, row?.id)}
                    >
                        delete
                    </span>
                </td>
            </tr>
        );
    };

    return (
        <>
            <h2 className="optTit">
                参加者
                {permission.reserve_update && (
                    <a
                        className="js-modal-open"
                        data-target="mdAddUser"
                        onClick={handleModalAdd}
                    >
                        <span className="material-icons">add_circle</span>追加
                    </a>
                )}
            </h2>
            <div className="tableWrap dragTable participant">
                <div className="tableCont">
                    <table>
                        <thead>
                            <tr>
                                <th className="txtalc wd10">
                                    <span>代表</span>
                                </th>
                                <th>
                                    <span>顧客番号</span>
                                </th>
                                <th>
                                    <span>氏名</span>
                                </th>
                                <th>
                                    <span>氏名(カナ)</span>
                                </th>
                                <th>
                                    <span>氏名(ローマ字)</span>
                                </th>
                                <th className="txtalc">
                                    <span>性別</span>
                                </th>
                                <th className="txtalc">
                                    <span>年齢</span>
                                </th>
                                <th className="txtalc">
                                    <span>年齢区分</span>
                                </th>
                                <th>
                                    <span>旅券番号</span>
                                </th>
                                <th>
                                    <span>旅券期限</span>
                                </th>
                                <th>
                                    <span>電話番号</span>
                                </th>
                                <th className="txtalc wd10">
                                    <span>取消</span>
                                </th>
                                <th className="txtalc wd10">
                                    <span>削除</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {isLoading && (
                                <tr>
                                    <td colSpan={13}>
                                        <ReactLoading
                                            type={"bubbles"}
                                            color={"#dddddd"}
                                        />
                                    </td>
                                </tr>
                            )}
                            {!isLoading && lists.length === 0 && (
                                <tr>
                                    <td colSpan={13}>データがありません。</td>
                                </tr>
                            )}
                            {!isLoading &&
                                lists &&
                                lists.map((row, index) =>
                                    row?.cancel ? (
                                        <ParticipantCancelRow
                                            row={row}
                                            index={index}
                                            key={index}
                                        />
                                    ) : (
                                        <ParticipantRow
                                            row={row}
                                            index={index}
                                            key={index}
                                        />
                                    )
                                )}
                        </tbody>
                    </table>
                </div>
            </div>

            <ParticipantEditModal
                input={input}
                handleChange={handleChange}
                handleSubmit={handleSubmit}
                sexes={sexes}
                ageKbns={ageKbns}
                birthdayYears={birthdayYears}
                birthdayMonths={birthdayMonths}
                birthdayDays={birthdayDays}
                countries={countries}
                editMode={editMode}
                isEditing={isEditing}
            />

            <SmallDangerModal
                id="mdParticipantCancelCard"
                title="参加者を取り消しますか？"
                handleAction={handleCancel}
                isActioning={isCanceling}
                actionLabel="取り消す"
            />

            <SmallDangerModal
                id="mdParticipantDeleteCard"
                title="参加者を削除しますか？"
                handleAction={handleDelete}
                isActioning={isDeleting}
            />
        </>
    );
};

export default ParticipantArea;
