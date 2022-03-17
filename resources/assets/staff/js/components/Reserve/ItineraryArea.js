import React, { useEffect, useState, useContext } from "react";
import { ConstContext } from "../ConstApp";
import { useMountedRef } from "../../../../hooks/useMountedRef";
import SmallDangerModal from "../SmallDangerModal";
import ReactLoading from "react-loading";
import { calcProfitRate } from "../../libs";
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
            return `/api/${agencyAccount}/estimate/${reception}/${step}/${estimateNumber}/itinerary/list`;
        case types.application_step_reserve: // 予約
            return `/api/${agencyAccount}/estimate/${reception}/${step}/${reserveNumber}/itinerary/list`;
        default:
            return null;
    }
};

const getCreateUrl = (
    reception,
    step,
    types,
    agencyAccount,
    estimateNumber,
    reserveNumber
) => {
    switch (step) {
        case types.application_step_draft: // 見積
            return `/${agencyAccount}/estimates/${reception}/${step}/${estimateNumber}/itinerary/create`;
        case types.application_step_reserve: // 予約
            return `/${agencyAccount}/estimates/${reception}/${step}/${reserveNumber}/itinerary/create`;
        default:
            return null;
    }
};

const getEnableApiUrl = (
    reception,
    step,
    types,
    agencyAccount,
    estimateNumber,
    reserveNumber
) => {
    switch (step) {
        case types.application_step_draft: // 見積
            return `/api/${agencyAccount}/estimate/${reception}/${step}/${estimateNumber}/itinerary/enabled`;
        case types.application_step_reserve: // 予約
            return null; // 現状予約時は有効の切り替え不可
        default:
            return null;
    }
};

const getDeleteApiUrl = (
    reception,
    step,
    types,
    agencyAccount,
    estimateNumber,
    reserveNumber,
    itineraryNumber
) => {
    switch (step) {
        case types.application_step_draft: // 見積
            return `/api/${agencyAccount}/estimate/${reception}/${step}/${estimateNumber}/itinerary/${itineraryNumber}`;
        case types.application_step_reserve: // 予約
            return null; // 現状予約時は削除処理不可
        default:
            return null;
    }
};

/**
 *
 * @param {*} applicationStep 申込状態（見積or予約）
 * @returns
 */
const ItineraryArea = ({
    isShow,
    reception,
    applicationStep,
    applicationStepList,
    estimateNumber,
    reserveNumber,
    currentItineraryNumber,
    setCurrentItineraryNumber,
    participantDeleteRequestId,
    participantCancelRequestId,
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

    // 作成URL
    const createApiUrl = getCreateUrl(
        reception,
        applicationStep,
        applicationStepList,
        agencyAccount,
        estimateNumber,
        reserveNumber
    );
    const [lists, setLists] = useState([]);

    const [deleteControlNumber, setDeletelControlNumber] = useState(null); // 削除対象の行程番号

    const [isLoading, setIsLoading] = useState(false); // リスト取得中
    const [isEditing, setIsEditing] = useState(false); // 編集処理中
    const [isDeleting, setIsDeleting] = useState(false); // 削除処理中

    const fetch = async () => {
        if (!mounted.current) return;
        if (isLoading) return;

        setIsLoading(true); // 二重読み込み禁止

        const response = await axios
            .get(listApiUrl, {
                params: {
                    sort: "created_at",
                    direction: "desc"
                }
            })
            .finally(() => {
                setIsLoading(false);
            });

        if (mounted.current && response?.data?.data) {
            const rows = response.data.data;
            setLists([...rows]);

            // 有効行程番号を更新
            setCurrentItineraryNumber(
                _.get(_.find(rows, { enabled: 1 }), "control_number")
            );
        }
    };
    useEffect(() => {
        if (isShow) {
            // 表示に切り替わったらリスト取得
            fetch();
        }
    }, [isShow, participantDeleteRequestId, participantCancelRequestId]); // 表示状態、参加者削除時、参加者取り消し時にリスト更新

    // 有効切り替え（※見積ステータス時のみ）
    const handleChangeEnabled = async e => {
        if (!mounted.current) return;
        if (isEditing) return;

        setIsEditing(true); // 二重読み込み禁止
        const controlNumber = e.target.value;

        const response = await axios
            .post(
                getEnableApiUrl(
                    reception,
                    applicationStep,
                    applicationStepList,
                    agencyAccount,
                    estimateNumber,
                    reserveNumber
                ),
                {
                    control_number: controlNumber,
                    _method: "put"
                }
            )
            .finally(() => {
                setIsEditing(false);
            });

        if (mounted.current && response?.data?.data) {
            setCurrentItineraryNumber(controlNumber); // 有効化をチェック
        }
    };

    // 削除ボタン
    const handleModalDelete = (e, controlNumber) => {
        e.preventDefault();
        setDeletelControlNumber(controlNumber);
    };

    // 削除処理（※見積ステータス時のみ）
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
                    deleteControlNumber
                )
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
            // ページネーションがないので削除後は特にリストの再取得は必要ないと思われるので、listsから不要行のカットで良いと思われる
            setLists([
                ...lists.filter(
                    row => row.control_number !== deleteControlNumber
                )
            ]);
            if (currentItineraryNumber === deleteControlNumber) {
                // 有効化されていた行が削除された場合は、有効行程番号を初期化
                setCurrentItineraryNumber(null);
            }
        }
    };

    // 新規作成ボタン
    const CreateButton = ({
        createApiUrl,
        applicationStep,
        applicationStepList,
        isLoading,
        lists
    }) => {
        // キャンセル予約の場合は追加不可。予約時は2個目以降の行程追加不可
        if (
            permission?.itinerary_create &&
            (applicationStep == applicationStepList.application_step_draft ||
                (applicationStep ==
                    applicationStepList.application_step_reserve &&
                    !isLoading &&
                    lists.length === 0))
        ) {
            return (
                <a
                    href="#"
                    onClick={e => {
                        e.preventDefault();
                        window.location.href = createApiUrl;
                    }}
                >
                    <span className="material-icons">add_circle</span>
                    追加
                </a>
            );
        }
        return null;
    };

    return (
        <>
            <h2 className="optTit">
                行程
                <CreateButton
                    createApiUrl={createApiUrl}
                    applicationStep={applicationStep}
                    applicationStepList={applicationStepList}
                    isLoading={isLoading}
                    lists={lists}
                />
            </h2>
            <div className="tableWrap dragTable">
                <div className="tableCont">
                    <table>
                        <thead>
                            <tr>
                                <th className="txtalc wd10">
                                    <span>有効</span>
                                </th>
                                <th>
                                    <span>行程表番号</span>
                                </th>
                                <th>
                                    <span>備考</span>
                                </th>
                                <th>
                                    <span>作成日</span>
                                </th>
                                <th>
                                    <span>更新日時</span>
                                </th>
                                <th>
                                    <span>GRS合計</span>
                                </th>
                                <th>
                                    <span>NET合計</span>
                                </th>
                                <th>
                                    <span>利益合計(利益率)</span>
                                </th>
                                <th className="txtalc">
                                    <span>ルーミングリスト</span>
                                </th>
                                <th className="txtalc wd10">
                                    <span>行程表</span>
                                </th>
                                <th className="txtalc wd10">
                                    <span>削除</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {isLoading && (
                                <tr>
                                    <td colSpan={11}>
                                        <ReactLoading
                                            type={"bubbles"}
                                            color={"#dddddd"}
                                        />
                                    </td>
                                </tr>
                            )}
                            {!isLoading && lists.length === 0 && (
                                <tr>
                                    <td colSpan={11}>データがありません。</td>
                                </tr>
                            )}
                            {!isLoading &&
                                lists.length > 0 &&
                                lists.map((row, index) => (
                                    <tr
                                        key={index}
                                        className={classNames({
                                            done:
                                                applicationStep ==
                                                    applicationStepList.application_step_reserve &&
                                                currentItineraryNumber !==
                                                    row?.control_number
                                        })}
                                    >
                                        <td className="txtalc checkBox">
                                            {/**予約状態の場合はチェック不可 */}
                                            <input
                                                type="radio"
                                                id={`koutei${index}`}
                                                name="koutei"
                                                value={row?.control_number}
                                                checked={
                                                    currentItineraryNumber ===
                                                    row?.control_number
                                                }
                                                onChange={handleChangeEnabled}
                                                disabled={
                                                    applicationStep ==
                                                        applicationStepList.application_step_reserve &&
                                                    currentItineraryNumber !==
                                                        row?.control_number
                                                }
                                            />
                                            <label htmlFor={`koutei${index}`}>
                                                &nbsp;
                                            </label>
                                        </td>
                                        <td>
                                            <a
                                                href="#"
                                                onClick={e => {
                                                    e.preventDefault();
                                                    window.location.href =
                                                        row?.edit_url;
                                                }}
                                            >
                                                {row?.control_number ?? "-"}
                                            </a>
                                        </td>
                                        <td>{row?.note ?? "-"}</td>
                                        <td>{row?.created_at ?? "-"}</td>
                                        <td>{row?.updated_at ?? "-"}</td>
                                        <td>
                                            ￥{row.sum_gross.toLocaleString()}
                                        </td>
                                        <td>
                                            ￥{row.sum_net.toLocaleString()}
                                        </td>
                                        <td>
                                            ￥
                                            {row.sum_gross_profit.toLocaleString()}
                                            (
                                            {calcProfitRate(
                                                row.sum_gross_profit,
                                                row.sum_gross
                                            ).toFixed(1)}
                                            %)
                                            {/** 利益率 = 利益 ÷ 売上 × 100 */}
                                        </td>
                                        <td className="txtalc">
                                            {row?.room_list_url ? (
                                                <a
                                                    href={`${row?.room_list_url}`}
                                                    target="_blank"
                                                >
                                                    <span className="material-icons">
                                                        picture_as_pdf
                                                    </span>
                                                </a>
                                            ) : (
                                                "-"
                                            )}
                                        </td>
                                        <td className="txtalc">
                                            <a
                                                href={row?.pdf_url ?? ""}
                                                target="_blank"
                                            >
                                                <span className="material-icons">
                                                    picture_as_pdf
                                                </span>
                                            </a>
                                        </td>
                                        <td className="txtalc">
                                            {/** 行程削除は見積もり時のみ可 */}
                                            {applicationStep ==
                                                applicationStepList.application_step_draft && (
                                                <span
                                                    className="material-icons js-modal-open"
                                                    data-target="mdItineraryDeleteCard"
                                                    onClick={e =>
                                                        handleModalDelete(
                                                            e,
                                                            row?.control_number
                                                        )
                                                    }
                                                >
                                                    delete
                                                </span>
                                            )}
                                            {applicationStep ==
                                                applicationStepList.application_step_reserve &&
                                                "-"}
                                        </td>
                                    </tr>
                                ))}
                        </tbody>
                    </table>
                </div>
            </div>
            <SmallDangerModal
                id="mdItineraryDeleteCard"
                title="この行程を削除しますか？"
                handleAction={handleDelete}
                isActioning={isDeleting}
            />
        </>
    );
};

export default ItineraryArea;
