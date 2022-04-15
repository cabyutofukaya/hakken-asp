import React, { useEffect, useState, useContext } from "react";
import { ConstContext } from "../ConstApp";
import { useMountedRef } from "../../../../hooks/useMountedRef";
import SmallDangerModal from "../SmallDangerModal";
import ParticipantEditModal from "./ParticipantEditModal";
import ReactLoading from "react-loading";
import classNames from "classnames";
import ParticipantCancelChargeModal from "./ParticipantCancelChargeModal";
import { RESERVE } from "../../constants";
import ParticipantCreateModal from "./ParticipantCreateModal";

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

// 取消API URL（キャンセルチャージナシ）
const getNonChargeCancelApiUrl = (
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
            return `/api/${agencyAccount}/estimate/${reception}/participant/${id}/no-cancel-charge/cancel`;
        case types.application_step_reserve: // 予約
            return `/api/${agencyAccount}/estimate/${reception}/participant/${id}/no-cancel-charge/cancel`;
        default:
            return null;
    }
};

// キャンセルチャージ設定URL(予約時のみ)
const getCancelChargeUrl = (urlPattern, id) => {
    return urlPattern.replace("#id#", id); // url文字列のID部分を置換
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
    ageKbnVals,
    birthdayYears,
    birthdayMonths,
    birthdayDays,
    countries,
    setDeleteRequestId,
    setCancelRequestId,
    permission,
    constsCommon,
    setSuccessMessage,
    updatedAt,
    setUpdatedAt
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

    const [editInput, setEditInput] = useState({}); // 編集モーダル入力値
    const [createInput, setCreateInput] = useState({}); // 作成モーダル入力値

    const [editMode, setEditMode] = useState(null); // モーダル表示時の登録or編集を判定

    const [representative, setRepresentative] = useState(null); // 代表
    const [cancelId, setCancelId] = useState(null); // 取消対象の参加者ID
    const [deleteId, setDeletelId] = useState(null); // 削除対象の参加者ID

    const [isLoading, setIsLoading] = useState(false); // リスト取得中
    const [isCreating, setIsCreating] = useState(false); // 作成処理中
    const [isEditing, setIsEditing] = useState(false); // 編集処理中
    const [isCanceling, setIsCanceling] = useState(false); // 取消処理中
    const [isDeleting, setIsDeleting] = useState(false); // 削除処理中
    const [isExistsPurchaseChecking, setIsExistsPurchaseChecking] = useState(
        false
    ); // 仕入データの存在チェック中か否か

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
            setUpdatedAt(response.data.data.reserve?.updated_at); // 予約レコード更新日時更新
        }
    };

    // 作成におけるinput制御
    const handleCreateChange = e => {
        setCreateInput({ ...createInput, [e.target.name]: e.target.value });
    };

    // 編集におけるinput制御
    const handleEditChange = e => {
        setEditInput({ ...editInput, [e.target.name]: e.target.value });
    };

    // 取り消しモーダルを表示
    // 対象参加者に対する仕入れがあるか無いかでモーダルを出し分け
    const handleModalCancel = async (e, id) => {
        e.preventDefault();

        if (!mounted.current) return;
        if (isExistsPurchaseChecking) return;

        setIsExistsPurchaseChecking(true);

        const response = await axios
            .get(
                `/api/${agencyAccount}/estimate/participant/${id}/is-exists-purchase-data`
            )
            .finally(() => {
                if (mounted.current) {
                    setIsExistsPurchaseChecking(false);
                }
            });
        if (response?.data?.result) {
            setCancelId(id); // 対象の参加者IDをセット
            response.data.result == "yes"
                ? $("[data-target=mdParticipantCancelChargeCard]").trigger(
                      "click"
                  )
                : $("[data-target=mdParticipantNoCancelChargeCard]").trigger(
                      "click"
                  );
        } else {
            alert("データの取得に失敗しました");
        }
    };

    // 新規登録モーダル
    const handleModalAdd = e => {
        e.preventDefault();
        setEditMode("create");
        setCreateInput({
            ad_number: _.filter(lists, item => {
                return item.age_kbn == ageKbnVals.age_kbn_ad;
            }).length,
            ch_number: _.filter(lists, item => {
                return item.age_kbn == ageKbnVals.age_kbn_ch;
            }).length,
            inf_number: _.filter(lists, item => {
                return item.age_kbn == ageKbnVals.age_kbn_inf;
            }).length
        }); // 初期値をセット
    };

    // 編集モーダル
    const handleModalEdit = (e, id) => {
        e.preventDefault();
        setEditMode("edit");

        // 選択行データからユーザー情報を取得
        const row = lists.find(row => row.id === id);

        setEditInput({
            ...defaultValue, // radioボタン等のデフォルト値
            ...row
        });
    };

    // 登録処理
    const handleCreateSubmit = async e => {
        e.preventDefault();

        if (!mounted.current) return;
        if (isCreating) return;

        // 人数が変わっていない場合は処理ナシ
        let changed = false;
        for (const row of [
            { column: "ad_number", kbn: ageKbnVals.age_kbn_ad },
            { column: "ch_number", kbn: ageKbnVals.age_kbn_ch },
            { column: "inf_number", kbn: ageKbnVals.age_kbn_inf }
        ]) {
            const inputNum = createInput?.[row.column] ?? 0;
            const rowNum = _.filter(lists, item => {
                return item.age_kbn == row.kbn;
            }).length;
            if (inputNum != rowNum) {
                changed = true;
                break;
            }
        }
        if (!changed) {
            $(".js-modal-close").trigger("click"); // モーダルclose
            return;
        }

        // 現在の人数と比較して人数が減っていたらエラー
        let err = [];
        [
            { column: "ad_number", kbn: ageKbnVals.age_kbn_ad, label: "大人" },
            { column: "ch_number", kbn: ageKbnVals.age_kbn_ch, label: "子供" },
            { column: "inf_number", kbn: ageKbnVals.age_kbn_inf, label: "幼児" }
        ].map(row => {
            const inputNum = createInput?.[row.column] ?? 0;
            const rowNum = _.filter(lists, item => {
                return item.age_kbn == row.kbn;
            }).length;
            if (inputNum < rowNum) {
                err.push(
                    `${row.label}人数を減らす場合は参加者リストから削除してください。`
                );
            }
        });
        if (err.length) {
            alert(err.join("\n"));
            return;
        }

        setIsCreating(true); // 多重処理制御

        let response = null;
        // 新規登録
        response = await axios
            .post(storeApiUrl, {
                ...createInput
            })
            .finally(() => {
                $(".js-modal-close").trigger("click"); // モーダルclose
                setTimeout(function() {
                    if (mounted.current) {
                        setIsCreating(false);
                    }
                }, 3000);
            });

        if (mounted.current && response?.data?.data) {
            setUpdatedAt(response.data.reserve.updated_at); // 予約レコード更新日時更新
            let message = "";
            if (response.data.reserve?.reserve_itinerary_exists == 1) {
                // 行程情報がある場合は書類設定の見直しと行程の更新を促す
                message =
                    "参加者を追加しました。料金情報を更新するため行程を更新し、帳票の当該ユーザーのチェックを有効にしてください。";
            } else {
                message = "参加者を追加しました。";
            }
            $("#successMessage .closeIcon")
                .parent()
                .slideDown();
            setSuccessMessage(message); // メッセージエリアを一旦slideDown(表示状態)してからメッセージをセット

            fetch();
        }
    };

    // 編集処理
    const handleEditSubmit = async e => {
        e.preventDefault();

        if (!mounted.current) return;
        if (isEditing) return;
        setIsEditing(true); // 多重処理制御

        let response = null;
        // if (editMode === "create") {
        //     // 新規登録
        //     response = await axios
        //         .post(storeApiUrl, {
        //             ...editInput
        //         })
        //         .finally(() => {
        //             $(".js-modal-close").trigger("click"); // モーダルclose
        //             setTimeout(function() {
        //                 if (mounted.current) {
        //                     setIsEditing(false);
        //                 }
        //             }, 3000);
        //         });
        // } else if (editMode === "edit") {
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
                    editInput?.id
                ),
                {
                    ...editInput,
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
        // }

        if (mounted.current && response?.data?.data) {
            setUpdatedAt(response.data.data.reserve.updated_at); // 予約レコード更新日時更新
            // if (
            //     editMode === "create" &&
            //     response.data.data.reserve?.reserve_itinerary_exists == 1
            // ) {
            //     // 新規登録で行程情報がある場合は書類設定の見直しと行程の更新を促す
            //     $("#successMessage .closeIcon")
            //         .parent()
            //         .slideDown();
            //     setSuccessMessage(
            //         "参加者を追加しました。料金情報を更新するため行程を更新し、帳票の当該ユーザーのチェックを有効にしてください。"
            //     ); // メッセージエリアを一旦slideDown(表示状態)してからメッセージをセット
            // }
            fetch();
        }
    };

    // 参加者取り消し処理(チャージナシ)
    const handleNonCharge = async e => {
        if (!mounted.current) return;
        if (isCanceling) return;

        setIsCanceling(true);

        const response = await axios
            .post(
                getNonChargeCancelApiUrl(
                    reception,
                    applicationStep,
                    applicationStepList,
                    agencyAccount,
                    estimateNumber,
                    reserveNumber,
                    cancelId
                ),
                {
                    reserve: { updated_at: updatedAt },
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
        if (mounted.current && response?.data?.data) {
            setUpdatedAt(response.data.data.reserve.updated_at); // 予約レコード更新日時更新
            fetch(); // リスト再取得。TODO 再取得は負荷が高いので更新した行のみ変更するような処理を検討する → 代表者を取り消した時に代表者フラグをoffにする処理が必要なので、やはり一覧取得したほうがよいかも
        }
    };
    // 参加者取り消し処理(チャージあり→チャージ設定ページへ遷移)
    const handleCancelCharge = () => {
        if (!mounted.current) return;
        if (isCanceling) return;
        setIsCanceling(false); // 一応、処理フラグを無効にしておく
        $(".js-modal-close").trigger("click"); // モーダルクローズ
        location.href = getCancelChargeUrl(
            constsCommon.participantCancelChargeUrlPattern,
            cancelId
        );
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
                ),
                {
                    data: { reserve: { updated_at: updatedAt } }
                }
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
        if (mounted.current && response?.data?.data) {
            setUpdatedAt(response.data.data.reserve?.updated_at); // 予約レコード更新日時更新
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
                {/**↓見積もり時は表示ナシでOK?*/}
                {applicationStep ==
                    applicationStepList.application_step_reserve && (
                    <td className="txtalc">
                        {permission?.participant_cancel && (
                            <>
                                <span
                                    className="material-icons"
                                    onClick={e => handleModalCancel(e, row?.id)}
                                >
                                    not_interested
                                </span>
                                {/**モーダルは仕入の有無によって出し分ける。出し分けの際は以下のダミー要素に対してクリックイベントを発火。仕入有り用と無し用モーダル */}
                                <span
                                    className="js-modal-open"
                                    data-target="mdParticipantNoCancelChargeCard"
                                ></span>
                                <span
                                    className="js-modal-open"
                                    data-target="mdParticipantCancelChargeCard"
                                ></span>
                            </>
                        )}
                        {!permission?.participant_cancel && <>-</>}
                    </td>
                )}
                <td className="txtalc">
                    {permission?.participant_delete && (
                        <span
                            className="material-icons js-modal-open"
                            data-target="mdParticipantDeleteCard"
                            onClick={e => handleModalDelete(e, row?.id)}
                        >
                            delete
                        </span>
                    )}
                    {!permission.participant_delete && <>-</>}
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
                {permission?.reserve_create && permission?.participant_create && (
                    <a
                        className="js-modal-open"
                        data-target="mdAddPerson"
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
                                {/**↓見積もり時は不要でok? */}
                                {applicationStep ==
                                    applicationStepList.application_step_reserve && (
                                    <th className="txtalc wd10">
                                        <span>取消</span>
                                    </th>
                                )}
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

            <ParticipantCreateModal
                id="mdAddPerson"
                input={createInput}
                handleChange={handleCreateChange}
                handleSubmit={handleCreateSubmit}
                isCreating={isCreating}
                permission={permission}
            />

            <ParticipantEditModal
                input={editInput}
                handleChange={handleEditChange}
                handleSubmit={handleEditSubmit}
                sexes={sexes}
                ageKbns={ageKbns}
                birthdayYears={birthdayYears}
                birthdayMonths={birthdayMonths}
                birthdayDays={birthdayDays}
                countries={countries}
                editMode={editMode}
                isEditing={isEditing}
                permission={permission}
            />

            {/**キャンセルモーダル(キャンセルチャージナシ) */}
            <SmallDangerModal
                id="mdParticipantNoCancelChargeCard"
                title="参加者を取り消しますか？"
                handleAction={handleNonCharge}
                isActioning={isCanceling}
                actionLabel="取り消す"
            />
            {/**キャンセルモーダル(キャンセルチャージあり) */}
            <ParticipantCancelChargeModal
                id="mdParticipantCancelChargeCard"
                defaultCheck={RESERVE.CANCEL_CHARGE_NO}
                nonChargeAction={handleNonCharge}
                chargeAction={handleCancelCharge}
                isActioning={isCanceling}
                title={"参加者を取り消しますか？"}
                positiveLabel={"取り消す"}
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
