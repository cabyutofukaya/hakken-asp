import React, { useState, useContext } from "react";
import { ConstContext } from "../ConstApp";
import OnlyNumberInput from "../OnlyNumberInput";
import ErrorMessage from "../ErrorMessage";
import { isEmptyObject } from "../../libs";
import classNames from "classnames";

/**
 *
 * @returns
 */
const CancelChargeArea = ({ defaultValue, consts, errors }) => {
    const { documentZeiKbns } = useContext(ConstContext);

    const csrfToken = document.head.querySelector('meta[name="csrf-token"]')
        .content;

    const [lists, setLists] = useState({ ...defaultValue["rows"] });
    const [errorObj, setErrorObj] = useState(errors); // エラー文言を保持

    const [cancelChargeErrors, setCancelChargeErrors] = useState([]); // キャンセルチャージでエラーがある枠のlists配列キー値を保持
    const [cancelChargeNetErrors, setCancelChargeNetErrors] = useState([]); // 仕入れ先支払料金でエラーがある枠のlists配列キー値を保持

    const [isSubmitting, setIsSubmitting] = useState(false); // form送信中

    // 入力制御
    const handleChange = (e, key, name) => {
        const row = lists[key];
        row[name] = e.target.value;
        lists[key] = row;
        setLists({ ...lists });
    };

    // キャンセル料の有無checkbox制御
    const handleCancelCheck = (e, key, name) => {
        const row = lists[key];

        // チェックボックスの入力制御
        const currentVal = row[name]; // 現在の値
        row[name] = currentVal == 1 ? 0 : 1;
        lists[key] = row;
        setLists({ ...lists });
    };

    // 送信制御
    const handleSubmit = e => {
        e.preventDefault();

        let errMsg = {};

        let err = [];
        // キャンセル料の入力値チェック → キャンセル料金が数量で割り切れない場合はエラーを出す
        Object.keys(lists).map(key => {
            const cancelCharge = Number(lists[key].cancel_charge);
            const quantity = lists[key].quantity;
            if (
                cancelCharge > 0 &&
                !Number.isInteger(cancelCharge / quantity)
            ) {
                // エラーのあったlists配列のキー値を保存
                err = [...err, key];
            }
        });

        setCancelChargeErrors([...err]);
        if (err.length > 0) {
            errMsg["cancel_charge"] = [
                "「キャンセル料金」は数量で割り切れる金額を設定してください。"
            ];
        }

        //////////////

        err = [];
        // 仕入れ先支払料金の入力値チェック → 仕入れ先支払料金が数量で割り切れない場合はエラーを出す
        Object.keys(lists).map(key => {
            const cancelChargeNet = Number(lists[key].cancel_charge_net);
            const quantity = lists[key].quantity;
            if (
                cancelChargeNet > 0 &&
                !Number.isInteger(cancelChargeNet / quantity)
            ) {
                // エラーのあったlists配列のキー値を保存
                err = [...err, key];
            }
        });

        setCancelChargeNetErrors([...err]);
        if (err.length > 0) {
            errMsg["cancel_charge_net"] = [
                "「仕入れ先支払料金」は数量で割り切れる金額を設定してください。"
            ];
        }

        setErrorObj(errMsg);

        // エラーがなければform送信
        if (isEmptyObject(errMsg)) {
            document.cancelChargeForm.submit();
            setIsSubmitting(true); // form送信中
        }
    };

    // 「キャンセルせずに戻る」ボタン
    const handleBack = e => {
        e.preventDefault();
        location.href = consts?.reserveUrl;
    };

    return (
        <>
            <ErrorMessage errorObj={errorObj} />

            <h2 className="subTit">
                <span className="material-icons"> subject </span>仕入れ先情報
            </h2>
            <form
                name="cancelChargeForm"
                action={consts.cancelChargeUpdateUrl}
                method="post"
                onSubmit={handleSubmit}
            >
                <input type="hidden" name="_token" value={csrfToken} />
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
                                            <span>商品名</span>
                                        </th>
                                        <th className="txtalc">
                                            <span>数量</span>
                                        </th>
                                        <th>
                                            <span>キャンセル料金</span>
                                        </th>
                                        <th>
                                            <span>仕入れ先支払料金</span>
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
                                                        done:
                                                            lists[key]?.valid ==
                                                            0
                                                    })}
                                                >
                                                    <td className="txtalc checkBox">
                                                        <input
                                                            type="checkbox"
                                                            id={`cancel_${key}`}
                                                            value="1"
                                                            name={`rows[${key}][is_cancel]`}
                                                            onChange={e =>
                                                                handleCancelCheck(
                                                                    e,
                                                                    key,
                                                                    "is_cancel"
                                                                )
                                                            }
                                                            checked={
                                                                lists[key]
                                                                    ?.is_cancel ==
                                                                1
                                                            }
                                                        />
                                                        <label
                                                            htmlFor={`cancel_${key}`}
                                                        >
                                                            &nbsp;
                                                        </label>
                                                    </td>
                                                    <td>
                                                        {lists[key]?.name ??
                                                            "-"}
                                                        <input
                                                            type="hidden"
                                                            name={`rows[${key}][name]`}
                                                            value={
                                                                lists[key]
                                                                    ?.name ?? ""
                                                            }
                                                        />
                                                    </td>
                                                    <td className="txtalc">
                                                        {lists[key]?.quantity ??
                                                            0}
                                                        <input
                                                            type="hidden"
                                                            name={`rows[${key}][quantity]`}
                                                            value={
                                                                lists[key]
                                                                    ?.quantity ??
                                                                0
                                                            }
                                                        />
                                                    </td>
                                                    <td className="slimInput">
                                                        <OnlyNumberInput
                                                            name={`rows[${key}][cancel_charge]`}
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
                                                                    e,
                                                                    key,
                                                                    "cancel_charge"
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
                                                        />
                                                    </td>
                                                    <td className="slimInput">
                                                        <OnlyNumberInput
                                                            name={`rows[${key}][cancel_charge_net]`}
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
                                                                    e,
                                                                    key,
                                                                    "cancel_charge_net"
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
                                                        />
                                                    </td>
                                                    <td>
                                                        ￥
                                                        {(
                                                            lists[key]?.gross ??
                                                            0
                                                        ).toLocaleString()}
                                                        <input
                                                            type="hidden"
                                                            name={`rows[${key}][gross]`}
                                                            value={
                                                                lists[key]
                                                                    ?.gross ?? 0
                                                            }
                                                        />
                                                    </td>
                                                    <td>
                                                        ￥
                                                        {(
                                                            lists[key]?.cost ??
                                                            0
                                                        ).toLocaleString()}
                                                        <input
                                                            type="hidden"
                                                            name={`rows[${key}][cost]`}
                                                            value={
                                                                lists[key]
                                                                    ?.cost ?? 0
                                                            }
                                                        />
                                                    </td>
                                                    <td>
                                                        {(
                                                            lists[key]
                                                                ?.commission_rate ??
                                                            0
                                                        ).toLocaleString()}
                                                        %
                                                        <input
                                                            type="hidden"
                                                            name={`rows[${key}][commission_rate]`}
                                                            value={
                                                                lists[key]
                                                                    ?.commission_rate ??
                                                                0
                                                            }
                                                        />
                                                    </td>
                                                    <td>
                                                        ￥
                                                        {(
                                                            lists[key]?.net ?? 0
                                                        ).toLocaleString()}
                                                        <input
                                                            type="hidden"
                                                            name={`rows[${key}][net]`}
                                                            value={
                                                                lists[key]
                                                                    ?.net ?? 0
                                                            }
                                                        />
                                                    </td>
                                                    <td className="txtalc">
                                                        {documentZeiKbns?.[
                                                            lists[key]?.zei_kbn
                                                        ] ?? "-"}
                                                        <input
                                                            type="hidden"
                                                            name={`rows[${key}][zei_kbn]`}
                                                            value={
                                                                lists[key]
                                                                    ?.zei_kbn ??
                                                                0
                                                            }
                                                        />
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
                            disabled={isSubmitting}
                        >
                            <span className="material-icons">
                                arrow_back_ios
                            </span>
                            キャンセルせずに戻る
                        </button>
                    </li>
                    <li className="wd50">
                        <button className="redBtn" disabled={isSubmitting}>
                            <span className="material-icons">save</span>{" "}
                            この内容でキャンセルする
                        </button>
                    </li>
                </ul>
            </form>
        </>
    );
};

export default CancelChargeArea;
