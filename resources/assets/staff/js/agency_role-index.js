import React, { useState, useEffect } from "react";
import { render } from "react-dom";
import PageNation from "./components/PageNation";

/**
 * ユーザー一覧
 *
 * @param {*} param0
 * @returns
 */
const AgencyRoleList = ({ agencyAccount }) => {
    // ページャー関連変数
    const [page, setPage] = useState(1);
    const [lastPage, setLastPage] = useState(0);
    const [sort, setSort] = useState("id");
    const [direction, setDirection] = useState("desc");
    const perPage = 4; // 1ページ表示件数

    const [isLoading, setIsLoading] = useState(false); // 重複読み込み防止

    const [sortParam, setSortParam] = useState({
        name: "asc",
        description: "asc",
        staffsCount: "asc"
    });

    const [agencyRoles, setAgencyRoles] = useState([]); //権限一覧

    const fetchList = () => {
        return axios.get(`/api/${agencyAccount}/listAgencyRoles`, {
            params: {
                page: page,
                sort: sort,
                direction: direction,
                per_page: perPage
            }
        });
    };

    const setData = data => {
        setAgencyRoles(data.data);
        // ページャー関連
        setPage(data.meta.current_page);
        setLastPage(data.meta.last_page);
    };

    useEffect(() => {
        let isMounted = true;

        const f = async () => {
            if (isLoading) return;
            setIsLoading(true);

            const response = await fetchList().finally(() => {
                if (isMounted) {
                    setIsLoading(false);
                }
            });
            if (response) {
                if (isMounted) {
                    const data = response.data;
                    setData(data);
                }
            }
        };

        f(); // リスト取得

        return () => {
            isMounted = false;
        };
    }, [page, sort, direction]);

    const handleSort = column => {
        const direction = sortParam[column] === "asc" ? "desc" : "asc";
        setDirection(direction);
        setSort(column);

        setSortParam({ ...sortParam, [column]: direction });
    };

    // ページリンクをクリックした挙動（ページネーションコンポーネント用）
    const handlePagerClick = (e, targetPage) => {
        e.preventDefault();
        setPage(targetPage);
    };

    return (
        <>
            {agencyRoles.length === 0 ? null : (
                <div className="tableCont">
                    <table>
                        <thead>
                            <tr>
                                <th
                                    className="sort"
                                    onClick={e => handleSort("name")}
                                >
                                    <span>権限名称</span>
                                </th>
                                <th
                                    className="sort"
                                    onClick={e => handleSort("description")}
                                >
                                    <span>説明</span>
                                </th>
                                <th
                                    className="sort txtalc wd10"
                                    onClick={e => handleSort("staffsCount")}
                                >
                                    <span>ユーザー数</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {agencyRoles.map(agencyRole => (
                                <tr key={agencyRole.id}>
                                    <td>
                                        <a
                                            href={`/${agencyAccount}/system/role/${agencyRole.id}/edit`}
                                        >
                                            {agencyRole.name}
                                        </a>
                                    </td>
                                    <td>{agencyRole.description}</td>
                                    <td className="txtalc">
                                        {agencyRole.staffs_count.toLocaleString()}
                                    </td>
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
            )}
        </>
    );
};

const Element = document.getElementById("agencyRoleList");
if (Element) {
    const agencyAccount = Element.getAttribute("agencyAccount");
    render(
        <AgencyRoleList agencyAccount={agencyAccount} />,
        document.getElementById("agencyRoleList")
    );
}
