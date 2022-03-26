import _ from "lodash";
import React, { useState, useReducer, useContext, useCallback } from "react";
import { ConstContext } from "./components/ConstApp";
import ConstApp from "./components/ConstApp";
import ReserveItineraryConstApp from "./components/ReserveItineraryConstApp";
import { ReserveItineraryConstContext } from "./components/ReserveItineraryConstApp"; // 下層コンポーネントに定数などを渡すコンテキスト
import { render } from "react-dom";
import { useMountedRef } from "../../hooks/useMountedRef";
import SmallDangerModal from "./components/SmallDangerModal";
import Waypoint from "./components/ReserveItinerary/Waypoint";
import WaypointImage from "./components/ReserveItinerary/WaypointImage";
import Destination from "./components/ReserveItinerary/Destination";
import SubjectModal from "./components/ReserveItinerary/SubjectModal";
import CancelSubjectModal from "./components/ReserveItinerary/CancelSubjectModal";
import { calcTaxInclud, calcNet, calcGrossProfit } from "./libs";
import { RESERVE_ITINERARY } from "./constants";
import UnderButton from "./components/ReserveItinerary/UnderButton";

// 写真項目初期値。説明フィールドは含まない
const initialPhotoInfo = {
    file_name: "",
    upload_file_name: "",
    file_size: 0,
    original_file_name: "",
    mime_type: ""
};

const listsReducer = (state, action) => {
    let copyState = _.cloneDeep(state); // 一応、stateはディープコピーしたものを使用
    if (action.type === "ADD_ROW") {
        // 行追加
        const row = {
            type: action.payload.name
        };
        const dateRow = copyState[action.payload.date];
        switch (action.payload.name) {
            case RESERVE_ITINERARY.WAYPOINT:
                dateRow.push(row);
                return { ...copyState };
            case RESERVE_ITINERARY.WAYPOINT_IMAGE:
                row["photos"] = [];
                row["photos"].push({ ...initialPhotoInfo, description: "" }); //画像フィールド初期化
                dateRow.push(row);
                return { ...copyState };
            case RESERVE_ITINERARY.DESTINATION:
                dateRow.push(row);
                return { ...copyState };
            default:
                return copyState;
        }
    } else if (action.type === "UP_ROW") {
        // 行を繰り上げ
        const dateRow = copyState[action.payload.date];
        if (action.payload.index === 0) {
            // 最上段の場合は処理ナシ
            return copyState;
        }
        dateRow.splice(
            action.payload.index - 1,
            2,
            dateRow[action.payload.index],
            dateRow[action.payload.index - 1]
        );
        return { ...copyState, [action.payload.date]: dateRow };
    } else if (action.type === "DOWN_ROW") {
        // 行を繰り下げ
        const dateRow = copyState[action.payload.date];
        if (
            action.payload.index === dateRow.length - 1 ||
            _.get(dateRow[action.payload.index + 1], "type") === "destination"
        ) {
            // 最後尾、もしくはテレコ対象行が「目的地」の場合は処理ナシ
            return copyState;
        }
        dateRow.splice(
            action.payload.index,
            2,
            dateRow[action.payload.index + 1],
            dateRow[action.payload.index]
        );
        return { ...copyState, [action.payload.date]: dateRow };
    } else if (action.type === "DELETE_ROW") {
        // 行削除
        const dateRow = copyState[action.payload.date];
        const newDateRow = dateRow.filter(
            (row, index) => index !== action.payload.index
        );
        $(".js-modal-close").trigger("click"); // 削除モーダルclose
        return { ...copyState, [action.payload.date]: newDateRow };
    } else if (action.type === "DELETE_PURCHASING_ROW") {
        // 仕入行削除
        let rows =
            copyState[action.payload.date][action.payload.index]
                .reserve_purchasing_subjects;
        rows = rows.filter((r, index) => index != action.payload.no);
        copyState[action.payload.date][
            action.payload.index
        ].reserve_purchasing_subjects = rows;
        $(".js-modal-close").trigger("click"); // 削除モーダルclose
        return { ...copyState };
    } else if (action.type === "CHANGE_VALUE") {
        // 入力値制御
        const dateRow = copyState[action.payload.date];
        dateRow[action.payload.index][action.payload.name] =
            action.payload.value;
        return { ...copyState };
    } else if (action.type === "CHANGE_PHOTO") {
        // 写真選択
        const photoRow =
            copyState[action.payload.date][action.payload.index]["photos"][
                action.payload.no
            ];
        photoRow[action.payload.name] = action.payload.value;
        return { ...copyState };
    } else if (action.type === "CHANGE_PHOTO_EXPLANATION") {
        // 写真情報の入力制御（説明フィールド）
        const photoRow =
            copyState[action.payload.date][action.payload.index]["photos"][
                action.payload.no
            ];
        photoRow[action.payload.name] = action.payload.value;
        return { ...copyState };
    } else if (action.type === "CHANGE_PHOTO_UPLOAD") {
        // 写真アップロード制御。既存の画像パスをクリアしてS3にアップした画像パスをセット
        const photoRow =
            copyState[action.payload.date][action.payload.index]["photos"][
                action.payload.no
            ];
        photoRow.file_name = "";
        photoRow.upload_file_name = action.payload.upload_file_name;
        photoRow.file_size = action.payload.file_size;
        photoRow.original_file_name = action.payload.original_file_name;
        photoRow.mime_type = action.payload.mime_type;
        return { ...copyState };
    } else if (action.type === "CHANGE_CLEAR_PHOTO") {
        // 画像情報クリア
        const photoRow =
            copyState[action.payload.date][action.payload.index]["photos"][
                action.payload.no
            ];
        Object.keys(initialPhotoInfo).map(
            k => (photoRow[k] = initialPhotoInfo[k])
        ); // 写真フィールド初期化
        return { ...copyState };
    } else if (action.type === "ADD_PURCHASING_ROW") {
        // 仕入情報追加
        const row = copyState[action.payload.date][action.payload.index];
        if (!Array.isArray(row?.reserve_purchasing_subjects)) {
            row["reserve_purchasing_subjects"] = [];
        }
        row["reserve_purchasing_subjects"].push(action.payload.data); // reserve_purchasing_subjectsに仕入情報を追加
        return { ...copyState };
    } else if (action.type === "UPDATE_PURCHASING_ROW") {
        // 仕入情報編集
        copyState[action.payload.date][action.payload.index][
            "reserve_purchasing_subjects"
        ][action.payload.no] = action.payload.data;
        return {
            ...copyState
        };
    }
};

const ItineraryArea = ({
    editMode,
    isTravelDates,
    defaultValue,
    formSelects,
    consts,
    participants,
    customFields,
    subjectCustomCategoryCode,
    modalInitialValues
} = {}) => {
    if (!isTravelDates)
        return (
            <>
                <div>旅行日が設定されていません（出発日、帰着日）。</div>
                <UnderButton
                    backUrl={consts.backUrl}
                    editMode={editMode}
                    canSave={isTravelDates == 1}
                    handleSubmit={e => {}}
                />
            </>
        ); // 旅行日未設定

    const { agencyAccount, purchaseNormal, purchaseCancel } = useContext(
        ConstContext
    );

    const mounted = useMountedRef(); // マウント・アンマウント制御

    const { subjectCategoryTypes, modes } = useContext(
        ReserveItineraryConstContext
    );

    const [isDeleteChecking, setIsDeleteChecking] = useState(false); // 削除可否チェック中か否か
    const [isSubmitting, setIsSubmitting] = useState(false); // form送信中か否か

    // ParticipantPriceTrait@getInitialDataと同じ処理
    const initialTargetPurchasing = {
        mode: modes.purchasing_mode_create,
        subject: subjectCategoryTypes?.default,
        ad_zei_kbn: modalInitialValues?.zeiKbnDefault,
        ch_zei_kbn: modalInitialValues?.zeiKbnDefault,
        inf_zei_kbn: modalInitialValues?.zeiKbnDefault,
        participants: [
            ...participants.map(row => {
                return {
                    participant_id: row.participant_id,
                    purchase_type: row.cancel ? purchaseCancel : purchaseNormal, // 仕入種別
                    valid: row.cancel ? 0 : 1, // 取り消しユーザーの場合はvalid=falseで初期化
                    is_cancel: 0, // キャンセル料はナシで初期化で良いと思う。TODO 今一度要検討
                    age_kbn: row.age_kbn,
                    zei_kbn: modalInitialValues?.zeiKbnDefault,
                    //　キャンセル金額関連は0円で初期化
                    cancel_charge: 0,
                    cancel_charge_net: 0,
                    cancel_charge_profit: 0
                };
            })
        ]
    }; // 仕入情報初期値(PURCHASING_MODE_CREATE=新規登録)

    const [lists, rowDispatch] = useReducer(listsReducer, defaultValue?.dates); // 日程情報の入力制御

    const [note, setNote] = useState(defaultValue?.note); // 備考入力制御
    // 追加対象行情報。日付、行番号
    const [targetAddRow, setTargetAddRow] = useReducer((state, newState) => ({
        ...state,
        ...newState
    }));

    // 編集対象の仕入行情報
    const [editPurchasingRowInfo, setEditPurchasingRowInfo] = useReducer(
        (state, newState) => ({
            ...state,
            ...newState
        })
    );
    // 削除対象の仕入行情報
    const [deletePurchasingRowInfo, setDeletePurchasingRowInfo] = useReducer(
        (state, newState) => ({
            ...state,
            ...newState
        })
    );

    // 削除対象行情報
    const [deleteRowInfo, setDeleteRowInfo] = useReducer((state, newState) => ({
        ...state,
        ...newState
    }));

    // 現在、入力対象になっている仕入行情報の入力制御。下層プロパティの値を変更する際はディープコピーを使用すること！
    const [targetPurchasing, targetPurchasingDispatch] = useReducer(
        (state, action) => {
            let copyState = _.cloneDeep(state); // 下層プロパティを変更する処理が多いので一応、stateはディープコピーしたものを使用する

            switch (action.type) {
                case "CHANGE_SUBJECT": //科目変更。subjectの値以外をリセット
                    return { ...initialTargetPurchasing, ...action.payload };
                case "CHANGE_INPUT": //入力制御
                    return { ...copyState, ...action.payload };
                case "BULK_CHANGE_PRICE": //料金一括変更
                    // 参加者行の同項目も同じ値で一括変更
                    Object.keys(action.payload).map((key, index) => {
                        let m = null;
                        if ((m = key.match(/^ad_(.+)/))) {
                            // 大人区分
                            copyState.participants.forEach(row => {
                                if (row.age_kbn === consts.ageKbns?.ad) {
                                    row[m[1]] = action.payload[key];
                                }
                            });
                        } else if ((m = key.match(/^ch_(.+)/))) {
                            // 子供区分
                            copyState.participants.forEach(row => {
                                if (row.age_kbn === consts.ageKbns?.ch) {
                                    row[m[1]] = action.payload[key];
                                }
                            });
                        } else if ((m = key.match(/^inf_(.+)/))) {
                            // 幼児区分
                            copyState.participants.forEach(row => {
                                if (row.age_kbn === consts.ageKbns?.inf) {
                                    row[m[1]] = action.payload[key];
                                }
                            });
                        }
                    });
                    return { ...copyState, ...action.payload };
                case "CHANGE_PRICE": //料金枠入力制御
                    const name = Object.keys(action.payload)[0];
                    const value = action.payload[name];

                    // copyState[name] = Number(value); 税区分が数字ではない場合がある
                    copyState[name] = value;

                    let m = null;
                    // 税金計算
                    if ((m = name.match(/^ad_(.+)/))) {
                        // 大人区分
                        if (/_gross_ex$/.test(name) || /_zei_kbn$/.test(name)) {
                            // 税金
                            copyState["ad_gross"] = calcTaxInclud(
                                copyState["ad_gross_ex"],
                                copyState["ad_zei_kbn"]
                            );
                            //粗利
                            copyState["ad_gross_profit"] = calcGrossProfit(
                                copyState["ad_gross"],
                                copyState["ad_net"]
                            );
                            copyState.participants.forEach(row => {
                                if (row.age_kbn === consts.ageKbns?.ad) {
                                    row["gross_ex"] = copyState["ad_gross_ex"];
                                    row["zei_kbn"] = copyState["ad_zei_kbn"];
                                    row["gross"] = copyState["ad_gross"];
                                    row["gross_profit"] =
                                        copyState["ad_gross_profit"];
                                }
                            });
                        } else if (
                            /_cost$/.test(name) ||
                            /_commission_rate$/.test(name)
                        ) {
                            //NET単価
                            copyState["ad_net"] = calcNet(
                                copyState["ad_cost"],
                                copyState["ad_commission_rate"]
                            );
                            //粗利
                            copyState["ad_gross_profit"] = calcGrossProfit(
                                copyState["ad_gross"],
                                copyState["ad_net"]
                            );
                            copyState.participants.forEach(row => {
                                if (row.age_kbn === consts.ageKbns?.ad) {
                                    row["cost"] = copyState["ad_cost"];
                                    row["commission_rate"] =
                                        copyState["ad_commission_rate"];
                                    row["net"] = copyState["ad_net"];
                                    row["gross_profit"] =
                                        copyState["ad_gross_profit"];
                                }
                            });
                        } else if (/_gross$/.test(name) || /_net$/.test(name)) {
                            // 粗利
                            copyState["ad_gross_profit"] = calcGrossProfit(
                                copyState["ad_gross"],
                                copyState["ad_net"]
                            );
                            copyState.participants.forEach(row => {
                                if (row.age_kbn === consts.ageKbns?.ad) {
                                    row["cost"] = copyState["ad_cost"];
                                    row["net"] = copyState["ad_net"];
                                    row["gross_profit"] =
                                        copyState["ad_gross_profit"];
                                }
                            });
                        }
                    } else if ((m = name.match(/^ch_(.+)/))) {
                        // 子供区分
                        if (/_gross_ex$/.test(name) || /_zei_kbn$/.test(name)) {
                            // 税金
                            copyState["ch_gross"] = calcTaxInclud(
                                copyState["ch_gross_ex"],
                                copyState["ch_zei_kbn"]
                            );
                            //粗利
                            copyState["ch_gross_profit"] = calcGrossProfit(
                                copyState["ch_gross"],
                                copyState["ch_net"]
                            );
                            copyState.participants.forEach(row => {
                                if (row.age_kbn === consts.ageKbns?.ch) {
                                    row["gross_ex"] = copyState["ch_gross_ex"];
                                    row["zei_kbn"] = copyState["ch_zei_kbn"];
                                    row["gross"] = copyState["ch_gross"];
                                    row["gross_profit"] =
                                        copyState["ch_gross_profit"];
                                }
                            });
                        } else if (
                            /_cost$/.test(name) ||
                            /_commission_rate$/.test(name)
                        ) {
                            // NET単価
                            copyState["ch_net"] = calcNet(
                                copyState["ch_cost"],
                                copyState["ch_commission_rate"]
                            );
                            //粗利
                            copyState["ch_gross_profit"] = calcGrossProfit(
                                copyState["ch_gross"],
                                copyState["ch_net"]
                            );
                            copyState.participants.forEach(row => {
                                if (row.age_kbn === consts.ageKbns?.ch) {
                                    row["cost"] = copyState["ch_cost"];
                                    row["commission_rate"] =
                                        copyState["ch_commission_rate"];
                                    row["net"] = copyState["ch_net"];
                                    row["gross_profit"] =
                                        copyState["ch_gross_profit"];
                                }
                            });
                        } else if (/_gross$/.test(name) || /_net$/.test(name)) {
                            // 粗利
                            copyState["ch_gross_profit"] = calcGrossProfit(
                                copyState["ch_gross"],
                                copyState["ch_net"]
                            );
                            copyState.participants.forEach(row => {
                                if (row.age_kbn === consts.ageKbns?.ch) {
                                    row["cost"] = copyState["ch_cost"];
                                    row["net"] = copyState["ch_net"];
                                    row["gross_profit"] =
                                        copyState["ch_gross_profit"];
                                }
                            });
                        }
                    } else if ((m = name.match(/^inf_(.+)/))) {
                        // 幼児区分
                        if (/_gross_ex$/.test(name) || /_zei_kbn$/.test(name)) {
                            // 税金
                            copyState["inf_gross"] = calcTaxInclud(
                                copyState["inf_gross_ex"],
                                copyState["inf_zei_kbn"]
                            );
                            //粗利
                            copyState["inf_gross_profit"] = calcGrossProfit(
                                copyState["inf_gross"],
                                copyState["inf_net"]
                            );
                            copyState.participants.forEach(row => {
                                if (row.age_kbn === consts.ageKbns?.inf) {
                                    row["gross_ex"] = copyState["inf_gross_ex"];
                                    row["zei_kbn"] = copyState["inf_zei_kbn"];
                                    row["gross"] = copyState["inf_gross"];
                                    row["gross_profit"] =
                                        copyState["inf_gross_profit"];
                                }
                            });
                        } else if (
                            /_cost$/.test(name) ||
                            /_commission_rate$/.test(name)
                        ) {
                            // NET単価
                            copyState["inf_net"] = calcNet(
                                copyState["inf_cost"],
                                copyState["inf_commission_rate"]
                            );
                            copyState.participants.forEach(row => {
                                if (row.age_kbn === consts.ageKbns?.inf) {
                                    row["cost"] = copyState["inf_cost"];
                                    row["commission_rate"] =
                                        copyState["inf_commission_rate"];
                                    row["net"] = copyState["inf_net"];
                                }
                            });
                        } else if (/_gross$/.test(name) || /_net$/.test(name)) {
                            // 粗利
                            copyState["inf_gross_profit"] = calcGrossProfit(
                                copyState["inf_gross"],
                                copyState["inf_net"]
                            );
                            copyState.participants.forEach(row => {
                                if (row.age_kbn === consts.ageKbns?.inf) {
                                    row["cost"] = copyState["inf_cost"];
                                    row["net"] = copyState["inf_net"];
                                    row["gross_profit"] =
                                        copyState["inf_gross_profit"];
                                }
                            });
                        }
                    }
                    return { ...copyState, ...action.payload };
                case "CHANGE_PARTICIPANT_INPUT": // 参加者行入力制御
                    copyState.participants[action.index][action.name] =
                        action.payload;
                    return {
                        ...copyState
                    };
                case "CHANGE_PARTICIPANT_CHECKBOX": // 参加者行入力制御(CHECKBOX)
                    const val = action.payload == 1 ? 0 : 1;
                    copyState.participants[action.index][action.name] = val;
                    return {
                        ...copyState
                    };
                case "CHANGE_PARTICIPANT_PRICE_INPUT": // 参加者行料金入力制御
                    const row = copyState.participants[action.index];
                    // row[action.name] = Number(action.payload); 税区分が数字ではない場合がある

                    row[action.name] = action.payload;
                    if (
                        /^gross_ex$/.test(action.name) ||
                        /^zei_kbn$/.test(action.name)
                    ) {
                        // 税金
                        row["gross"] = calcTaxInclud(
                            row["gross_ex"],
                            row["zei_kbn"]
                        );
                        // 粗利
                        row["gross_profit"] = calcGrossProfit(
                            row["gross"],
                            row["net"]
                        );
                    } else if (
                        /^cost$/.test(action.name) ||
                        /^commission_rate$/.test(action.name)
                    ) {
                        // NET単価
                        row["net"] = calcNet(
                            row["cost"],
                            row["commission_rate"]
                        );
                        // 粗利
                        row["gross_profit"] = calcGrossProfit(
                            row["gross"],
                            row["net"]
                        );
                    } else if (
                        /^gross$/.test(action.name) ||
                        /^net$/.test(action.name)
                    ) {
                        // 粗利
                        row["gross_profit"] = calcGrossProfit(
                            row["gross"],
                            row["net"]
                        );
                    } else if (
                        // キャンセルチャージorキャンセルチャージ仕入
                        /^cancel_charge$/.test(action.name) ||
                        /^cancel_charge_net$/.test(action.name)
                    ) {
                        // キャンセル粗利
                        row["cancel_charge_profit"] = calcGrossProfit(
                            row["cancel_charge"],
                            row["cancel_charge_net"]
                        );
                    }
                    return {
                        ...copyState
                    };
                case "INITIAL_EDIT": //編集データセット（通常仕入・キャンセル仕入）
                    setTargetAddRow({
                        date: action.payload.date,
                        index: action.payload.index
                    }); // 追加対象行情報を設定 -> 科目を変更した場合は登録処理が走るため、あらかじめ初期化
                    setEditPurchasingRowInfo({
                        date: action.payload.date,
                        index: action.payload.index,
                        no: action.payload.no
                    }); // 編集対象行情報を設定
                    let data =
                        lists[action.payload.date][action.payload.index][
                            "reserve_purchasing_subjects"
                        ][action.payload.no];
                    let copyData = _.cloneDeep(data);
                    copyData.mode = modes.purchasing_mode_edit; // PURCHASING_MODE_EDIT=編集
                    return { ...copyData };
                case "ADD_PURCHASING_MODAL": //仕入行追加ボタン押下
                    setTargetAddRow({
                        date: action.payload.date,
                        index: action.payload.index
                    }); // 追加対象行情報を設定
                    return { ...initialTargetPurchasing };
                default:
                    return copyState;
            }
        },
        initialTargetPurchasing
    );

    // 入力制御
    const handleChange = useCallback((e, date, index) => {
        rowDispatch({
            type: "CHANGE_VALUE",
            payload: {
                date,
                index,
                name: e.target.name,
                value: e.target.value
            }
        });
    }, []);

    // 写真アップロード。s3にアップした画像パスをセット
    const handleUploadPhoto = useCallback((e, date, index, no, data) => {
        rowDispatch({
            type: "CHANGE_PHOTO_UPLOAD",
            payload: {
                date,
                index,
                no,
                upload_file_name: data?.file_name ?? "",
                file_size: data?.file_size ?? 0,
                original_file_name: data?.original_file_name ?? "",
                mime_type: data?.mime_type ?? ""
            }
        });
    }, []);

    // 写真削除。保持している画像パスをクリア
    const handleClearPhoto = useCallback((e, date, index, no) => {
        rowDispatch({
            type: "CHANGE_CLEAR_PHOTO",
            payload: {
                date,
                index,
                no
            }
        });
    }, []);

    // 画像ファイルの選択制御
    const handleChangePhoto = useCallback((e, date, index, no) => {
        rowDispatch({
            type: "CHANGE_PHOTO",
            payload: {
                date,
                index,
                no,
                name: e.target.name,
                value: e.target.value
            }
        });
    }, []);

    // 写真情報の入力データ制御（説明フィールド）
    const handleChangePhotoExplanation = useCallback((e, date, index, no) => {
        rowDispatch({
            type: "CHANGE_PHOTO_EXPLANATION",
            payload: {
                date,
                index,
                no,
                name: e.target.name,
                value: e.target.value
            }
        });
    }, []);

    // スケジュール行追加
    const handleAddRow = useCallback((e, name, date) => {
        e.preventDefault();
        rowDispatch({
            type: "ADD_ROW",
            payload: {
                name,
                date
            }
        });
    }, []);

    // スケジュール行UP
    const handleUpRow = useCallback((e, date, index) => {
        e.preventDefault();
        rowDispatch({
            type: "UP_ROW",
            payload: {
                date,
                index
            }
        });
    }, []);

    const handleDownRow = useCallback((e, date, index) => {
        e.preventDefault();
        rowDispatch({
            type: "DOWN_ROW",
            payload: {
                date,
                index
            }
        });
    }, []);

    // スケジュール行削除
    // 削除対象スケジュールに対する出金データがある場合はエラーを出す。なければ削除
    const handleDeleteRow = useCallback(
        async e => {
            e.preventDefault();

            const date = deleteRowInfo.date;
            const index = deleteRowInfo.index;

            // 対象行
            const row = lists?.[date]?.[index];
            if (row?.id) {
                //スケジュール行あり

                if (!mounted.current || isDeleteChecking) return;
                setIsDeleteChecking(true);

                // 削除対象スケジュールにて出金登録がある場合はエラー
                const response = await axios
                    .get(
                        `/api/${agencyAccount}/reserve_schedule/${row.id}/exist_withdrawal`
                    )
                    .finally(() => {
                        if (mounted.current) {
                            setIsDeleteChecking(false);
                        }
                    });
                if (mounted.current && response?.data) {
                    if (response.data === "no") {
                        // スケジュール行削除
                        rowDispatch({
                            type: "DELETE_ROW",
                            payload: {
                                date,
                                index
                            }
                        });
                    } else if (response.data === "yes") {
                        alert(
                            "出金データがあるため削除できません。\nスケジュールを削除する前に支払管理より、当該商品の出金履歴を削除してからご変更ください。"
                        );
                        $(".js-modal-close").trigger("click"); // 削除モーダルclose
                    }
                }
            } else {
                // 新規行の場合はチェックなしで削除可
                rowDispatch({
                    type: "DELETE_ROW",
                    payload: {
                        date,
                        index
                    }
                });
            }
        },
        [deleteRowInfo]
    );

    /**
     * 仕入行削除
     * 削除対象商品に対する出金データがある場合はエラーを出す。なければ削除
     */
    const handleDeletePurchasingRow = useCallback(
        async e => {
            e.preventDefault();

            const date = deletePurchasingRowInfo.date;
            const index = deletePurchasingRowInfo.index;
            const no = deletePurchasingRowInfo.no;

            // 対象行
            const row =
                lists?.[date]?.[index]?.reserve_purchasing_subjects?.[no];

            // IDがある(=編集時)は出金登録があるかチェックして、ある場合はエラー
            if (row?.id) {
                if (!mounted.current || isDeleteChecking) return;
                setIsDeleteChecking(true);

                const response = await axios
                    .get(
                        `/api/${agencyAccount}/purchasing_subject/${row?.subject}/${row.id}/exist_withdrawal`
                    )
                    .finally(() => {
                        if (mounted.current) {
                            setIsDeleteChecking(false);
                        }
                    });
                if (mounted.current && response?.data) {
                    if (response.data === "no") {
                        // 仕入行削除
                        rowDispatch({
                            type: "DELETE_PURCHASING_ROW",
                            payload: {
                                date,
                                index,
                                no
                            }
                        });
                    } else if (response.data === "yes") {
                        alert(
                            "出金データがあるため削除できません。\n支払管理より、当該商品の出金履歴を削除してからご変更ください。"
                        );
                        $(".js-modal-close").trigger("click"); // 削除モーダルclose
                    }
                }
            } else {
                // 新規登録された仕入行はノーチェックで仕入行削除
                rowDispatch({
                    type: "DELETE_PURCHASING_ROW",
                    payload: {
                        date,
                        index,
                        no
                    }
                });
            }
        },
        [deletePurchasingRowInfo]
    );

    // form送信
    const handleSubmit = async e => {
        e.preventDefault();

        if (!mounted.current) return;
        if (isSubmitting) return;

        setIsSubmitting(true);

        // lists配列から画像データを削除(データが大きく、POST不要の値なので)
        let params = _.cloneDeep(lists);
        for (let date in params) {
            params[date].forEach((row, i) => {
                if (row?.photos) {
                    params[date][i].photos.forEach((r, j) => {
                        params[date][i].photos[j].image = null; // 画像データそのものはアップしないので不要
                    });
                }
            });
        }

        const param =
            editMode == "edit"
                ? {
                      dates: params,
                      note,
                      updated_at: defaultValue?.updated_at,
                      set_message: 1,
                      _method: "PUT"
                  }
                : { dates: params, note, set_message: 1 };

        const response = await axios.post(consts.postUrl, param).finally(() => {
            setTimeout(function() {
                if (mounted.current) {
                    setIsSubmitting(false);
                }
            }, 1000); // 少し間を置く
        });

        if (mounted.current && response?.data?.data) {
            location.href = consts.backUrl;
        }
    };

    return (
        <>
            {/**更新日時 */}
            {/* <input
                type="hidden"
                name="updated_at"
                value={defaultValue?.updated_at ?? ""}
            /> */}
            <h2 className="subTit">
                <span className="material-icons">subject </span>備考欄
            </h2>
            <div className="itineraryArea">
                <ul className="baseList">
                    <li>
                        <span className="inputLabel">備考</span>
                        <textarea
                            name="note"
                            value={note ?? ""}
                            onChange={e => setNote(e.target.value)}
                        />
                    </li>
                </ul>
            </div>
            {lists &&
                Object.keys(lists).map((date, index) => (
                    <React.Fragment key={index}>
                        <h2 className="subTit">
                            <span className="material-icons">event_note </span>
                            {date}
                            {/**スケジュールがない日付でもdatesパラメータがPOSTされるようにhidden値でセット。スケジュールのある日付は.itineraryArea内のブロックの値で上書きPOSTされる */}
                            <input type="hidden" name={`dates[${date}]`} />
                        </h2>
                        <div className="itineraryArea">
                            {lists[date] &&
                                lists[date].map((row, index) => {
                                    switch (row.type) {
                                        case consts.itineraryTypes.waypoint:
                                            return (
                                                <Waypoint
                                                    canUp={index > 0}
                                                    canDown={
                                                        index <
                                                            lists[date].length -
                                                                1 &&
                                                        _.get(
                                                            lists[date][
                                                                index + 1
                                                            ],
                                                            "type"
                                                        ) !== "destination"
                                                    }
                                                    key={index}
                                                    index={index}
                                                    input={row}
                                                    date={date}
                                                    participants={participants}
                                                    transportations={
                                                        formSelects?.transportations
                                                    }
                                                    transportationTypes={
                                                        consts?.transportationTypes
                                                    }
                                                    zeiKbns={
                                                        formSelects?.zeiKbns
                                                    }
                                                    handleChange={handleChange}
                                                    handleUpRow={handleUpRow}
                                                    handleDownRow={
                                                        handleDownRow
                                                    }
                                                    handleDelete={e =>
                                                        setDeleteRowInfo({
                                                            date,
                                                            index
                                                        })
                                                    }
                                                    setDeletePurchasingRowInfo={
                                                        setDeletePurchasingRowInfo
                                                    }
                                                    targetPurchasingDispatch={
                                                        targetPurchasingDispatch
                                                    }
                                                />
                                            );
                                        case consts.itineraryTypes
                                            .waypoint_image:
                                            return (
                                                <WaypointImage
                                                    canUp={index > 0}
                                                    canDown={
                                                        index <
                                                            lists[date].length -
                                                                1 &&
                                                        _.get(
                                                            lists[date][
                                                                index + 1
                                                            ],
                                                            "type"
                                                        ) !== "destination"
                                                    }
                                                    key={index}
                                                    index={index}
                                                    input={row}
                                                    date={date}
                                                    participants={participants}
                                                    thumbSBaseUrl={
                                                        consts?.thumbSBaseUrl
                                                    }
                                                    transportations={
                                                        formSelects?.transportations
                                                    }
                                                    transportationTypes={
                                                        consts?.transportationTypes
                                                    }
                                                    zeiKbns={
                                                        formSelects?.zeiKbns
                                                    }
                                                    handleChange={handleChange}
                                                    handleUploadPhoto={
                                                        handleUploadPhoto
                                                    }
                                                    handleClearPhoto={
                                                        handleClearPhoto
                                                    }
                                                    handleChangePhoto={
                                                        handleChangePhoto
                                                    }
                                                    handleChangePhotoExplanation={
                                                        handleChangePhotoExplanation
                                                    }
                                                    handleUpRow={handleUpRow}
                                                    handleDownRow={
                                                        handleDownRow
                                                    }
                                                    handleDelete={e =>
                                                        setDeleteRowInfo({
                                                            date,
                                                            index
                                                        })
                                                    }
                                                    setDeletePurchasingRowInfo={
                                                        setDeletePurchasingRowInfo
                                                    }
                                                    targetPurchasingDispatch={
                                                        targetPurchasingDispatch
                                                    }
                                                />
                                            );
                                        case consts.itineraryTypes.destination:
                                            return (
                                                <Destination
                                                    key={index}
                                                    index={index}
                                                    input={row}
                                                    date={date}
                                                    participants={participants}
                                                    zeiKbns={
                                                        formSelects?.zeiKbns
                                                    }
                                                    handleChange={handleChange}
                                                    handleDelete={e =>
                                                        setDeleteRowInfo({
                                                            date,
                                                            index
                                                        })
                                                    }
                                                    setDeletePurchasingRowInfo={
                                                        setDeletePurchasingRowInfo
                                                    }
                                                    targetPurchasingDispatch={
                                                        targetPurchasingDispatch
                                                    }
                                                />
                                            );
                                    }
                                })}

                            {/** 最後の要素が「宿泊地・目的地」でなければ追加ボタンを表示*/}
                            {_.get(_.last(lists[date]), "type") !==
                                "destination" && (
                                <ul className="itineraryControl">
                                    <li>
                                        <button
                                            className="blueBtn"
                                            onClick={e =>
                                                handleAddRow(
                                                    e,
                                                    consts?.itineraryTypes
                                                        ?.waypoint,
                                                    date
                                                )
                                            }
                                        >
                                            <span className="material-icons">
                                                location_on
                                            </span>
                                            スポット・経由地を追加
                                        </button>
                                    </li>
                                    <li>
                                        <button
                                            className="blueBtn"
                                            onClick={e =>
                                                handleAddRow(
                                                    e,
                                                    consts?.itineraryTypes
                                                        ?.waypoint_image,
                                                    date
                                                )
                                            }
                                        >
                                            <span className="material-icons">
                                                location_on
                                            </span>
                                            スポット・経由地(写真付き)を追加
                                        </button>
                                    </li>
                                    <li>
                                        <button
                                            className="blueBtn"
                                            onClick={e =>
                                                handleAddRow(
                                                    e,
                                                    consts?.itineraryTypes
                                                        ?.destination,
                                                    date
                                                )
                                            }
                                        >
                                            <span className="material-icons">
                                                flag
                                            </span>
                                            宿泊地・目的地を追加
                                        </button>
                                    </li>
                                </ul>
                            )}
                        </div>
                    </React.Fragment>
                ))}
            <UnderButton
                editMode={editMode}
                canSave={isTravelDates == 1}
                isSubmitting={isSubmitting}
                handleSubmit={handleSubmit}
                backUrl={consts.backUrl}
            />
            {/**スケジュール行削除 */}
            <SmallDangerModal
                id="mdScheduleDelete"
                title="この項目を削除しますか？"
                handleAction={handleDeleteRow}
                isActioning={isDeleteChecking}
            />

            {/**仕入行削除 */}
            <SmallDangerModal
                id="mdPurchasingDelete"
                title="この項目を削除しますか？"
                handleAction={handleDeletePurchasingRow}
                isActioning={isDeleteChecking}
            />

            <SubjectModal
                subjectCategories={formSelects?.subjectCategories}
                input={targetPurchasing}
                targetAddRow={targetAddRow}
                editPurchasingRowInfo={editPurchasingRowInfo}
                zeiKbns={formSelects?.zeiKbns}
                participants={participants}
                suppliers={formSelects?.suppliers}
                cities={formSelects?.cities}
                handleChange={targetPurchasingDispatch}
                rowDispatch={rowDispatch}
                customFields={customFields}
                subjectCustomCategoryCode={subjectCustomCategoryCode}
                customFieldCodes={consts?.customFieldCodes}
                defaultSubjectHotels={formSelects?.defaultSubjectHotels}
                defaultSubjectOptions={formSelects?.defaultSubjectOptions}
                defaultSubjectAirplanes={formSelects?.defaultSubjectAirplanes}
            />

            <CancelSubjectModal
                subjectCategories={formSelects?.subjectCategories}
                input={targetPurchasing}
                targetAddRow={targetAddRow}
                editPurchasingRowInfo={editPurchasingRowInfo}
                zeiKbns={formSelects?.zeiKbns}
                participants={participants}
                suppliers={formSelects?.suppliers}
                cities={formSelects?.cities}
                handleChange={targetPurchasingDispatch}
                rowDispatch={rowDispatch}
                customFields={customFields}
                subjectCustomCategoryCode={subjectCustomCategoryCode}
                customFieldCodes={consts?.customFieldCodes}
                defaultSubjectHotels={formSelects?.defaultSubjectHotels}
                defaultSubjectOptions={formSelects?.defaultSubjectOptions}
                defaultSubjectAirplanes={formSelects?.defaultSubjectAirplanes}
            />
        </>
    );
};
// 入力画面
const Element = document.getElementById("itineraryArea");
if (Element) {
    const editMode = Element.getAttribute("editMode");
    const isTravelDates = Element.getAttribute("isTravelDates");
    const jsVars = Element.getAttribute("jsVars");
    const parsedJsVars = jsVars && JSON.parse(jsVars);
    const reception = Element.getAttribute("reception");
    const applicationStep = Element.getAttribute("applicationStep");
    const applicationStepList = Element.getAttribute("applicationStepList");
    const isCanceled = Element.getAttribute("isCanceled");
    const isEnabled = Element.getAttribute("isEnabled");
    const parsedApplicationStepList =
        applicationStepList && JSON.parse(applicationStepList);
    const reserveNumber = Element.getAttribute("reserveNumber");
    const estimateNumber = Element.getAttribute("estimateNumber");
    const defaultValue = Element.getAttribute("defaultValue");
    const parsedDefaultValue = defaultValue && JSON.parse(defaultValue);
    const formSelects = Element.getAttribute("formSelects");
    const parsedFormSelects = formSelects && JSON.parse(formSelects);
    const consts = Element.getAttribute("consts");
    const parsedConsts = consts && JSON.parse(consts);
    const customFields = Element.getAttribute("customFields");
    const parsedCustomFields = customFields && JSON.parse(customFields);
    const subjectCustomCategoryCode = Element.getAttribute(
        "subjectCustomCategoryCode"
    );
    const participants = Element.getAttribute("participants");
    const parsedParticipants = participants && JSON.parse(participants);
    const modalInitialValues = Element.getAttribute("modalInitialValues");
    const parsedModalInitialValues =
        modalInitialValues && JSON.parse(modalInitialValues);

    render(
        <ConstApp jsVars={parsedJsVars}>
            <ReserveItineraryConstApp
                vars={{
                    reception,
                    applicationStep,
                    applicationStepList: parsedApplicationStepList,
                    estimateNumber,
                    reserveNumber,
                    subjectCategoryTypes: parsedConsts?.subjectCategoryTypes,
                    isCanceled,
                    isEnabled,
                    modes: parsedConsts?.modes
                }}
            >
                <ItineraryArea
                    editMode={editMode}
                    isTravelDates={isTravelDates}
                    defaultValue={parsedDefaultValue}
                    reserveNumber={reserveNumber}
                    formSelects={parsedFormSelects}
                    consts={parsedConsts}
                    customFields={parsedCustomFields}
                    subjectCustomCategoryCode={subjectCustomCategoryCode}
                    participants={parsedParticipants}
                    modalInitialValues={parsedModalInitialValues}
                />
            </ReserveItineraryConstApp>
        </ConstApp>,
        document.getElementById("itineraryArea")
    );
}
