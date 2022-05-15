import React, { useState, useEffect, useReducer, useContext } from "react";
import { ConstContext } from "./components/ConstApp";
import ConstApp from "./components/ConstApp";
import { render } from "react-dom";
import PageNation from "./components/PageNation";
import DeclineMessage from "./components/DeclineMessage";
import { useMountedRef } from "../../hooks/useMountedRef";
import ReactLoading from "react-loading";
import PaymentModal from "./components/ManagementPaymentItem/PaymentModal";
import classNames from "classnames";
import _ from "lodash";
import moment from "moment";
import PaymentDateModal from "./components/ManagementPaymentItem/PaymentDateModal";
import { MANAGEMENT_PAYMENT_ITEM } from "./actions"; //action名
import { RESERVE } from "./constants";

/**
 * 一括出金処理も書かれているが本ページでは未使用。
 * 実装する際は改めてコードを見直すこと
 *
 * @param {*} param0
 * @returns
 */
const PaymentList = ({
    reserveHashId,
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
    const [sort, setSort] = useState("item_code");
    const [direction, setDirection] = useState("asc");
    const [total, setTotal] = useState(0);
    const perPage = 10; // 1ページ表示件数

    const [isLoading, setIsLoading] = useState(false); // 重複読み込み防止
    const [isRegisting, setIsRegisting] = useState(false); // 出金登録処理中か否か
    const [isWithdrawalDeleting, setIsWithDrawalDeleting] = useState(false); // 出金データ削除処理中か否か
    const [isPaymentDateChanging, setIsPaymentDateChanging] = useState(false); // 支払日変更処理中か否か
    const [isBatching, setIsBatching] = useState(false); // 未払い額の一括処理中か否か

    const [declineMessage, setDeclineMessage] = useState("");

    const [sortParam, setSortParam] = useState({
        "reserve.control_number": "desc",
        supplier_name: "asc",
        item_code: "asc",
        item_name: "asc",
        "last_manager.name": "asc",
        last_note: "asc",
        payment_date: "asc",
        use_date: "asc",
        amount_billed: "asc",
        unpaid_balance: "asc",
        status: "asc"
    });

    // 出金登録モーダル関連処理
    const withdrawalInitial = {
        amount: 0,
        withdrawal_date: moment().format("YYYY/MM/DD"),
        record_date: moment().format("YYYY/MM/DD")
    };

    // // 一括出金登録モーダル関連処理
    // const withdrawalBatchInitial = {
    //     withdrawal_date: moment().format("YYYY/MM/DD"),
    //     record_date: moment().format("YYYY/MM/DD"),
    //     manager_id: consts?.managerId, // 作業中のスタッフIDで初期化
    //     note: ""
    // };

    // 出金登録関連Reducer
    const dataReducer = (state, action) => {
        const copyState = _.cloneDeep(state);

        if (
            action.type === MANAGEMENT_PAYMENT_ITEM.SET_PAYMENT_LISTS ||
            action.type === MANAGEMENT_PAYMENT_ITEM.PAYMENT_BATCED
        ) {
            // 支払一覧データをセット。リスト取得時・一括支払い完了後処理
            return {
                paymentLists: [...action.payload],
                doneLists: [],
                currentPaymentData: {},
                withdrawalData: {},
                withdrawalBatchData: {},
                paymentDateData: {}
            };
        } else if (action.type === MANAGEMENT_PAYMENT_ITEM.INIT_WITHDRAWAL) {
            // 未払金額押下時。対象レコードを初期化

            const paymentData = copyState["paymentLists"].find(
                row => row.id == action.payload
            );
            return {
                ...copyState,
                currentPaymentData: {
                    ...paymentData
                },
                // 入力制御データの担当者と備考は、前回の入力をそのまま初期値として使う形で良いと思う
                withdrawalData: {
                    ...withdrawalInitial,
                    manager_id: paymentData.manager_id ?? consts?.managerId,
                    note: paymentData.note ?? ""
                    // supplier_id_log: paymentData.supplier_id
                }
            };
        } else if (
            action.type === MANAGEMENT_PAYMENT_ITEM.INIT_WITHDRAWALBATCH
        ) {
            // 一括出金ボタン押下時。入力フィールドを初期化
            return {
                ...copyState,
                withdrawalBatchData: {
                    ...withdrawalBatchInitial
                }
            };
        } else if (action.type === MANAGEMENT_PAYMENT_ITEM.INIT_PAYMENTDATE) {
            // 支払日押下時

            const paymentData = copyState["paymentLists"].find(
                row => row.id == action.payload
            );
            return {
                ...copyState,
                currentPaymentData: {
                    ...paymentData
                },
                paymentDateData: { payment_date: paymentData.payment_date }
            };
        } else if (
            action.type === MANAGEMENT_PAYMENT_ITEM.CHANGE_WITHDRAWAL_INPUT
        ) {
            // 出金モーダル入力変更
            const withdrawalData = copyState.withdrawalData;
            withdrawalData[action.payload.name] = action.payload.value;
            return { ...copyState };
        } else if (
            action.type === MANAGEMENT_PAYMENT_ITEM.CHANGE_WITHDRAWALBATCH_INPUT
        ) {
            // 一括出金モーダル入力変更
            const withdrawalBatchData = copyState.withdrawalBatchData;
            withdrawalBatchData[action.payload.name] = action.payload.value;
            return { ...copyState };
        } else if (
            action.type === MANAGEMENT_PAYMENT_ITEM.CHANGE_PAYMENTDATA_INPUT
        ) {
            // 支払日モーダル入力変更
            const paymentDateData = copyState.paymentDateData;
            paymentDateData[action.payload.name] = action.payload.value;
            return { ...copyState };
        } else if (action.type === MANAGEMENT_PAYMENT_ITEM.CHANGE_DONE_CHECK) {
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
            action.type === MANAGEMENT_PAYMENT_ITEM.WITHDRAWAL_REGISTED ||
            action.type === MANAGEMENT_PAYMENT_ITEM.PAYMENTDATA_CHANGED
        ) {
            // 出金登録処理 or 支払日変更完了時処理
            const index = _.findIndex(copyState.paymentLists, function(o) {
                return o.id == action.payload.id;
            });
            if (index !== -1) {
                copyState.paymentLists[index] = action.payload; // 更新された支払いデータをセット
                return {
                    paymentLists: copyState.paymentLists,
                    doneLists: [],
                    currentPaymentData: {},
                    withdrawalData: {},
                    withdrawalBatchData: {},
                    paymentDateData: {}
                };
            } else {
                return { ...copyState };
            }
        } else if (action.type === MANAGEMENT_PAYMENT_ITEM.WITHDRAWAL_DELETED) {
            const accountPayableDetailId = action.payload.id; // 削除された出金データが属する支払詳細レコードID
            const paymentIndex = _.findIndex(copyState.paymentLists, function(
                o
            ) {
                return o.id == accountPayableDetailId;
            });
            if (paymentIndex !== -1) {
                //更新データで書き換え
                copyState.paymentLists[paymentIndex] = action.payload;
            }

            copyState.currentPaymentData = action.payload;

            return {
                ...copyState
            };
        } else {
            return copyState;
        }
    };

    // 現在編集対象の出金情報
    const [data, dataDispatch] = useReducer(dataReducer, {
        paymentLists: [], // 支払リスト
        doneLists: [], // 「支払い済みに反映」をチェックしたIDリスト → 本ページでは未使用
        currentPaymentData: {}, // 現在編集対象の支払いデータ
        withdrawalData: {}, // 出金Modal用入力データ
        withdrawalBatchData: {}, // 一括出金Modal用入力データ → 本ページでは未使用
        paymentDateData: {} // 支払日Modal用入力データ
    });

    const fetch = async () => {
        if (!mounted.current) return;
        if (isLoading) return;

        setIsLoading(true);

        const response = await axios
            .get(
                `/api/${agencyAccount}/management/payment/reserve/${reserveHashId}/item/list`,
                {
                    params: {
                        ...searchParam,
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
                type: MANAGEMENT_PAYMENT_ITEM.SET_PAYMENT_LISTS,
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

    //未払金をクリックしたときの挙動
    const handleUnpaidClick = id => {
        dataDispatch({
            type: MANAGEMENT_PAYMENT_ITEM.INIT_WITHDRAWAL,
            payload: id
        });
    };

    // 未払い金額の一括処理ボタンクリック時
    const handlePaymentBatchClick = e => {
        dataDispatch({
            type: MANAGEMENT_PAYMENT_ITEM.INIT_WITHDRAWALBATCH
        });
    };

    // 支払日をクリックした時の挙動
    const handlePaymentDateClick = id => {
        dataDispatch({
            type: MANAGEMENT_PAYMENT_ITEM.INIT_PAYMENTDATE,
            payload: id
        });
    };
    return (
        <>
            <div className="tableWrap dragTable">
                <DeclineMessage message={declineMessage} />
                <div className="tableCont managemnetTable">
                    <table>
                        <thead>
                            <tr>
                                <th
                                    className="sort"
                                    onClick={e => handleSortClick("item_code")}
                                >
                                    <span>商品コード</span>
                                </th>
                                <th
                                    className="sort"
                                    onClick={e => handleSortClick("item_name")}
                                >
                                    <span>商品名</span>
                                </th>
                                <th
                                    className="sort txtalc"
                                    onClick={e => handleSortClick("status")}
                                >
                                    <span>ステータス</span>
                                </th>
                                <th
                                    className="sort"
                                    onClick={e =>
                                        handleSortClick("supplier_name")
                                    }
                                >
                                    <span>仕入先</span>
                                </th>
                                <th
                                    className="sort txtalc"
                                    onClick={e =>
                                        handleSortClick("amount_billed")
                                    }
                                >
                                    <span>仕入額</span>
                                </th>
                                <th
                                    className="sort txtalc"
                                    onClick={e =>
                                        handleSortClick("unpaid_balance")
                                    }
                                >
                                    <span>未払金額</span>
                                </th>
                                <th
                                    className="sort txtalc"
                                    onClick={e =>
                                        handleSortClick("payment_date")
                                    }
                                >
                                    <span>支払予定日</span>
                                </th>
                                <th className="txtalc">
                                    <span>予約詳細</span>
                                </th>
                                <th
                                    className="sort"
                                    onClick={e =>
                                        handleSortClick("last_manager.name")
                                    }
                                >
                                    <span>自社担当</span>
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
                                    <td colSpan={10}>
                                        <ReactLoading
                                            type={"bubbles"}
                                            color={"#dddddd"}
                                        />
                                    </td>
                                </tr>
                            )}
                            {!isLoading && data.paymentLists.length === 0 && (
                                <tr>
                                    <td colSpan={10}>支払データはありません</td>
                                </tr>
                            )}
                            {!isLoading &&
                                data.paymentLists.length > 0 &&
                                data.paymentLists.map((row, index) => (
                                    <tr
                                        key={index}
                                        className={classNames({
                                            done:
                                                row?.status ==
                                                consts.statusVals.status_paid
                                        })}
                                    >
                                        <td>{row?.item_code ?? "-"}</td>
                                        <td>{row?.item_name ?? "-"}</td>
                                        <td className="txtalc">
                                            {row?.status_label && (
                                                <span
                                                    className={classNames(
                                                        "status",
                                                        {
                                                            red:
                                                                row.status ==
                                                                consts
                                                                    .statusVals
                                                                    .status_unpaid,
                                                            gray:
                                                                row.status ==
                                                                consts
                                                                    .statusVals
                                                                    .status_paid
                                                        }
                                                    )}
                                                >
                                                    {row.status_label}
                                                </span>
                                            )}
                                            {!row?.status_label && "-"}
                                        </td>
                                        <td>{row?.supplier_name ?? "-"}</td>
                                        <td className="txtalc">
                                            ￥
                                            {row.amount_billed.toLocaleString()}
                                        </td>
                                        <td className="txtalc">
                                            <span
                                                className={classNames({
                                                    red: row.unpaid_balance > 0,
                                                    payPeriod:
                                                        row.unpaid_balance > 0,
                                                    "js-modal-open": !isRegisting
                                                })}
                                                data-target="mdPayment"
                                                onClick={e =>
                                                    handleUnpaidClick(row.id)
                                                }
                                            >
                                                ￥
                                                {row.unpaid_balance.toLocaleString()}
                                            </span>
                                        </td>
                                        <td className="txtalc">
                                            <span
                                                className={classNames(
                                                    "payPeriod blue",
                                                    {
                                                        "js-modal-open": !isPaymentDateChanging
                                                    }
                                                )}
                                                data-target="mdEditPayday"
                                                onClick={e =>
                                                    handlePaymentDateClick(
                                                        row.id
                                                    )
                                                }
                                            >
                                                {row?.payment_date ?? "-"}
                                            </span>
                                        </td>
                                        <td className="txtalc">
                                            {!row.reserve?.is_deleted && (
                                                <a href={row.reserve_url ?? ""}>
                                                    <span className="material-icons">
                                                        event_note
                                                    </span>
                                                </a>
                                            )}
                                            {row.reserve?.is_deleted && "-"}
                                        </td>
                                        <td>
                                            {formSelects.staffs?.[
                                                row?.manager_id
                                            ] ?? "-"}
                                        </td>
                                        <td>{row?.note ?? "-"}</td>
                                    </tr>
                                ))}
                        </tbody>
                    </table>
                    {lastPage > 1 && (
                        <PageNation
                            currentPage={page}
                            lastPage={lastPage}
                            onClick={handlePagerClick}
                        />
                    )}
                </div>
            </div>
            {/**支払いモーダル */}
            <PaymentModal
                id="mdPayment"
                data={data}
                dataDispatch={dataDispatch}
                staffs={modalFormSelects.staffs}
                isRegisting={isRegisting}
                setIsRegisting={setIsRegisting}
                isWithdrawalDeleting={isWithdrawalDeleting}
                setIsWithDrawalDeleting={setIsWithDrawalDeleting}
                withdrawalMethodKey={consts?.withdrawalMethodKey}
                customFields={customFields}
                customFieldPositions={consts?.customFieldPositions}
                customFieldCodes={consts?.customFieldCodes}
                customCategoryCode={customCategoryCode}
            />
            {/** 支払予定日変更モーダル*/}
            <PaymentDateModal
                id="mdEditPayday"
                data={data}
                dataDispatch={dataDispatch}
                isProcessing={isPaymentDateChanging}
                setIsProcessing={setIsPaymentDateChanging}
            />
        </>
    );
};

const Element = document.getElementById("paymentList");
if (Element) {
    const reserveHashId = Element.getAttribute("reserveHashId");
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
            <PaymentList
                reserveHashId={reserveHashId}
                customCategoryCode={customCategoryCode}
                searchParam={parsedSearchParam}
                formSelects={parsedFormSelects}
                modalFormSelects={parsedModalFormSelects}
                consts={parsedConsts}
                customFields={parsedCustomFields}
            />
        </ConstApp>,
        document.getElementById("paymentList")
    );
}
