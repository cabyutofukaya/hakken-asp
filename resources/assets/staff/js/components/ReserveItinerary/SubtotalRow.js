import React, { useContext } from "react";
import { ConstContext } from "../ConstApp";
import { ReserveItineraryConstContext } from "../ReserveItineraryConstApp";
import SettingItem from "./SettingItem";
import _ from "lodash";
import { existsIsAliveCancelParticipant } from "../../libs";

/**
 * 科目小計
 * @returns
 */
const SubtotalRow = ({
    date,
    reservePurchasingSubjects,
    inputName,
    zeiKbns,
    handleEditPurchasingModal,
    handlePurchasingDeleteModal
}) => {
    const { purchaseNormal, purchaseCancel } = useContext(ConstContext);
    const { subjectCategoryTypes } = useContext(ReserveItineraryConstContext);

    return (
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
                            <th className="txtalc">
                                <span>設定項目</span>
                            </th>
                            <th>
                                <span>備考</span>
                            </th>
                            <th className="txtalc wd10">
                                <span>削除</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        {reservePurchasingSubjects.map((item, index) => (
                            <tr key={index}>
                                <td>
                                    <a
                                        href="#"
                                        className="js-modal-open"
                                        data-target="mdSubject"
                                        onClick={e =>
                                            handleEditPurchasingModal(e, index)
                                        }
                                    >
                                        {item?.name ?? "-"}
                                    </a>
                                </td>
                                <td className="txtalc">
                                    {/**ホテル科目は同一番号の部屋数を数量とする。valid==1のレコードに絞る。仕入タイプはpurchaseNormal */}
                                    {item?.subject ===
                                        subjectCategoryTypes.hotel &&
                                        (Object.keys(
                                            _.groupBy(
                                                _.map(
                                                    _.filter(
                                                        item?.participants,
                                                        function(item) {
                                                            return (
                                                                item?.purchase_type ==
                                                                    purchaseNormal &&
                                                                item?.valid == 1
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
                                    {/**ホテル科目以外は有効にチェックのある行数計。仕入タイプはpurchaseNormal
                                     */}
                                    {item?.subject !==
                                        subjectCategoryTypes.hotel &&
                                        _.sumBy(
                                            _.filter(item?.participants, {
                                                purchase_type: purchaseNormal
                                            }),
                                            row => {
                                                return parseInt(row?.valid, 10);
                                            }
                                        )}
                                </td>
                                <td>
                                    ￥
                                    {_.sumBy(item?.participants, row => {
                                        {
                                            /**有効にチェックがある行のみ集計。仕入種別はpurchaseNormal */
                                        }
                                        return row?.purchase_type ==
                                            purchaseNormal &&
                                            row?.valid &&
                                            row?.gross
                                            ? parseInt(row.gross, 10)
                                            : 0;
                                    }).toLocaleString()}
                                </td>
                                <td>
                                    ￥
                                    {_.sumBy(item?.participants, row => {
                                        {
                                            /**有効にチェックがある行のみ集計。仕入種別はpurchaseNormal */
                                        }
                                        return row?.purchase_type ==
                                            purchaseNormal &&
                                            row?.valid &&
                                            row?.cost
                                            ? parseInt(row.cost, 10)
                                            : 0;
                                    }).toLocaleString()}
                                </td>
                                <td>
                                    {_.isNaN(
                                        _.meanBy(item?.participants, row => {
                                            {
                                                /**有効にチェックがある行のみ集計。仕入種別はpurchaseNormal */
                                            }
                                            return row?.purchase_type ==
                                                purchaseNormal &&
                                                row?.valid &&
                                                row?.commission_rate
                                                ? parseInt(
                                                      row.commission_rate,
                                                      10
                                                  )
                                                : 0;
                                        })
                                    )
                                        ? "0"
                                        : _.meanBy(item?.participants, row => {
                                              {
                                                  /**有効にチェックがある行のみ集計。仕入種別はpurchaseNormal */
                                              }
                                              return row?.purchase_type ==
                                                  purchaseNormal &&
                                                  row?.valid &&
                                                  row?.commission_rate
                                                  ? parseInt(
                                                        row.commission_rate,
                                                        10
                                                    )
                                                  : 0;
                                          }).toFixed(1)}
                                    %
                                </td>
                                <td>
                                    ￥
                                    {_.sumBy(item?.participants, row => {
                                        {
                                            /**有効にチェックがある行のみ集計。仕入種別はpurchaseNormal */
                                        }
                                        return row?.purchase_type ==
                                            purchaseNormal &&
                                            row?.valid &&
                                            row?.net
                                            ? parseInt(row.net, 10)
                                            : 0;
                                    }).toLocaleString()}
                                </td>
                                <td className="txtalc">
                                    {/** 税区分の選択が全て同じ場合はその値を、異なる場合は不明扱い*/}
                                    {item?.participants?.length > 0 &&
                                    _.filter(item?.participants, {
                                        purchase_type: purchaseNormal,
                                        valid: 1
                                    }).every(row => {
                                        return (
                                            row?.zei_kbn ==
                                            item.participants[0]?.zei_kbn
                                        );
                                    })
                                        ? zeiKbns?.[
                                              item.participants[0].zei_kbn
                                          ]
                                        : "-"}
                                </td>
                                <td className="txtalc">
                                    <SettingItem date={date} item={item} />
                                </td>
                                <td>{item?.note ?? "-"}</td>
                                <td className="txtalc">
                                    {/**キャンセル仕入行がなければ削除可。ある場合は削除不可 */}
                                    {!existsIsAliveCancelParticipant(
                                        item?.participants ?? []
                                    ) ? (
                                        <span
                                            className="material-icons js-modal-open"
                                            data-target="mdPurchasingDelete"
                                            onClick={e =>
                                                handlePurchasingDeleteModal(
                                                    e,
                                                    index
                                                )
                                            }
                                        >
                                            delete
                                        </span>
                                    ) : (
                                        <>-</>
                                    )}
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
};

export default SubtotalRow;
