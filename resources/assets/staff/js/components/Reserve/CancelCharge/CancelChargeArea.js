import React, { useState, useReducer, useContext } from "react";
import { ConstContext } from "../../ConstApp";
import OnlyNumberInput from "../../OnlyNumberInput";
import ErrorMessage from "../../ErrorMessage";
import { isEmptyObject } from "../../../libs";
import classNames from "classnames";
import { useMountedRef } from "../../../../../hooks/useMountedRef";
import SubjectModal from "./SubjectModal";

// メインリストに関するアクション
const MAIN_ROW_ACTIONS = {
    CANCEL_CHECK: "MAIN_CANCEL_CHECK",
    INPUT_CHANGE: "MAIN_INPUT_CHANGE",
    SET_SUBJECT_INFO: "SET_SUBJECT_INFO",
    INPUT_FOCUS: "INPUT_FOCUS"
};
// 明細行に関するアクション
const DETAIL_ROW_ACTIONS = {
    INPUT_CHANGE: "DETAIL_INPUT_CHANGE",
    REGIST: "DETAIL_REGIST"
};

const PRICE_SETTING_PROPERTY = "price_setting"; // 一括設定の料金用データを保持するための一時プロパティ
export const SUBJECT_INFO_PROPERTY = "subject_info"; // 仕入情報を保持するための一時プロパティ

/**
 * 粗利を計算
 *
 * @param {*} row
 */
export const calcCancelProfit = row => {
    row["cancel_charge_profit"] =
        (parseInt(row.cancel_charge) || 0) -
        (parseInt(row.cancel_charge_net) || 0);
};

const initialPriceSetting = {
    ad_cancel_charge: 0,
    ad_cancel_charge_net: 0,
    ad_cancel_charge_profit: 0,
    ch_cancel_charge: 0,
    ch_cancel_charge_net: 0,
    ch_cancel_charge_profit: 0,
    inf_cancel_charge: 0,
    inf_cancel_charge_net: 0,
    inf_cancel_charge_profit: 0
};

/**
 *
 * @returns
 */
const CancelChargeArea = ({ defaultValue, consts, errors }) => {
    const mounted = useMountedRef(); // マウント・アンマウント制御

    const { agencyAccount, documentZeiKbns } = useContext(ConstContext);

    /**
     * メインリストの合計金額を計算してセット
     *
     * @param {*} row
     */
    const calcTotal = row => {
        let cancelCharge = 0;
        let cancelChargeNet = 0;
        let cancelChargeProfit = 0;

        for (const r of row.participants ?? []) {
            cancelCharge += parseInt(r.cancel_charge ?? 0, 10) || 0; // Nan判定の場合は0に
            cancelChargeNet += parseInt(r.cancel_charge_net ?? 0, 10) || 0; // Nan判定の場合は0に
            cancelChargeProfit +=
                parseInt(r.cancel_charge_profit ?? 0, 10) || 0; // Nan判定の場合は0に
        }

        row["cancel_charge"] = cancelCharge;
        row["cancel_charge_net"] = cancelChargeNet;
        row["cancel_charge_profit"] = cancelChargeProfit;
    };

    /**
     * 当該レコードの詳細金額を計算して値をセット
     */
    const calcDetail = row => {
        // 数量
        const quantity = row?.quantity ?? 0;
        // キャンセルチャージ合計
        const cancelCharge = row?.cancel_charge ?? 0;
        // キャンセルチャージ仕入合計
        const cancelChargeNet = row?.cancel_charge_net ?? 0;
        // 粗利合計
        const cancelChargeProfit = row?.cancel_charge_profit ?? 0;

        if (quantity) {
            // ひとまず小数点以下切り捨てで詳細単価を計算
            const chargeTanka = parseInt(cancelCharge / quantity, 10) || 0; // Nan判定の場合は0に
            const netTanka = parseInt(cancelChargeNet / quantity, 10) || 0; // Nan判定の場合は0に
            const profitTanka =
                parseInt(cancelChargeProfit / quantity, 10) || 0; // Nan判定の場合は0に

            for (const r of row.participants ?? []) {
                r["cancel_charge"] = chargeTanka;
                r["cancel_charge_net"] = netTanka;
                r["cancel_charge_profit"] = profitTanka;
            }
        }
    };

    const [currentPurchaseKey, setCurrentPurchaseKey] = useState(null); // 編集対象の仕入キー
    const [currentEditPurchase, setCurrentEditPurchase] = useState({});
    const [priceSetting, setPriceSetting] = useState({}); // 一括料金設定

    // メインリストに関するイベント処理
    const [lists, dispatch] = useReducer((state, action) => {
        let copyState = _.cloneDeep(state); // 下層プロパティを変更する処理が多いので一応、stateはディープコピーしたものを使用する

        const key = action?.payload?.key;
        const row = copyState[key];

        switch (action.type) {
            case MAIN_ROW_ACTIONS.CANCEL_CHECK: // キャンセル料の有無checkbox制御
                // チェックボックスの入力制御
                const val = row?.is_cancel; // 現在の値
                row["is_cancel"] = val == 1 ? 0 : 1;

                if (row["is_cancel"] == 0) {
                    // チェックoffの場合は料金クリア
                    row["cancel_charge"] = 0;
                    row["cancel_charge_net"] = 0;
                    row["cancel_charge_profit"] = 0;
                    calcDetail(row); // 詳細レコード計算
                }
                copyState[key] = row;
                return { ...copyState };
            case MAIN_ROW_ACTIONS.INPUT_FOCUS: // 金額入力フィールドfocus → キャンセル有無をON
                row["is_cancel"] = 1;
                copyState[key] = row;
                return { ...copyState };
            case MAIN_ROW_ACTIONS.INPUT_CHANGE: // 金額入力制御
                const name = action?.payload?.name;
                const value = parseInt(action?.payload?.value, 10) || 0; // Nan判定の場合は0に
                row[name] = value;
                calcCancelProfit(row); // 粗利計算
                calcDetail(row); // 詳細レコード計算
                copyState[key] = row;
                return { ...copyState };
            case MAIN_ROW_ACTIONS.SET_SUBJECT_INFO: // 最新の仕入情報取得
                const subjectData = action.payload.data;
                row[SUBJECT_INFO_PROPERTY] = { ...subjectData };
                copyState[key] = row;
                return { ...copyState };
            case DETAIL_ROW_ACTIONS.REGIST: // 明細の登録
                const copyTargetRow = _.cloneDeep(currentEditPurchase); // 仕入情報データ
                const priceSettingData = _.cloneDeep(priceSetting); // 料金一括設定用の入力データ

                calcTotal(copyTargetRow); // 合計を再計算
                copyState[currentPurchaseKey] = copyTargetRow;
                copyState[currentPurchaseKey][
                    PRICE_SETTING_PROPERTY
                ] = priceSettingData; // 料金の一括設定入力データをセット

                return { ...copyState };
            default:
                return copyState;
        }
    }, defaultValue["rows"]);

    const [errorObj, setErrorObj] = useState(errors); // エラー文言を保持

    const [cancelChargeErrors, setCancelChargeErrors] = useState([]); // キャンセルチャージでエラーがある枠のlists配列キー値を保持
    const [cancelChargeNetErrors, setCancelChargeNetErrors] = useState([]); // 仕入先支払料金合計でエラーがある枠のlists配列キー値を保持

    const [isSending, setIsSending] = useState(false); // データ送信中
    const [isSubjectInfoGetting, setIsSubjectInfoGetting] = useState(false); // 仕入情報取得中か否か

    // 金額入力制御(メインリスト)
    const handleChange = (key, name, value) => {
        dispatch({
            type: MAIN_ROW_ACTIONS.INPUT_CHANGE,
            payload: {
                key,
                name,
                value
            }
        });
    };

    // キャンセル料の有無checkbox制御
    const handleCancelCheck = key => {
        dispatch({
            type: MAIN_ROW_ACTIONS.CANCEL_CHECK,
            payload: {
                key
            }
        });
    };

    // 入力フィールドFOCUS
    const handleInputFocus = key => {
        dispatch({
            type: MAIN_ROW_ACTIONS.INPUT_FOCUS,
            payload: {
                key
            }
        });
    };

    // 仕入商品名クリック時（明細編集モーダル表示前処理）
    const handleEditModal = (e, key) => {
        e.preventDefault();

        setCurrentPurchaseKey(key); // 編集対象の仕入情報キーをセット

        let data = _.cloneDeep(lists[key]); //モーダル側で編集しても影響ないようにコピーデータを渡す
        setCurrentEditPurchase(data);

        // 仕入情報。設定データが保持されていなければ取得
        if (!_.has(data, SUBJECT_INFO_PROPERTY)) {
            getSubjectInfo(key, data?.subject, data?.code); // 最新データを取得
        }

        // 料金一括設定用の入力フィールド。設定データが保持されていなければ初期化。保持されていれば読み込み
        if (!_.has(data, PRICE_SETTING_PROPERTY)) {
            setPriceSetting({ ...initialPriceSetting }); // 料金編集フィールドを初期
        } else {
            setPriceSetting(data[PRICE_SETTING_PROPERTY]);
        }
    };

    /**
     * 明細行登録
     */
    const handleDetailRegist = () => {
        dispatch({
            type: DETAIL_ROW_ACTIONS.REGIST
        });
    };

    // 送信制御
    const handleSend = async e => {
        e.preventDefault();

        if (!mounted.current) return;
        if (isSending) return;

        let errMsg = {};

        let err = [];
        Object.keys(lists).map(key => {
            const cancelCharge = Number(lists[key].cancel_charge);
            const sumCancelCharge = _.sumBy(lists[key]?.participants, function(
                p
            ) {
                return parseInt(p?.cancel_charge, 10) || 0; // Nan判定の場合は0に
            }); // 明細モーダルのキャンセルチャージ合計

            if (cancelCharge != sumCancelCharge) {
                // エラーのあったlists配列のキー値を保存
                err = [...err, key];
            }
        });

        setCancelChargeErrors([...err]);
        if (err.length > 0) {
            errMsg["cancel_charge"] = [
                "「キャンセル料金合計」と詳細設定の合計が異なります。"
            ];
        }

        //////////////

        err = [];
        // 仕入先支払料金合計の入力値チェック → 仕入先支払料金合計が数量で割り切れない場合はエラーを出す
        Object.keys(lists).map(key => {
            const cancelChargeNet = Number(lists[key].cancel_charge_net);
            const sumCancelChargeNet = _.sumBy(
                lists[key]?.participants,
                function(p) {
                    return parseInt(p?.cancel_charge_net, 10) || 0; // Nan判定の場合は0に
                }
            ); // 明細モーダルのキャンセルチャージnet合計

            if (cancelChargeNet != sumCancelChargeNet) {
                // エラーのあったlists配列のキー値を保存
                err = [...err, key];
            }
        });

        setCancelChargeNetErrors([...err]);
        if (err.length > 0) {
            errMsg["cancel_charge_net"] = [
                "「仕入先支払料金合計」と詳細設定の合計が異なります。"
            ];
        }

        setErrorObj(errMsg);

        // エラーがなければform送信
        if (isEmptyObject(errMsg)) {
            setIsSending(true); // form送信中

            const params = {
                rows: { ...lists },
                reserve: { updated_at: defaultValue?.reserve?.updated_at },
                set_message: 1
            };

            const response = await axios
                .post(consts.cancelChargeUpdateUrl, params)
                .finally(() => {
                    setTimeout(function() {
                        if (mounted.current) {
                            setIsSending(false);
                        }
                    }, 1000); // 少し間を置く
                });

            if (mounted.current && response?.data?.result == "ok") {
                // 完了後の処理
                location.href = consts?.cancelChargeUpdateAfterUrl;
            } else {
                alert("キャンセルチャージ処理に失敗しました。");
            }
        }
    };

    // 「キャンセルせずに戻る」ボタン
    const handleBack = e => {
        e.preventDefault();
        location.href = consts?.reserveUrl;
    };

    /**
     * 最新の仕入情報取得API
     * @param {*} key 仕入行キー
     * @param {*} subjectCategory 仕入カテゴリ
     * @param {*} code 商品コード
     */
    const getSubjectInfo = async (key, subjectCategory, code) => {
        if (!mounted.current) return;
        if (isSubjectInfoGetting) return;

        setIsSubjectInfoGetting(true);

        const response = await axios
            .get(
                `/api/${agencyAccount}/subject/${subjectCategory}/code/${code}`
            )
            .finally(() => {
                if (mounted.current) {
                    setIsSubjectInfoGetting(false);
                }
            });

        if (mounted.current && response?.data?.data) {
            dispatch({
                type: MAIN_ROW_ACTIONS.SET_SUBJECT_INFO,
                payload: {
                    key,
                    data: response.data.data
                }
            });
        }
    };

    return (
        <>
            <ErrorMessage errorObj={errorObj} />

            <h2 className="subTit">
                <span className="material-icons"> subject </span>仕入れ先情報
            </h2>
            <div id="inputArea">
                <div className="tableWrap">
                    <div className="tableCont">
                        <table>
                            <thead>
                                <tr>
                                    <th className="txtalc wd10">
                                        <span>キャンセル料の有無</span>
                                    </th>
                                    <th>
                                        <span>商品名[詳細設定]</span>
                                    </th>
                                    <th className="txtalc">
                                        <span>数量</span>
                                    </th>
                                    <th>
                                        <span>キャンセル料金合計</span>
                                    </th>
                                    <th>
                                        <span>仕入先支払料金合計</span>
                                    </th>
                                    <th>
                                        <span>GRS単価</span>
                                    </th>
                                    <th>
                                        <span>仕入値</span>
                                    </th>
                                    <th>
                                        <span>手数料率</span>
                                    </th>
                                    <th>
                                        <span>NET単価</span>
                                    </th>
                                    <th className="txtalc">
                                        <span>税区分</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                {lists &&
                                    Object.keys(lists).map(key => {
                                        return (
                                            <tr
                                                key={key}
                                                className={classNames({
                                                    done: lists[key]?.valid == 0
                                                })}
                                            >
                                                <td className="txtalc checkBox">
                                                    <input
                                                        type="checkbox"
                                                        id={`cancel_${key}`}
                                                        onChange={e =>
                                                            handleCancelCheck(
                                                                key
                                                            )
                                                        }
                                                        checked={
                                                            lists[key]
                                                                ?.is_cancel == 1
                                                        }
                                                    />
                                                    <label
                                                        htmlFor={`cancel_${key}`}
                                                    >
                                                        &nbsp;
                                                    </label>
                                                </td>
                                                <td>
                                                    {/**キャンセル料チェックONならモーダルリンク */}
                                                    {lists[key]?.is_cancel ==
                                                        1 && (
                                                        <>
                                                            <a
                                                                href="#"
                                                                className="js-modal-open"
                                                                data-target="mdSubject"
                                                                onClick={e =>
                                                                    handleEditModal(
                                                                        e,
                                                                        key
                                                                    )
                                                                }
                                                            >
                                                                {lists[key]
                                                                    ?.name ??
                                                                    "-"}
                                                            </a>
                                                            {lists[key]
                                                                ?.supplier_name && (
                                                                <>
                                                                    (
                                                                    {
                                                                        lists[
                                                                            key
                                                                        ]
                                                                            .supplier_name
                                                                    }
                                                                    )
                                                                </>
                                                            )}
                                                        </>
                                                    )}
                                                    {lists[key]?.is_cancel !=
                                                        1 && (
                                                        <>
                                                            {lists[key]?.name ??
                                                                "-"}
                                                            {lists[key]
                                                                ?.supplier_name && (
                                                                <>
                                                                    (
                                                                    {
                                                                        lists[
                                                                            key
                                                                        ]
                                                                            .supplier_name
                                                                    }
                                                                    )
                                                                </>
                                                            )}
                                                        </>
                                                    )}
                                                </td>
                                                <td className="txtalc">
                                                    {lists[key]?.quantity ?? 0}
                                                </td>
                                                <td className="slimInput">
                                                    {/**キャンセルチャージoffの場合は0で初期化し金額入れられないようにreadonly*/}
                                                    <OnlyNumberInput
                                                        value={
                                                            lists[key]
                                                                ?.cancel_charge ??
                                                            0
                                                        }
                                                        negativeValuePermit={
                                                            false
                                                        }
                                                        handleChange={e =>
                                                            handleChange(
                                                                key,
                                                                "cancel_charge",
                                                                e.target.value
                                                            )
                                                        }
                                                        handleFocus={e =>
                                                            handleInputFocus(
                                                                key
                                                            )
                                                        }
                                                        className={
                                                            _.indexOf(
                                                                cancelChargeErrors,
                                                                key
                                                            ) !== -1
                                                                ? "error"
                                                                : ""
                                                        }
                                                        readOnly={
                                                            lists[key]
                                                                ?.is_cancel != 1
                                                        }
                                                    />
                                                </td>
                                                <td className="slimInput">
                                                    {/**キャンセルチャージoffの場合は0で初期化し金額入れられないようにreadonly*/}
                                                    <OnlyNumberInput
                                                        value={
                                                            lists[key]
                                                                ?.cancel_charge_net ??
                                                            0
                                                        }
                                                        negativeValuePermit={
                                                            false
                                                        }
                                                        handleChange={e =>
                                                            handleChange(
                                                                key,
                                                                "cancel_charge_net",
                                                                e.target.value
                                                            )
                                                        }
                                                        handleFocus={e =>
                                                            handleInputFocus(
                                                                key
                                                            )
                                                        }
                                                        className={
                                                            _.indexOf(
                                                                cancelChargeNetErrors,
                                                                key
                                                            ) !== -1
                                                                ? "error"
                                                                : ""
                                                        }
                                                        readOnly={
                                                            lists[key]
                                                                ?.is_cancel != 1
                                                        }
                                                    />
                                                </td>
                                                <td>
                                                    ￥
                                                    {(
                                                        lists[key]?.gross ?? 0
                                                    ).toLocaleString()}
                                                </td>
                                                <td>
                                                    ￥
                                                    {(
                                                        lists[key]?.cost ?? 0
                                                    ).toLocaleString()}
                                                </td>
                                                <td>
                                                    {lists[key]?.commission_rate
                                                        ? lists[
                                                              key
                                                          ].commission_rate.toLocaleString()
                                                        : "-"}
                                                    %
                                                </td>
                                                <td>
                                                    ￥
                                                    {(
                                                        lists[key]?.net ?? 0
                                                    ).toLocaleString()}
                                                </td>
                                                <td className="txtalc">
                                                    {documentZeiKbns?.[
                                                        lists[key]?.zei_kbn
                                                    ] ?? "-"}
                                                </td>
                                            </tr>
                                        );
                                    })}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <ul id="formControl">
                <li className="wd50">
                    <button
                        className="grayBtn"
                        onClick={handleBack}
                        disabled={isSending}
                    >
                        <span className="material-icons">arrow_back_ios</span>
                        キャンセルせずに戻る
                    </button>
                </li>
                <li className="wd50">
                    <button
                        className={classNames("redBtn", {
                            loading: isSending
                        })}
                        disabled={isSending}
                        onClick={handleSend}
                    >
                        <span className="material-icons">save</span>{" "}
                        この内容でキャンセルする
                    </button>
                </li>
            </ul>

            <SubjectModal
                id="mdSubject"
                data={currentEditPurchase}
                setData={setCurrentEditPurchase}
                priceSetting={priceSetting}
                setPriceSetting={setPriceSetting}
                subjectInfo={
                    lists?.[currentPurchaseKey]?.[SUBJECT_INFO_PROPERTY]
                }
                handleRegist={handleDetailRegist}
            />
        </>
    );
};

export default CancelChargeArea;
