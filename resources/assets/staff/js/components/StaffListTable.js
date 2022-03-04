import React from "react";
import classNames from "classnames";
import ReactLoading from "react-loading";

const StaffListTable = ({
    agencyAccount,
    staffs,
    statuses,
    handleSortClick,
    isLoading
}) => {
    return (
        <table>
            <thead>
                <tr>
                    <th
                        className="sort"
                        onClick={e => handleSortClick("account")}
                    >
                        <span>アカウントID</span>
                    </th>
                    <th className="sort" onClick={e => handleSortClick("name")}>
                        <span>ユーザー名</span>
                    </th>
                    <th
                        className="sort"
                        onClick={e => handleSortClick("agency_role.name")}
                    >
                        <span>ユーザー権限</span>
                    </th>
                    <th
                        className="sort"
                        onClick={e => handleSortClick("email")}
                    >
                        <span>メールアドレス</span>
                    </th>
                    <th
                    // className="sort"
                    // onClick={e => handleSortClick("shozoku")}
                    >
                        <span>所属</span>
                    </th>
                    <th
                        className="sort"
                        onClick={e => handleSortClick("status")}
                    >
                        <span>アカウント状態</span>
                    </th>
                </tr>
            </thead>
            <tbody>
                {isLoading && (
                    <tr>
                        <td colSpan={6}>
                            <ReactLoading type={"bubbles"} color={"#dddddd"} />
                        </td>
                    </tr>
                )}
                {!isLoading && staffs.length === 0 && (
                    <tr>
                        <td colSpan={6}>ユーザーデータはありません</td>
                    </tr>
                )}

                {!isLoading &&
                    staffs.length > 0 &&
                    staffs.map(staff => (
                        <tr key={staff.id}>
                            <td>
                                <a
                                    href={`/${agencyAccount}/system/user/${staff.account}/edit`}
                                >
                                    {staff.account ?? "-"}
                                </a>
                            </td>
                            <td>{staff.name ?? "-"}</td>
                            <td>{staff.agency_role.name ?? "-"}</td>
                            <td>{staff.email ?? "-"}</td>
                            <td>{staff.shozoku.val ?? "-"}</td>
                            <td className="txtalc">
                                <span
                                    className={classNames("status", {
                                        green: staff.status == 1,
                                        gray: staff.status == 5
                                    })}
                                >
                                    {statuses?.[staff.status]}
                                </span>
                            </td>
                        </tr>
                    ))}
            </tbody>
        </table>
    );
};

export default StaffListTable;
