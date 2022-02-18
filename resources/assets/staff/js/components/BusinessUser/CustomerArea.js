import React, { useContext } from "react";
import { ConstContext } from "../ConstApp";
import { concatAdress, nl2br, toPostFormat } from "../../../../helpers";
import ManagerArea from "./ManagerArea";
import classNames from "classnames";

/**
 *
 * @param {array} customFields カスタム項目情報（値、項目データ）
 * @param {array} consts 各種定数値
 * @returns
 */
const CustomerArea = ({
    isShow,
    user,
    formSelects,
    customFields,
    consts,
    permission
}) => {
    const { agencyAccount } = useContext(ConstContext);

    return (
        <div
            className={classNames("userList", {
                show: isShow
            })}
        >
            <ul className="sideList half">
                <li>
                    <h2>
                        <span className="material-icons">business</span>
                        基本情報
                    </h2>
                    <table className="baseTable">
                        <tbody>
                            <tr>
                                <th>顧客番号</th>
                                <td>{user.user_number ?? "-"}</td>
                            </tr>
                            <tr>
                                <th>法人名(カナ)</th>
                                <td>
                                    {user.name ?? "-"}({user.name_kana ?? "-"})
                                </td>
                            </tr>
                            <tr>
                                <th>法人名英語表記</th>
                                <td>{user.name_roman ?? "-"}</td>
                            </tr>
                            <tr>
                                <th>電話番号</th>
                                <td>{user.tel ?? "-"}</td>
                            </tr>
                            <tr>
                                <th>FAX</th>
                                <td>{user.fax ?? "-"}</td>
                            </tr>
                            <tr>
                                <th>住所</th>
                                <td>
                                    {toPostFormat(user?.zip_code) ?? "-"}
                                    <br />
                                    {concatAdress(user)}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </li>
                <li>
                    <h2>
                        <span className="material-icons">
                            playlist_add_check
                        </span>
                        管理情報(カスタムフィールド)
                    </h2>
                    <table className="baseTable">
                        <tbody>
                            <tr>
                                <th>自社担当</th>
                                <td>{user.manager.name ?? "-"}</td>
                            </tr>
                            {customFields?.[
                                consts?.customFieldPositions?.custom
                            ] &&
                                Object.keys(
                                    customFields[
                                        consts.customFieldPositions.custom
                                    ]
                                ).map((key, index) => (
                                    <tr key={index}>
                                        <th>
                                            {
                                                customFields[
                                                    consts.customFieldPositions
                                                        .custom
                                                ][key]?.name
                                            }
                                        </th>
                                        <td>
                                            {customFields[
                                                consts.customFieldPositions
                                                    .custom
                                            ][key].val ?? "-"}
                                        </td>
                                    </tr>
                                ))}
                            <tr>
                                <th>一括支払契約</th>
                                <td>
                                    {formSelects.oneTimePayments[
                                        user.pay_altogether
                                    ] ?? "-"}
                                </td>
                            </tr>
                            <tr>
                                <th>備考</th>
                                <td>{user?.note ? nl2br(user.note) : "-"}</td>
                            </tr>
                        </tbody>
                    </table>
                </li>
            </ul>
            <ManagerArea
                isShow={isShow}
                userNumber={user?.user_number}
                dms={formSelects?.dms}
                sexes={formSelects?.sexes}
            />
            <ul id="formControl">
                <li className="wd50">
                    <button
                        className="grayBtn"
                        onClick={e => {
                            e.preventDefault();
                            window.location.href = `/${agencyAccount}/client/business/index`;
                        }}
                    >
                        <span className="material-icons">arrow_back_ios</span>
                        一覧に戻る
                    </button>
                </li>
                {permission.update && (
                    <li className="wd50">
                        <button
                            className="blueBtn"
                            onClick={e => {
                                e.preventDefault();
                                window.location.href = `/${agencyAccount}/client/business/${user?.user_number}/edit`;
                            }}
                        >
                            <span className="material-icons">edit_note</span>{" "}
                            顧客情報を編集する
                        </button>
                    </li>
                )}
            </ul>
        </div>
    );
};

export default CustomerArea;
