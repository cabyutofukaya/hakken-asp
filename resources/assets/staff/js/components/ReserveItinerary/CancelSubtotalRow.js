import React, { useContext } from "react";
import { ConstContext } from "../ConstApp";
import { ReserveItineraryConstContext } from "../ReserveItineraryConstApp";
import { existsIsAliveCancelParticipant } from "../../libs";
import _ from "lodash";

/**
 * キャンセル科目小計
 * @returns
 */
const CancelSubtotalRow = ({
    date,
    reservePurchasingSubjects,
    inputName,
    zeiKbns,
    handleEditCancelPurchasingModal
}) => {
    const { purchaseNormal, purchaseCancel } = useContext(ConstContext);
    const { subjectCategoryTypes, modes } = useContext(
        ReserveItineraryConstContext
    );

    return (
        <div className="subjectList cancel">
            <h3>キャンセルした仕入科目</h3>
            <div className="tableWrap dragTable">
                <div className="tableCont">
                    <table>
                        <thead>
                            <tr>
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
                                    <span>仕入先支払料金</span>
                                </th>
                                <th>
                                    <span>粗利</span>
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
                            {reservePurchasingSubjects.map((item, index) => {
                                {
                                    /**キャンセル仕入行があれば表示 */
                                }
                                if (
                                    existsIsAliveCancelParticipant(
                                        item?.participants ?? []
                                    )
                                ) {
                                    return (
                                        <tr key={index}>
                                            <td>
                                                <a
                                                    href="#"
                                                    className="js-modal-open"
                                                    data-target="mdSubjectCancel"
                                                    onClick={e =>
                                                        handleEditCancelPurchasingModal(
                                                            e,
                                                            index
                                                        )
                                                    }
                                                >
                                                    {item?.name ?? "-"}
                                                </a>
                                            </td>
                                            <td className="txtalc">
                                                {/**ホテル科目は同一番号の部屋数を数量とする。is_cancel==1のレコードに絞る。仕入タイプはpurchaseCancel */}
                                                {item?.subject ===
                                                    subjectCategoryTypes.hotel &&
                                                    (Object.keys(
                                                        _.groupBy(
                                                            _.map(
                                                                _.filter(
                                                                    item?.participants,
                                                                    function(
                                                                        item
                                                                    ) {
                                                                        return (
                                                                            item?.purchase_type ==
                                                                                purchaseCancel &&
                                                                            item?.is_cancel ==
                                                                                1
                                                                        );
                                                                    }
                                                                ),
                                                                row => {
                                                                    return !row?.room_number
                                                                        ? {
                                                                              room_number: null
                                                                          }
                                                                        : {
                                                                              room_number:
                                                                                  row.room_number
                                                                          }; // 部屋番号はnullと空文字を同じ部屋としてカウント
                                                                }
                                                            ),
                                                            "room_number" // 部屋番号でグループ化
                                                        )
                                                    ).length ??
                                                        0)}
                                                {/**ホテル科目以外は有効にチェックのある行数計。仕入タイプはpurchaseCancel
                                                 */}
                                                {item?.subject !==
                                                    subjectCategoryTypes.hotel &&
                                                    _.sumBy(
                                                        _.filter(
                                                            item?.participants,
                                                            {
                                                                purchase_type: purchaseCancel
                                                            }
                                                        ),
                                                        row => {
                                                            return parseInt(
                                                                row?.is_cancel,
                                                                10
                                                            );
                                                        }
                                                    )}
                                            </td>
                                            <td>
                                                ￥
                                                {_.sumBy(
                                                    item?.participants,
                                                    row => {
                                                        {
                                                            /**キャンセルにチェックがある行のみ集計。仕入種別はpurchaseCancel */
                                                        }
                                                        return row?.purchase_type ==
                                                            purchaseCancel &&
                                                            row?.is_cancel &&
                                                            row?.cancel_charge
                                                            ? parseInt(
                                                                  row.cancel_charge,
                                                                  10
                                                              )
                                                            : 0;
                                                    }
                                                ).toLocaleString()}
                                            </td>
                                            <td>
                                                ￥
                                                {_.sumBy(
                                                    item?.participants,
                                                    row => {
                                                        {
                                                            /**キャンセルにチェックがある行のみ集計。仕入種別はpurchaseCancel */
                                                        }
                                                        return row?.purchase_type ==
                                                            purchaseCancel &&
                                                            row?.is_cancel &&
                                                            row?.cancel_charge_net
                                                            ? parseInt(
                                                                  row.cancel_charge_net,
                                                                  10
                                                              )
                                                            : 0;
                                                    }
                                                ).toLocaleString()}
                                            </td>
                                            <td>
                                                ￥
                                                {_.sumBy(
                                                    item?.participants,
                                                    row => {
                                                        {
                                                            /**キャンセルにチェックがある行のみ集計。仕入種別はpurchaseCancel */
                                                        }
                                                        return row?.purchase_type ==
                                                            purchaseCancel &&
                                                            row?.is_cancel &&
                                                            row?.cancel_charge_profit
                                                            ? parseInt(
                                                                  row.cancel_charge_profit,
                                                                  10
                                                              )
                                                            : 0;
                                                    }
                                                ).toLocaleString()}
                                            </td>
                                            <td>
                                                ￥
                                                {_.sumBy(
                                                    item?.participants,
                                                    row => {
                                                        {
                                                            /**キャンセルにチェックがある行のみ集計。仕入種別はpurchaseCancel */
                                                        }
                                                        return row?.purchase_type ==
                                                            purchaseCancel &&
                                                            row?.is_cancel &&
                                                            row?.gross
                                                            ? parseInt(
                                                                  row.gross,
                                                                  10
                                                              )
                                                            : 0;
                                                    }
                                                ).toLocaleString()}
                                            </td>
                                            <td>
                                                ￥
                                                {_.sumBy(
                                                    item?.participants,
                                                    row => {
                                                        {
                                                            /**キャンセルにチェックがある行のみ集計。仕入種別はpurchaseCancel */
                                                        }
                                                        return row?.purchase_type ==
                                                            purchaseCancel &&
                                                            row?.is_cancel &&
                                                            row?.cost
                                                            ? parseInt(
                                                                  row.cost,
                                                                  10
                                                              )
                                                            : 0;
                                                    }
                                                ).toLocaleString()}
                                            </td>
                                            <td>
                                                {_.isNaN(
                                                    _.meanBy(
                                                        item?.participants,
                                                        row => {
                                                            {
                                                                /**有効にチェックがある行のみ集計。仕入種別はpurchaseCancel */
                                                            }
                                                            return row?.purchase_type ==
                                                                purchaseCancel &&
                                                                row?.is_cancel &&
                                                                row?.commission_rate
                                                                ? parseInt(
                                                                      row.commission_rate,
                                                                      10
                                                                  )
                                                                : 0;
                                                        }
                                                    )
                                                )
                                                    ? "0"
                                                    : _.meanBy(
                                                          item?.participants,
                                                          row => {
                                                              {
                                                                  /**有効にチェックがある行のみ集計。仕入種別はpurchaseCancel */
                                                              }
                                                              return row?.purchase_type ==
                                                                  purchaseCancel &&
                                                                  row?.is_cancel &&
                                                                  row?.commission_rate
                                                                  ? parseInt(
                                                                        row.commission_rate,
                                                                        10
                                                                    )
                                                                  : 0;
                                                          }
                                                      ).toFixed(1)}
                                                %
                                            </td>
                                            <td>
                                                ￥
                                                {_.sumBy(
                                                    item?.participants,
                                                    row => {
                                                        {
                                                            /**有効にチェックがある行のみ集計。仕入種別はpurchaseCancel */
                                                        }
                                                        return row?.purchase_type ==
                                                            purchaseCancel &&
                                                            row?.is_cancel &&
                                                            row?.net
                                                            ? parseInt(
                                                                  row.net,
                                                                  10
                                                              )
                                                            : 0;
                                                    }
                                                ).toLocaleString()}
                                            </td>
                                            <td className="txtalc">
                                                {/** 税区分の選択が全て同じ場合はその値を、異なる場合は不明扱い*/}
                                                {item?.participants?.length >
                                                    0 &&
                                                _.filter(item?.participants, {
                                                    purchase_type: purchaseCancel,
                                                    is_cancel: 1
                                                }).every(row => {
                                                    return (
                                                        row?.zei_kbn ==
                                                        item.participants[0]
                                                            ?.zei_kbn
                                                    );
                                                })
                                                    ? zeiKbns?.[
                                                          item.participants[0]
                                                              .zei_kbn
                                                      ]
                                                    : "-"}
                                            </td>
                                        </tr>
                                    );
                                }
                            })}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    );
};

export default CancelSubtotalRow;
