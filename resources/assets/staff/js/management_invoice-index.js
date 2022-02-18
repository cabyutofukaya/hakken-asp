import React, { useState, useEffect, useReducer, useContext } from "react";
import { ConstContext } from "./components/ConstApp";
import ConstApp from "./components/ConstApp";
import { render } from "react-dom";
import PageNation from "./components/PageNation";
import InvoiceStatus from "./components/InvoiceStatus";
import DeclineMessage from "./components/DeclineMessage";
import { useMountedRef } from "../../hooks/useMountedRef";
import ReactLoading from "react-loading";
import classNames from "classnames";
import _ from "lodash";
import moment from "moment";
import { MANAGEMENT_INVOICE } from "./actions"; //action名
import DepositModal from "./components/ManagementInvoice/DepositModal";
import DepositBatchModal from "./components/ManagementInvoice/DepositBatchModal";

// 一括請求行
const BundleRow = ({
    agencyAccount,
    row,
    statusVals,
    formSelects,
    handleNotDepositedClick,
    isRegisting
}) => {
    return (
        <tr
            className={classNames({
                done:
                    row.not_deposit_amount === 0 &&
                    row.combination_deposits.length > 0
            })}
        >
            <td className="txtalc checkBox">-</td>
            <td>-</td>
            <td className="txtalc">
                <InvoiceStatus
                    isDeposited={
                        row.not_deposit_amount === 0 &&
                        row.combination_deposits.length > 0
                    }
                    status={row.status}
                    status_label={row.status_label}
                    statusVals={statusVals}
                />
            </td>
            <td>&nbsp;</td>
            <td>
                {row.billing_address_name ?? "-"}
                <a
                    href={`/${agencyAccount}/management/bundle_invoice/${row?.bundle_id}/breakdown`}
                >
                    [一括請求]
                </a>
            </td>
            <td className="txtalc">
                <a
                    href={`/${agencyAccount}/management/bundle_invoice/${row?.bundle_id}/breakdown`}
                >
                    <span className="material-icons">list</span>
                </a>
            </td>
            <td className="txtalc">
                ￥{(row.amount_total ?? 0).toLocaleString()}
            </td>
            <td className="txtalc">
                <span
                    className={classNames("payPeriod blue", {
                        "js-modal-open": !isRegisting
                    })}
                    data-target="mdDeposit"
                    onClick={e => handleNotDepositedClick(row.id)}
                >
                    ￥{(row.not_deposit_amount ?? 0).toLocaleString()}
                </span>
            </td>
            <td className="txtalc">
                ￥{(row.deposit_amount ?? 0).toLocaleString()}
            </td>
            <td>{row.issue_date ?? "-"}</td>
            <td className="txtalc">
                <span className="payPeriod">{row.payment_deadline ?? "-"}</span>
            </td>
            <td>{row.departure_date ?? "-"}</td>
            <td>{formSelects.staffs?.[row.manager_id] ?? "-"}</td>
            <td className="txtalc">
                <a
                    href={`/${agencyAccount}/management/bundle_invoice/${row?.bundle_id}`}
                >
                    <span className="material-icons">description</span>
                </a>
            </td>
            <td className="txtalc">
                <a
                    href={`/${agencyAccount}/management/bundle_receipt/${row?.bundle_id}`}
                >
                    <span className="material-icons">description</span>
                </a>
            </td>
            <td>{row.note ?? "-"}</td>
        </tr>
    );
};

// 通常請求行
const NormalRow = ({
    row,
    statusVals,
    doneLists,
    formSelects,
    handleNotDepositedClick,
    dataDispatch,
    isRegisting,
    isBatching
}) => {
    return (
        <tr
            className={classNames({
                done:
                    row.not_deposit_amount === 0 &&
                    row.combination_deposits.length > 0
            })}
        >
            <td className="txtalc checkBox">
                {row.not_deposit_amount === 0 && <>-</>}
                {row.not_deposit_amount !== 0 && (
                    <>
                        {/**バッチ処理中は一応押せないようにdisable化。checkbox
                         */}
                        <input
                            type="checkbox"
                            id={`done${row.id}`}
                            checked={
                                _.indexOf(doneLists, row.reserve_invoice_id) !==
                                -1
                            }
                            onChange={e =>
                                dataDispatch({
                                    type: MANAGEMENT_INVOICE.CHANGE_DONE_CHECK,
                                    payload: row.reserve_invoice_id // 請求idをセット。「row.id」は請求IDと一括請求IDを判別する値が入ったDatabaseの純粋なIDではない点に注意
                                })
                            }
                            disabled={isBatching}
                        />
                        <label htmlFor={`done${row.id}`}>&nbsp;</label>
                    </>
                )}
            </td>
            <td>
                <a href={row?.reserve_url ?? ""}>
                    {row.reserve.control_number ?? "-"}
                </a>
            </td>
            <td className="txtalc">
                <InvoiceStatus
                    isDeposited={
                        row.not_deposit_amount === 0 &&
                        row.combination_deposits.length > 0
                    }
                    status={row.status}
                    status_label={row.status_label}
                    statusVals={statusVals}
                />
            </td>
            <td>{row.applicant_name ?? "-"}</td>
            <td>{row.billing_address_name ?? "-"}</td>
            <td className="txtalc">-</td>
            <td className="txtalc">
                ￥{(row.amount_total ?? 0).toLocaleString()}
            </td>
            <td className="txtalc">
                <span
                    className={classNames("payPeriod blue", {
                        "js-modal-open": !isRegisting
                    })}
                    data-target="mdDeposit"
                    onClick={e => handleNotDepositedClick(row.id)}
                >
                    ￥{(row.not_deposit_amount ?? 0).toLocaleString()}
                </span>
            </td>
            <td className="txtalc">
                ￥{(row.deposit_amount ?? 0).toLocaleString()}
            </td>
            <td>{row.issue_date ?? "-"}</td>
            <td className="txtalc">
                <span className="payPeriod">{row.payment_deadline ?? "-"}</span>
            </td>
            <td>{row.departure_date ?? "-"}</td>
            <td>{formSelects.staffs?.[row.manager_id] ?? "-"}</td>
            <td className="txtalc">
                <a href={row?.invoice_url ?? ""}>
                    <span className="material-icons">description</span>
                </a>
            </td>
            <td className="txtalc">
                <a href={row?.receipt_url ?? ""}>
                    <span className="material-icons">description</span>
                </a>
            </td>
            <td>{row.note ?? "-"}</td>
        </tr>
    );
};

/**
 * 請求管理一覧ページ
 *
 * @param {*} param0
 * @returns
 */
const InvoiceList = ({
    searchParam,
    formSelects,
    modalFormSelects,
    consts,
    customFields,
    customCategoryCode
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
    const [isRegisting, setIsRegisting] = useState(false); // 出金登録処理中か否か
    const [isDepositDeleting, setIsDepositDeleting] = useState(false); // 入金データ削除処理中か否か
    const [isBatching, setIsBatching] = useState(false); // 未払い額の一括処理中か否か

    const [declineMessage, setDeclineMessage] = useState("");

    const [sortParam, setSortParam] = useState({
        created_at: "desc",
        "reserve.control_number": "desc",
        applicant_name: "desc",
        billing_address_name: "desc",
        is_pay_altogether: "desc",
        amount_total: "desc",
        deposit_amount: "desc",
        not_deposit_amount: "desc",
        issue_date: "desc",
        payment_deadline: "desc",
        departure_date: "desc",
        "last_manager.name": "desc",
        last_note: "desc"
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
            action.type === MANAGEMENT_INVOICE.SET_INVOICE_LISTS ||
            action.type === MANAGEMENT_INVOICE.DEPOSIT_BATCED
        ) {
            // 支払一覧データをセット。リスト取得時・一括入金完了後処理
            return {
                invoiceLists: [...action.payload],
                doneLists: [],
                currentInvoiceData: {},
                depositData: {},
                depositBatchData: {}
            };
        } else if (action.type === MANAGEMENT_INVOICE.INIT_DEPOSIT) {
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
                    is_pay_altogether: invoiceData.is_pay_altogether ?? null, // 一括請求の場合はtrue
                    reserve_invoice_id: invoiceData.reserve_invoice_id ?? null, // 通常請求ID
                    reserve_bundle_invoice_id:
                        invoiceData.reserve_bundle_invoice_id ?? null, // 一括請求ID
                    manager_id: invoiceData.manager_id ?? "",
                    note: invoiceData.note ?? ""
                }
            };
        } else if (action.type === MANAGEMENT_INVOICE.INIT_DEPOSITBATCH) {
            // 一括入金ボタン押下時。入力フィールドを初期化
            return {
                ...copyState,
                depositBatchData: {
                    ...depositBatchInitial
                }
            };
        } else if (action.type === MANAGEMENT_INVOICE.CHANGE_DEPOSIT_INPUT) {
            // 入金モーダル入力変更
            const depositData = copyState.depositData;
            depositData[action.payload.name] = action.payload.value;
            return { ...copyState };
        } else if (
            action.type === MANAGEMENT_INVOICE.CHANGE_DEPOSITBATCH_INPUT
        ) {
            // 一括入金モーダル入力変更
            const depositBatchData = copyState.depositBatchData;
            depositBatchData[action.payload.name] = action.payload.value;
            return { ...copyState };
        } else if (action.type === MANAGEMENT_INVOICE.CHANGE_DONE_CHECK) {
            const doneLists = copyState.doneLists;
            if (_.indexOf(doneLists, action.payload) !== -1) {
                // check済なので値除去
                _.pull(doneLists, action.payload);
            } else {
                // 未checkなので値追加
                doneLists.push(action.payload);
            }
            return { ...copyState };
        } else if (action.type === MANAGEMENT_INVOICE.DEPOSIT_REGISTED) {
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
        } else if (action.type === MANAGEMENT_INVOICE.DEPOSIT_DELETED) {
            const reserveInvoiceId = action.payload.id; // 削除された入金データが属する請求レコードID(v_reserve_invoices_id)
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
        invoiceLists: [], // 請求リスト(v_reserve_invoicesテーブル)
        doneLists: [], // 「入金済みに反映」をチェックしたIDリスト。reserve_invoice_idがセットされている行が対象。IDの接頭辞が「ri」。一括請求行(rbi)はチェック不可
        currentInvoiceData: {}, // 現在編集対象の請求データ
        depositData: {}, // 入金Modal用入力データ
        depositBatchData: {} // 一括入金Modal用入力データ
    });

    const fetch = async () => {
        if (!mounted.current) return;
        if (isLoading) return;

        setIsLoading(true);

        const response = await axios
            .get(`/api/${agencyAccount}/management/invoice/list`, {
                params: {
                    ...searchParam,
                    page: page,
                    sort: sort,
                    direction: direction,
                    per_page: perPage
                }
            })
            .finally(() => {
                if (mounted.current) {
                    setIsLoading(false);
                }
            });
        if (mounted.current && response?.data?.data) {
            dataDispatch({
                type: MANAGEMENT_INVOICE.SET_INVOICE_LISTS,
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
            type: MANAGEMENT_INVOICE.INIT_DEPOSIT,
            payload: id
        });
    };

    // 入金金額の一括処理ボタンクリック時
    const handleDepositBatchClick = e => {
        dataDispatch({
            type: MANAGEMENT_INVOICE.INIT_DEPOSITBATCH
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
                                        handleSortClick("is_pay_altogether")
                                    }
                                >
                                    <span>一括内訳</span>
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
                                    <td colSpan={15}>
                                        <ReactLoading
                                            type={"bubbles"}
                                            color={"#dddddd"}
                                        />
                                    </td>
                                </tr>
                            )}
                            {!isLoading && data.invoiceLists.length === 0 && (
                                <tr>
                                    <td colSpan={15}>請求データはありません</td>
                                </tr>
                            )}
                            {!isLoading &&
                                data.invoiceLists.length > 0 &&
                                data.invoiceLists.map((row, index) => (
                                    <React.Fragment key={index}>
                                        {row.is_pay_altogether == 1 && (
                                            <BundleRow
                                                agencyAccount={agencyAccount}
                                                row={row}
                                                statusVals={consts.statusVals}
                                                formSelects={formSelects}
                                                handleNotDepositedClick={
                                                    handleNotDepositedClick
                                                }
                                                isRegisting={isRegisting}
                                            />
                                        )}
                                        {row.is_pay_altogether != 1 && (
                                            <NormalRow
                                                agencyAccount={agencyAccount}
                                                row={row}
                                                doneLists={data.doneLists}
                                                statusVals={consts.statusVals}
                                                formSelects={formSelects}
                                                handleNotDepositedClick={
                                                    handleNotDepositedClick
                                                }
                                                dataDispatch={dataDispatch}
                                                isRegisting={isRegisting}
                                                isBatching={isBatching}
                                            />
                                        )}
                                    </React.Fragment>
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
            <DepositBatchModal
                id="mdAllCheck"
                data={data}
                dataDispatch={dataDispatch}
                staffs={modalFormSelects.staffs}
                isProcessing={isBatching}
                setIsProcessing={setIsBatching}
                customFields={customFields}
                customFieldPositions={consts?.customFieldPositions}
                customFieldCodes={consts?.customFieldCodes}
                customCategoryCode={customCategoryCode}
                listTypes={consts?.listTypes}
                searchParam={searchParam}
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

const Element = document.getElementById("invoiceList");
if (Element) {
    const jsVars = Element.getAttribute("jsVars");
    const parsedJsVars = jsVars && JSON.parse(jsVars);
    const customCategoryCode = Element.getAttribute("customCategoryCode");
    const searchParam = Element.getAttribute("searchParam");
    const parsedSearchParam = searchParam && JSON.parse(searchParam);
    const formSelects = Element.getAttribute("formSelects");
    const parsedFormSelects = formSelects && JSON.parse(formSelects);
    const modalFormSelects = Element.getAttribute("modalFormSelects");
    const parsedModalFormSelects =
        modalFormSelects && JSON.parse(modalFormSelects);
    const consts = Element.getAttribute("consts");
    const parsedConsts = consts && JSON.parse(consts);
    const customFields = Element.getAttribute("customFields");
    const parsedCustomFields = customFields && JSON.parse(customFields);

    render(
        <ConstApp jsVars={parsedJsVars}>
            <InvoiceList
                customCategoryCode={customCategoryCode}
                searchParam={parsedSearchParam}
                formSelects={parsedFormSelects}
                modalFormSelects={parsedModalFormSelects}
                consts={parsedConsts}
                customFields={parsedCustomFields}
            />
        </ConstApp>,
        document.getElementById("invoiceList")
    );
}
