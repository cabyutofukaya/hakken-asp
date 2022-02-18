import React, { useContext } from "react";
import { ConstContext } from "../ConstApp";
import {
    concatAdress,
    nl2br,
    toPostFormat,
    birth2age
} from "../../../../helpers";
import classNames from "classnames";
import VisaArea from "./VisaArea";
import MileageArea from "./MileageArea";
import MemberCardArea from "./MemberCardArea";

/**
 *
 * @param {array} customFields カスタム項目情報（値、項目データ）
 * @param {array} consts 各種定数値
 * @returns
 */
const CustomerArea = ({
    isShow,
    customCategoryCode,
    user,
    formSelects,
    customFields,
    consts,
    permission
}) => {
    const { agencyAccount } = useContext(ConstContext);

    const EmergencyLabel = ({ name, col, emergencyContactColumn }) => {
        return (
            <>
                {name}
                {emergencyContactColumn === col && (
                    <span className="default">(緊急連絡先)</span>
                )}
            </>
        );
    };

    return (
        <div
            className={classNames("userList", {
                show: isShow
            })}
        >
            <ul className="sideList half">
                <li>
                    <h2>
                        <span className="material-icons">person</span>
                        基本情報
                    </h2>
                    <table className="baseTable">
                        <tbody>
                            <tr>
                                <th>顧客番号</th>
                                <td>{user.user_number ?? "-"}</td>
                            </tr>
                            <tr>
                                <th>氏名(カナ)</th>
                                <td>
                                    {user.userable.name ?? "-"}(
                                    {user.userable.name_kana ?? "-"})
                                </td>
                            </tr>
                            <tr>
                                <th>氏名ローマ字</th>
                                <td>{user.userable.name_roman ?? "-"}</td>
                            </tr>
                            <tr>
                                <th>性別</th>
                                <td>
                                    {formSelects.sexes?.[user.userable.sex] ??
                                        "-"}
                                </td>
                            </tr>
                            <tr>
                                <th>生年月日</th>
                                <td>
                                    {user.userable.birthday_y ?? "-"}/
                                    {user.userable.birthday_m ?? "-"}/
                                    {user.userable.birthday_d ?? "-"}(
                                    {birth2age(
                                        user.userable.birthday_y,
                                        user.userable.birthday_m,
                                        user.userable.birthday_d
                                    ) ?? "-"}
                                    )
                                </td>
                            </tr>
                            <tr>
                                <th>年齢区分</th>
                                <td>
                                    {formSelects.ageKbns?.[
                                        user.userable?.user_ext?.age_kbn
                                    ] ?? "-"}
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <EmergencyLabel
                                        name="携帯"
                                        col="mobile_phone"
                                        emergencyContactColumn={
                                            user.userable?.user_ext
                                                ?.emergency_contact_column
                                        }
                                    />
                                </th>
                                <td>{user.userable.mobile_phone ?? "-"}</td>
                            </tr>
                            <tr>
                                <th>
                                    <EmergencyLabel
                                        name="固定電話"
                                        col="tel"
                                        emergencyContactColumn={
                                            user.userable?.user_ext
                                                ?.emergency_contact_column
                                        }
                                    />
                                </th>
                                <td>{user.userable.tel ?? "-"}</td>
                            </tr>
                            <tr>
                                <th>FAX</th>
                                <td>{user.userable.fax ?? "-"}</td>
                            </tr>
                            <tr>
                                <th>メールアドレス</th>
                                <td>{user.userable.email ?? "-"}</td>
                            </tr>
                            {/*  緊急連絡先のカスタム項目 */}
                            {customFields?.[
                                consts?.customFieldPositions
                                    ?.position_person_emergency_contact
                            ] &&
                                Object.keys(
                                    customFields[
                                        consts.customFieldPositions
                                            .position_person_emergency_contact
                                    ]
                                ).map((key, index) => (
                                    <tr key={index}>
                                        <th>
                                            {
                                                customFields[
                                                    consts.customFieldPositions
                                                        .position_person_emergency_contact
                                                ][key]?.name
                                            }
                                        </th>
                                        <td>
                                            {customFields[
                                                consts.customFieldPositions
                                                    .position_person_emergency_contact
                                            ][key].val ?? "-"}
                                        </td>
                                    </tr>
                                ))}
                            <tr>
                                <th>住所</th>
                                <td>
                                    {toPostFormat(user.userable.zip_code) ??
                                        "-"}
                                    <br />
                                    {concatAdress(user)}
                                </td>
                            </tr>
                            <tr>
                                <th>旅券番号</th>
                                <td>{user.userable.passport_number ?? "-"}</td>
                            </tr>
                            <tr>
                                <th>旅券発行日</th>
                                <td>
                                    {user.userable.passport_issue_date ?? "-"}
                                </td>
                            </tr>
                            <tr>
                                <th>旅券有効期限</th>
                                <td>
                                    {user.userable.passport_expiration_date ??
                                        "-"}
                                </td>
                            </tr>
                            <tr>
                                <th>旅券発行国</th>
                                <td>
                                    {formSelects.countries?.[
                                        user.userable
                                            .passport_issue_country_code
                                    ] ?? "-"}
                                </td>
                            </tr>
                            <tr>
                                <th>国籍</th>
                                <td>
                                    {formSelects.countries?.[
                                        user.userable.citizenship_code
                                    ] ?? "-"}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </li>
                <li>
                    <h2>
                        <span className="material-icons">
                            perm_contact_calendar
                        </span>
                        勤務先/学校
                    </h2>
                    <table className="baseTable">
                        <tbody>
                            <tr>
                                <th>名称</th>
                                <td>{user.userable.workspace_name ?? "-"}</td>
                            </tr>
                            <tr>
                                <th>住所</th>
                                <td>
                                    {user.userable.workspace_address ?? "-"}
                                </td>
                            </tr>
                            <tr>
                                <th>電話番号</th>
                                <td>{user.userable.workspace_tel ?? "-"}</td>
                            </tr>
                            {/*  勤務先・学校のカスタム項目 */}
                            {customFields?.[
                                consts?.customFieldPositions
                                    ?.position_person_workspace_school
                            ] &&
                                Object.keys(
                                    customFields[
                                        consts.customFieldPositions
                                            .position_person_workspace_school
                                    ]
                                ).map((key, index) => (
                                    <tr key={index}>
                                        <th>
                                            {
                                                customFields[
                                                    consts.customFieldPositions
                                                        .position_person_workspace_school
                                                ][key]?.name
                                            }
                                        </th>
                                        <td>
                                            {customFields[
                                                consts.customFieldPositions
                                                    .position_person_workspace_school
                                            ][key].val ?? "-"}
                                        </td>
                                    </tr>
                                ))}
                            <tr>
                                <th>備考</th>
                                <td>
                                    {user.userable.workspace_note
                                        ? nl2br(user.userable.workspace_note)
                                        : "-"}
                                </td>
                            </tr>
                        </tbody>
                    </table>
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
                                <td>
                                    {user.userable?.user_ext?.manager?.name ??
                                        "-"}
                                </td>
                            </tr>
                            {/*  カスタムフィールドのカスタム項目 */}
                            {customFields?.[
                                consts?.customFieldPositions
                                    ?.position_person_custom_field
                            ] &&
                                Object.keys(
                                    customFields[
                                        consts.customFieldPositions
                                            .position_person_custom_field
                                    ]
                                ).map((key, index) => (
                                    <tr key={index}>
                                        <th>
                                            {
                                                customFields[
                                                    consts.customFieldPositions
                                                        .position_person_custom_field
                                                ][key]?.name
                                            }
                                        </th>
                                        <td>
                                            {customFields[
                                                consts.customFieldPositions
                                                    .position_person_custom_field
                                            ][key].val ?? "-"}
                                        </td>
                                    </tr>
                                ))}
                            <tr>
                                <th>DM</th>
                                <td>
                                    {formSelects.dms?.[
                                        user.userable?.user_ext?.dm
                                    ] ?? "-"}
                                </td>
                            </tr>
                            <tr>
                                <th>備考</th>
                                <td>
                                    {user.userable?.user_ext?.note
                                        ? nl2br(user.userable.user_ext.note)
                                        : "-"}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </li>
            </ul>

            <VisaArea
                userNumber={user?.user_number}
                countries={formSelects?.countries}
                customCategoryCode={customCategoryCode}
            />

            <MileageArea
                userNumber={user?.user_number}
                codeUserCustomerAirplaneCompanyKey={
                    consts.codeUserCustomerAirplaneCompanyKey
                }
                customFields={
                    formSelects.userCustomItems[
                        consts?.customFieldPositions
                            ?.position_person_mileage_modal
                    ]
                }
                customCategoryCode={customCategoryCode}
            />

            <MemberCardArea
                userNumber={user?.user_number}
                customCategoryCode={customCategoryCode}
            />

            <ul id="formControl">
                <li className="wd50">
                    <button
                        className="grayBtn"
                        onClick={e => {
                            window.location.href = `/${agencyAccount}/client/person/index`;
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
                                window.location.href = `/${agencyAccount}/client/person/${user?.user_number}/edit`;
                            }}
                        >
                            <span className="material-icons">edit_note</span>
                            顧客情報を編集する
                        </button>
                    </li>
                )}
            </ul>
        </div>
    );
};

export default CustomerArea;
