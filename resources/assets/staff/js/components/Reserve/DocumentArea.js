import React, { useState, useEffect, useContext } from "react";
import { ConstContext } from "../ConstApp";
import { useMountedRef } from "../../../../hooks/useMountedRef";
import ReactLoading from "react-loading";
import SmallDangerModal from "../SmallDangerModal";
import classNames from "classnames";

// 一覧取得API URL
const getListApiUrl = (
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
            return `/api/${agencyAccount}/estimate/${reception}/${step}/${estimateNumber}/itinerary/${itineraryNumber}/confirm/list`;
        case types.application_step_reserve: // 予約
            return `/api/${agencyAccount}/estimate/${reception}/${step}/${reserveNumber}/itinerary/${itineraryNumber}/confirm/list`;
        default:
            return null;
    }
};

/**
 *
 * @param {*} reception
 * @param {*} step
 * @param {*} types
 * @param {*} agencyAccount
 * @param {*} estimateNumber
 * @param {*} reserveNumber
 * @param {*} itineraryNumber
 * @param {*} departedQuery 催行済みの場合に付与するgetクエリ
 * @returns
 */
const getCreateApiUrl = (
    reception,
    step,
    types,
    agencyAccount,
    estimateNumber,
    reserveNumber,
    itineraryNumber,
    departedQuery
) => {
    switch (step) {
        case types.application_step_draft: // 見積
            return `/${agencyAccount}/estimates/${reception}/${step}/${estimateNumber}/itinerary/${itineraryNumber}/confirm${departedQuery}`;
        case types.application_step_reserve: // 予約
            return `/${agencyAccount}/estimates/${reception}/${step}/${reserveNumber}/itinerary/${itineraryNumber}/confirm${departedQuery}`;
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
    itineraryNumber,
    confirmNumber
) => {
    switch (step) {
        case types.application_step_draft: // 見積
            return `/api/${agencyAccount}/estimate/${reception}/${step}/${estimateNumber}/itinerary/${itineraryNumber}/confirm/${confirmNumber}`;
        case types.application_step_reserve: // 予約
            return `/api/${agencyAccount}/estimate/${reception}/${step}/${reserveNumber}/itinerary/${itineraryNumber}/confirm/${confirmNumber}`;
        default:
            return null;
    }
};

const DocumentArea = ({
    isShow,
    reception,
    applicationStep,
    applicationStepList,
    estimateNumber,
    reserveNumber,
    currentItineraryNumber,
    hasOriginalDocumentQuoteTemplate,
    constsCommon
}) => {
    const { agencyAccount } = useContext(ConstContext);

    const mounted = useMountedRef(); // マウント・アンマウント制御

    const [lists, setLists] = useState([]);

    const [deleteConfirmNumber, setDeleteConfirmNumber] = useState(null); // 削除対象の確認番号

    const [isLoading, setIsLoading] = useState(false); // リスト取得中
    const [isDeleting, setIsDeleting] = useState(false); // 削除処理中

    const fetch = async () => {
        if (!mounted.current) return;
        if (isLoading) return;

        setIsLoading(true); // 二重読み込み禁止

        const response = await axios
            .get(
                getListApiUrl(
                    reception,
                    applicationStep,
                    applicationStepList,
                    agencyAccount,
                    estimateNumber,
                    reserveNumber,
                    currentItineraryNumber
                ),
                {
                    params: {
                        sort: "confirm_number",
                        direction: "desc"
                    }
                }
            )
            .finally(() => {
                if (mounted.current) {
                    setIsLoading(false);
                }
            });

        if (mounted.current && response?.data?.data) {
            const rows = response.data.data;
            setLists([...rows]);
        }
    };

    useEffect(() => {
        if (isShow) {
            if (!currentItineraryNumber) {
                //無効な行程IDの場合はリストを空に
                setLists([]);
            } else {
                // 表示に切り替わったらリスト取得
                fetch();
            }
        }
    }, [isShow, currentItineraryNumber]);

    // 削除ボタン
    const handleModalDelete = (e, confirmNumber) => {
        e.preventDefault();
        setDeleteConfirmNumber(confirmNumber);
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
                    currentItineraryNumber,
                    deleteConfirmNumber
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
                    row => row.confirm_number !== deleteConfirmNumber
                )
            ]);
        }
    };

    return (
        <>
            <h2 className="optTit">
                帳票
                {hasOriginalDocumentQuoteTemplate && currentItineraryNumber && (
                    <a
                        href={getCreateApiUrl(
                            reception,
                            applicationStep,
                            applicationStepList,
                            agencyAccount,
                            estimateNumber,
                            reserveNumber,
                            currentItineraryNumber,
                            constsCommon?.departedQuery ?? ""
                        )}
                    >
                        <span className="material-icons">add_circle</span>追加
                    </a>
                )}
                {/** デフォルト系以外の「見積・予約確認」テンプレートが設定されていて、かつ行程IDが選択されていればリンクを表示*/}
            </h2>
            <div className="tableWrap dragTable">
                <div className="tableCont">
                    <table>
                        <thead>
                            <tr>
                                <th>
                                    <span>帳票番号</span>
                                </th>
                                <th>
                                    <span>タイトル</span>
                                </th>
                                <th>
                                    <span>宛先</span>
                                </th>
                                <th>
                                    <span>合計金額</span>
                                </th>
                                <th>
                                    <span>発行日</span>
                                </th>
                                <th className="txtalc wd10">
                                    <span>PDF</span>
                                </th>
                                <th className="txtalc wd10">
                                    <span>削除</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {isLoading && (
                                <tr>
                                    <td colSpan={7}>
                                        <ReactLoading
                                            type={"bubbles"}
                                            color={"#dddddd"}
                                        />
                                    </td>
                                </tr>
                            )}
                            {!isLoading && lists.length === 0 && (
                                <tr>
                                    <td colSpan={7}>データがありません。</td>
                                </tr>
                            )}
                            {!isLoading &&
                                lists.length > 0 &&
                                lists.map((row, index) => (
                                    <tr key={index}>
                                        <td>
                                            <a
                                                href={
                                                    currentItineraryNumber
                                                        ? row?.edit_url
                                                        : "#"
                                                }
                                            >
                                                {row.confirm_number ?? "-"}
                                            </a>
                                        </td>
                                        <td>{row.title ?? "-"}</td>
                                        <td>
                                            {row.document_address?.name ?? "-"}
                                        </td>
                                        <td>
                                            ￥
                                            {(
                                                row.amount_total ?? 0
                                            ).toLocaleString()}
                                        </td>
                                        <td>{row.issue_date ?? "-"}</td>
                                        <td className="txtalc">
                                            {row.pdf?.id ? (
                                                <a
                                                    href={`/${agencyAccount}/pdf/document/quote/${row.pdf.id}`}
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
                                            {/**デフォルトテンプレートは削除不可 */}
                                            {!row.is_nondelete ? (
                                                <span
                                                    className={classNames(
                                                        "material-icons",
                                                        {
                                                            "js-modal-open": !isDeleting
                                                        }
                                                    )}
                                                    data-target="mdConfirmDeleteCard"
                                                    onClick={e =>
                                                        handleModalDelete(
                                                            e,
                                                            row?.confirm_number
                                                        )
                                                    }
                                                >
                                                    delete
                                                </span>
                                            ) : (
                                                "-"
                                            )}
                                        </td>
                                    </tr>
                                ))}
                        </tbody>
                    </table>
                </div>
            </div>
            <SmallDangerModal
                id="mdConfirmDeleteCard"
                title="この帳票を削除しますか？"
                handleAction={handleDelete}
                isActioning={isDeleting}
            />
        </>
    );
};

export default DocumentArea;
