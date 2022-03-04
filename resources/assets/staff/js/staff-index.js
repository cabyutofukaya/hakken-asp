import React, { useState, useEffect } from "react";
import { render } from "react-dom";
import PageNation from "./components/PageNation";
import StaffListTable from "./components/StaffListTable";

const StaffList = ({ agencyAccount, searchParam, formSelects }) => {
    // ページャー関連変数
    const [page, setPage] = useState(1);
    const [lastPage, setLastPage] = useState(0);
    const [sort, setSort] = useState("account");
    const [direction, setDirection] = useState("asc");
    const [total, setTotal] = useState(0);
    const perPage = 10; // 1ページ表示件数

    const [isLoading, setIsLoading] = useState(false); // 重複読み込み防止

    // TODO 所属を実装
    const [sortParam, setSortParam] = useState({
        account: "asc",
        name: "asc",
        "agency_role.name": "asc",
        // shozokus: "asc",
        email: "asc",
        state: "asc"
    });

    const [staffs, setStaffs] = useState([]); //スタッフ一覧

    const fetch = async () => {
        if (isLoading) return;

        setIsLoading(true);

        const response = await axios
            .get(`/api/${agencyAccount}/staff/list`, {
                params: {
                    ...searchParam,
                    page: page,
                    sort: sort,
                    direction: direction,
                    per_page: perPage
                }
            })
            .finally(() => {
                setIsLoading(false);
            });
        if (response) {
            const data = response.data;

            setStaffs(data.data);
            // ページャー関連
            setPage(data.meta.current_page);
            setLastPage(data.meta.last_page);
            setTotal(data.meta.total);
        }
    };

    useEffect(() => {
        fetch(); // 一覧データ取得
    }, [page, sort, direction]);

    // ページリンクをクリックした挙動（ページネーションコンポーネント用）
    const handlePagerClick = (e, targetPage) => {
        e.preventDefault();
        setPage(targetPage);
    };

    // 並び替えリンクをクリックした挙動（StaffListTable用）
    const handleSortClick = column => {
        const direction = sortParam[column] === "asc" ? "desc" : "asc";
        setDirection(direction);
        setSort(column);

        setSortParam({ ...sortParam, [column]: direction });
    };

    return (
        <>
            <div className="tableCont">
                <StaffListTable
                    agencyAccount={agencyAccount}
                    staffs={staffs}
                    statuses={formSelects?.statuses}
                    handleSortClick={handleSortClick}
                    isLoading={isLoading}
                />
                {lastPage > 1 && (
                    <PageNation
                        currentPage={page}
                        lastPage={lastPage}
                        onClick={handlePagerClick}
                    />
                )}
            </div>
        </>
    );
};

const Element = document.getElementById("staffList");
if (Element) {
    const agencyAccount = Element.getAttribute("agencyAccount");
    const searchParam = Element.getAttribute("searchParam");
    const parsedSearchParam = searchParam && JSON.parse(searchParam);
    const formSelects = Element.getAttribute("formSelects");
    const parsedFormSelects = formSelects && JSON.parse(formSelects);
    render(
        <StaffList
            agencyAccount={agencyAccount}
            searchParam={parsedSearchParam}
            formSelects={parsedFormSelects}
        />,
        document.getElementById("staffList")
    );
}
