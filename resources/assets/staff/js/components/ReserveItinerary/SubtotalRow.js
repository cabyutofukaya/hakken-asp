import React, { useContext } from "react";
import { ReserveItineraryConstContext } from "../ReserveItineraryConstApp";
import SettingItem from "./SettingItem";
import SubjectHiddenRow from "./SubjectHiddenRow";
import _ from "lodash";

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
                                    <SubjectHiddenRow
                                        item={item}
                                        index={index}
                                        inputName={inputName}
                                    />
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
                                    {/**ホテル科目は同一番号の部屋数を数量とする。valid==1のレコードに絞る */}
                                    {item?.subject ===
                                        subjectCategoryTypes.hotel &&
                                        (Object.keys(
                                            _.groupBy(
                                                _.filter(
                                                    item?.participants,
                                                    function(item) {
                                                        return item?.valid == 1;
                                                    }
                                                ),
                                                "room_number"
                                            )
                                        ).length ??
                                            0)}
                                    {/**ホテル科目以外は有効にチェックのある行数計
                                     */}
                                    {item?.subject !==
                                        subjectCategoryTypes.hotel &&
                                        _.sumBy(item?.participants, row => {
                                            return parseInt(row?.valid, 10);
                                        })}
                                </td>
                                <td>
                                    ￥
                                    {_.sumBy(item?.participants, row => {
                                        {
                                            /**有効にチェックがある行のみ集計 */
                                        }
                                        return row?.valid && row?.gross
                                            ? parseInt(row.gross, 10)
                                            : 0;
                                    }).toLocaleString()}
                                </td>
                                <td>
                                    ￥
                                    {_.sumBy(item?.participants, row => {
                                        {
                                            /**有効にチェックがある行のみ集計 */
                                        }
                                        return row?.valid && row?.cost
                                            ? parseInt(row.cost, 10)
                                            : 0;
                                    }).toLocaleString()}
                                </td>
                                <td>
                                    {_.isNaN(
                                        _.meanBy(item?.participants, row => {
                                            {
                                                /**有効にチェックがある行のみ集計 */
                                            }
                                            return row?.valid &&
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
                                                  /**有効にチェックがある行のみ集計 */
                                              }
                                              return row?.valid &&
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
                                            /**有効にチェックがある行のみ集計 */
                                        }
                                        return row?.valid && row?.net
                                            ? parseInt(row.net, 10)
                                            : 0;
                                    }).toLocaleString()}
                                </td>
                                <td className="txtalc">
                                    {/** 税区分の選択が全て同じ場合はその値を、異なる場合は不明扱い*/}
                                    {item?.participants?.length > 0 &&
                                    _.filter(item?.participants, {
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
