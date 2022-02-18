import React, { useState, useEffect, useReducer, useContext } from "react";
import { ConstContext } from "./components/ConstApp";
import ConstApp from "./components/ConstApp";
import { render } from "react-dom";
import PageNation from "./components/PageNation";
import DeclineMessage from "./components/DeclineMessage";
import { useMountedRef } from "../../hooks/useMountedRef";
import ReactLoading from "react-loading";
import classNames from "classnames";
import _ from "lodash";
import moment from "moment";
import { MANAGEMENT_INVOICE_BREAKDOWN } from "./actions"; //action名
import DepositModal from "./components/ManagementInvoiceBreakdown/DepositModal";
import DepositBatchModal from "./components/ManagementInvoiceBreakdown/DepositBatchModal";
import InvoiceStatus from "./components/InvoiceStatus";

/**
 * 請求管理一覧ページ
 *
 * @param {*} param0
 * @returns
 */
const BreakdownList = ({
    formSelects,
    modalFormSelects,
    consts,
    customFields,
    customCategoryCode,
    reserveBundleInvoiceId
}) => {
    const { agencyAccount } = useContext(ConstContext);

    const mounted = useMountedRef(); // マウント・アンマウント制御

    // ページャー関連変数
    const [page, setPage] = useState(1);
    const [lastPage, setLastPage] = useState(0);
    const [sort, setSort] = useState("created_at");
    const [direction, setDirection] = useState("desc");
    const [total, setTotal] = useState(0);
    const perPage = 10; // 1ページ表示件数

    const [isLoading, setIsLoading] = useState(false); // 重複読み込み防止
    const [isRegisting, setIsRegisting] = useState(false); // 入金登録処理中か否か
    const [isDepositDeleting, setIsDepositDeleting] = useState(false); // 入金データ削除処理中か否か
    const [isBatching, setIsBatching] = useState(false); // 未払い額の一括処理中か否か

    const [declineMessage, setDeclineMessage] = useState("");

    const [sortParam, setSortParam] = useState({
        created_at: "desc"
    });

    // 入金登録モーダル関連処理
    const depositInitial = {
        amount: 0,
        // withdrawal_method: withdrawalModalDefaultValue.withdrawalDefault,
        // manager_id: withdrawalModalDefaultValue.managerId,
        deposit_date: moment().format("YYYY/MM/DD"),
        record_date: moment().format("YYYY/MM/DD")
        // note: ""
    };

    // 一括入金登録モーダル関連処理
    const depositBatchInitial = {
        deposit_date: moment().format("YYYY/MM/DD"),
        record_date: moment().format("YYYY/MM/DD"),
        manager_id: consts?.managerId, // 作業中のスタッフIDで初期化
        note: ""
    };

    // 入金登録関連Reducer
    const dataReducer = (state, action) => {
        const copyState = _.cloneDeep(state);

        if (
            action.type === MANAGEMENT_INVOICE_BREAKDOWN.SET_INVOICE_LISTS ||
            action.type === MANAGEMENT_INVOICE_BREAKDOWN.DEPOSIT_BATCED
        ) {
            // 支払一覧データをセット。リスト取得時・一括入金完了後処理
            return {
                invoiceLists: [...action.payload],
                doneLists: [],
                currentInvoiceData: {},
                depositData: {},
                depositBatchData: {}
            };
        } else if (action.type === MANAGEMENT_INVOICE_BREAKDOWN.INIT_DEPOSIT) {
            // 未入金額押下時。対象レコードを初期化

            const invoiceData = copyState["invoiceLists"].find(
                row => row.id == action.payload
            );
            return {
                ...copyState,
                currentInvoiceData: {
                    ...invoiceData
                },
                // 入力制御データの担当者と備考は、前回の入力をそのまま初期値として使う形で良いと思う
                depositData: {
                    ...depositInitial,
                    manager_id: invoiceData.manager_id ?? "",
                    note: invoiceData.note ?? ""
                }
            };
        } else if (
            action.type === MANAGEMENT_INVOICE_BREAKDOWN.INIT_DEPOSITBATCH
        ) {
            // 一括入金ボタン押下時。入力フィールドを初期化
            return {
                ...copyState,
                depositBatchData: {
                    ...depositBatchInitial
                }
            };
        } else if (
            action.type === MANAGEMENT_INVOICE_BREAKDOWN.CHANGE_DEPOSIT_INPUT
        ) {
            // 入金モーダル入力変更
            const depositData = copyState.depositData;
            depositData[action.payload.name] = action.payload.value;
            return { ...copyState };
        } else if (
            action.type ===
            MANAGEMENT_INVOICE_BREAKDOWN.CHANGE_DEPOSITBATCH_INPUT
        ) {
            // 一括入金モーダル入力変更
            const depositBatchData = copyState.depositBatchData;
            depositBatchData[action.payload.name] = action.payload.value;
            return { ...copyState };
        } else if (
            action.type === MANAGEMENT_INVOICE_BREAKDOWN.CHANGE_DONE_CHECK
        ) {
            const doneLists = copyState.doneLists;
            if (_.indexOf(doneLists, action.payload) !== -1) {
                // check済なので値除去
                _.pull(doneLists, action.payload);
            } else {
                // 未checkなので値追加
                doneLists.push(action.payload);
            }
            return { ...copyState };
        } else if (
            action.type === MANAGEMENT_INVOICE_BREAKDOWN.DEPOSIT_REGISTED
        ) {
            // 入金登録処理
            const index = _.findIndex(copyState.invoiceLists, function(o) {
                return o.id == action.payload.id;
            });
            if (index !== -1) {
                copyState.invoiceLists[index] = action.payload; // 更新された請求データをセット
                return {
                    invoiceLists: copyState.invoiceLists,
                    doneLists: [],
                    currentInvoiceData: {},
                    depositData: {},
                    depositBatchData: {}
                };
            } else {
                return { ...copyState };
            }
        } else if (
            action.type === MANAGEMENT_INVOICE_BREAKDOWN.DEPOSIT_DELETED
        ) {
            const reserveInvoiceId = action.payload.id; // 削除された入金データが属する請求レコードID
            const invoiceIndex = _.findIndex(copyState.invoiceLists, function(
                o
            ) {
                return o.id == reserveInvoiceId;
            });
            if (invoiceIndex !== -1) {
                //更新データで書き換え
                copyState.invoiceLists[invoiceIndex] = action.payload;
            }

            copyState.currentInvoiceData = action.payload;

            return {
                ...copyState
            };
        } else {
            return copyState;
        }
    };

    // 現在編集対象の入金情報等
    const [data, dataDispatch] = useReducer(dataReducer, {
        invoiceLists: [], // 請求リスト
        doneLists: [], // 「入金済みに反映」をチェックしたIDリスト
        currentInvoiceData: {}, // 現在編集対象の請求データ
        depositData: {}, // 入金Modal用入力データ
        depositBatchData: {} // 一括入金Modal用入力データ
    });

    const fetch = async () => {
        if (!mounted.current) return;
        if (isLoading) return;

        setIsLoading(true);

        const response = await axios
            .get(
                `/api/${agencyAccount}/management/bundle_invoice/${reserveBundleInvoiceId}/breakdown/list`,
                {
                    params: {
                        page: page,
                        sort: sort,
                        direction: direction,
                        per_page: perPage
                    }
                }
            )
            .finally(() => {
                if (mounted.current) {
                    setIsLoading(false);
                }
            });
        if (mounted.current && response?.data?.data) {
            dataDispatch({
                type: MANAGEMENT_INVOICE_BREAKDOWN.SET_INVOICE_LISTS,
                payload: response.data.data
            });
            // ページャー関連
            setPage(response.data.meta.current_page);
            setLastPage(response.data.meta.last_page);
            setTotal(response.data.meta.total);
        }
    };

    useEffect(() => {
        fetch(); // 一覧取得
    }, [page, sort, direction]);

    // ページリンクをクリックした挙動（ページネーションコンポーネント用）
    const handlePagerClick = (e, targetPage) => {
        e.preventDefault();
        setPage(targetPage);
    };

    // 並び替えリンクをクリックした挙動
    const handleSortClick = column => {
        const direction = sortParam[column] === "asc" ? "desc" : "asc";
        setDirection(direction);
        setSort(column);

        setSortParam({ ...sortParam, [column]: direction });
    };

    //未入金額をクリックしたときの挙動
    const handleNotDepositedClick = id => {
        dataDispatch({
            type: MANAGEMENT_INVOICE_BREAKDOWN.INIT_DEPOSIT,
            payload: id
        });
    };

    // 入金金額の一括処理ボタンクリック時
    const handleDepositBatchClick = e => {
        dataDispatch({
            type: MANAGEMENT_INVOICE_BREAKDOWN.INIT_DEPOSITBATCH
        });
    };

    return (
        <>
            <DeclineMessage message={declineMessage} />
            <div className="tableWrap dragTable">
                <div className="tableCont managemnetTable">
                    <table>
                        <thead>
                            <tr>
                                <th className="txtalc">&nbsp;</th>
                                <th
                                    className="sort"
                                    onClick={e =>
                                        handleSortClick(
                                            "reserve.control_number"
                                        )
                                    }
                                >
                                    <span>予約番号</span>
                                </th>
                                <th className="txtalc">
                                    <span>ステータス</span>
                                </th>
                                <th
                                    className="sort"
                                    onClick={e =>
                                        handleSortClick("applicant_name")
                                    }
                                >
                                    <span>予約申込者</span>
                                </th>
                                <th
                                    className="sort"
                                    onClick={e =>
                                        handleSortClick("billing_address_name")
                                    }
                                >
                                    <span>請求先名</span>
                                </th>
                                <th
                                    className="sort txtalc"
                                    onClick={e =>
                                        handleSortClick("amount_total")
                                    }
                                >
                                    <span>請求金額</span>
                                </th>
                                <th
                                    className="sort txtalc"
                                    onClick={e =>
                                        handleSortClick("not_deposit_amount")
                                    }
                                >
                                    <span>未入金額</span>
                                </th>
                                <th
                                    className="sort txtalc"
                                    onClick={e =>
                                        handleSortClick("deposit_amount")
                                    }
                                >
                                    <span>入金済額</span>
                                </th>
                                <th
                                    className="sort txtalc"
                                    onClick={e => handleSortClick("issue_date")}
                                >
                                    <span>発行日</span>
                                </th>
                                <th
                                    className="sort txtalc"
                                    onClick={e =>
                                        handleSortClick("payment_deadline")
                                    }
                                >
                                    <span>支払期限</span>
                                </th>
                                <th
                                    className="sort txtalc"
                                    onClick={e =>
                                        handleSortClick("departure_date")
                                    }
                                >
                                    <span>出発日</span>
                                </th>
                                <th
                                    className="sort"
                                    onClick={e =>
                                        handleSortClick("last_manager.name")
                                    }
                                >
                                    <span>自社担当</span>
                                </th>
                                <th className="txtalc">
                                    <span>請求書</span>
                                </th>
                                <th className="txtalc">
                                    <span>領収書</span>
                                </th>
                                <th
                                    className="sort"
                                    onClick={e => handleSortClick("last_note")}
                                >
                                    <span>備考</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {isLoading && (
                                <tr>
                                    <td colSpan={14}>
                                        <ReactLoading
                                            type={"bubbles"}
                                            color={"#dddddd"}
                                        />
                                    </td>
                                </tr>
                            )}
                            {!isLoading && data.invoiceLists.length === 0 && (
                                <tr>
                                    <td colSpan={14}>請求データはありません</td>
                                </tr>
                            )}
                            {!isLoading &&
                                data.invoiceLists.length > 0 &&
                                data.invoiceLists.map((row, index) => (
                                    <tr
                                        key={index}
                                        className={classNames({
                                            done:
                                                row.amount_total > 0 &&
                                                row.sum_not_deposit === 0
                                        })}
                                    >
                                        <td className="txtalc checkBox">
                                            {row.sum_not_deposit === 0 && (
                                                <>-</>
                                            )}
                                            {row.sum_not_deposit !== 0 && (
                                                <>
                                                    {/**バッチ処理中は一応押せないようにdisable化。checkbox
                                                     */}
                                                    <input
                                                        type="checkbox"
                                                        id={`done${index}`}
                                                        checked={
                                                            _.indexOf(
                                                                data.doneLists,
                                                                row.id
                                                            ) !== -1
                                                        }
                                                        onChange={e =>
                                                            dataDispatch({
                                                                type:
                                                                    MANAGEMENT_INVOICE_BREAKDOWN.CHANGE_DONE_CHECK,
                                                                payload: row.id
                                                            })
                                                        }
                                                        disabled={isBatching}
                                                    />
                                                    <label
                                                        htmlFor={`done${index}`}
                                                    >
                                                        &nbsp;
                                                    </label>
                                                </>
                                            )}
                                        </td>
                                        <td>
                                            <a href={row.reserve_url ?? ""}>
                                                {row.reserve.control_number ??
                                                    "-"}
                                            </a>
                                        </td>
                                        <td className="txtalc">
                                            <InvoiceStatus
                                                isDeposited={
                                                    row.amount_total > 0 &&
                                                    row.sum_not_deposit === 0
                                                }
                                                status={row.status}
                                                status_label={row.status_label}
                                                statusVals={consts.statusVals}
                                            />
                                        </td>
                                        <td>{row.applicant_name ?? "-"}</td>
                                        <td>
                                            {row.billing_address_name ?? "-"}
                                        </td>
                                        <td className="txtalc">
                                            ￥
                                            {(
                                                row.amount_total ?? 0
                                            ).toLocaleString()}
                                        </td>
                                        <td className="txtalc">
                                            <span
                                                className={classNames(
                                                    "payPeriod blue",
                                                    {
                                                        "js-modal-open": !isRegisting
                                                    }
                                                )}
                                                data-target="mdDeposit"
                                                onClick={e =>
                                                    handleNotDepositedClick(
                                                        row.id
                                                    )
                                                }
                                            >
                                                ￥
                                                {(
                                                    row.sum_not_deposit ?? 0
                                                ).toLocaleString()}
                                            </span>
                                        </td>
                                        <td className="txtalc">
                                            ￥
                                            {(
                                                row.sum_deposit ?? 0
                                            ).toLocaleString()}
                                        </td>
                                        <td>{row.issue_date ?? "-"}</td>
                                        <td className="txtalc">
                                            <span className="payPeriod">
                                                {row.payment_deadline ?? "-"}
                                            </span>
                                        </td>
                                        <td>{row.departure_date ?? "-"}</td>
                                        <td>
                                            {formSelects.staffs?.[
                                                row.manager_id
                                            ] ?? "-"}
                                        </td>
                                        <td className="txtalc">
                                            <a href={row.invoice_url ?? ""}>
                                                <span className="material-icons">
                                                    description
                                                </span>
                                            </a>
                                        </td>
                                        <td className="txtalc">
                                            <a href={row.receipt_url ?? ""}>
                                                <span className="material-icons">
                                                    description
                                                </span>
                                            </a>
                                        </td>

                                        <td>{row.note ?? "-"}</td>
                                    </tr>
                                ))}
                        </tbody>
                    </table>
                    <div className="allCheck">
                        <button
                            className={classNames("blueBtn", {
                                "js-modal-open": data.doneLists.length > 0
                            })}
                            data-target="mdAllCheck"
                            disabled={data.doneLists.length === 0}
                            onClick={handleDepositBatchClick}
                        >
                            チェックした項目の未入金額を入金済みに反映する
                        </button>
                    </div>
                    {lastPage > 1 && (
                        <PageNation
                            currentPage={page}
                            lastPage={lastPage}
                            onClick={handlePagerClick}
                        />
                    )}
                </div>
            </div>
            {/**入金登録 */}
            <DepositModal
                id="mdDeposit"
                data={data}
                dataDispatch={dataDispatch}
                staffs={modalFormSelects.staffs}
                isRegisting={isRegisting}
                setIsRegisting={setIsRegisting}
                isDepositDeleting={isDepositDeleting}
                setIsDepositDeleting={setIsDepositDeleting}
                depositMethodKey={consts?.depositMethodKey}
                customFields={customFields}
                customFieldPositions={consts?.customFieldPositions}
                customFieldCodes={consts?.customFieldCodes}
                customCategoryCode={customCategoryCode}
                listTypes={consts?.listTypes}
            />
            {/** 一括入金処理モーダル */}
            <DepositBatchModal
                id="mdAllCheck"
                data={data}
                dataDispatch={dataDispatch}
                staffs={modalFormSelects.staffs}
                isProcessing={isBatching}
                setIsProcessing={setIsBatching}
                reserveBundleInvoiceId={reserveBundleInvoiceId}
                customFields={customFields}
                customFieldPositions={consts?.customFieldPositions}
                customFieldCodes={consts?.customFieldCodes}
                customCategoryCode={customCategoryCode}
                listTypes={consts?.listTypes}
                pageParam={{
                    page,
                    sort,
                    direction,
                    per_page: perPage
                }}
            />
        </>
    );
};

const Element = document.getElementById("breakdownList");
if (Element) {
    const jsVars = Element.getAttribute("jsVars");
    const parsedJsVars = jsVars && JSON.parse(jsVars);
    const customCategoryCode = Element.getAttribute("customCategoryCode");
    const formSelects = Element.getAttribute("formSelects");
    const parsedFormSelects = formSelects && JSON.parse(formSelects);
    const modalFormSelects = Element.getAttribute("modalFormSelects");
    const parsedModalFormSelects =
        modalFormSelects && JSON.parse(modalFormSelects);
    const consts = Element.getAttribute("consts");
    const parsedConsts = consts && JSON.parse(consts);
    const customFields = Element.getAttribute("customFields");
    const parsedCustomFields = customFields && JSON.parse(customFields);
    const reserveBundleInvoiceId = Element.getAttribute(
        "reserveBundleInvoiceId"
    );

    render(
        <ConstApp jsVars={parsedJsVars}>
            <BreakdownList
                customCategoryCode={customCategoryCode}
                formSelects={parsedFormSelects}
                modalFormSelects={parsedModalFormSelects}
                consts={parsedConsts}
                customFields={parsedCustomFields}
                reserveBundleInvoiceId={reserveBundleInvoiceId}
            />
        </ConstApp>,
        document.getElementById("breakdownList")
    );
}
