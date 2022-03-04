/**
 * 権限対象チェックボックスの全選択⇆全解除
 */
$(() => {
    // 全選択
    $("[data-target_on]").on("click", function() {
        const target = $(this).data("target_on");
        $(`[data-target='${target}']`)
            .find("input[type=checkbox]:enabled")
            .not(":disabled") // 操作対象は有効要素のみ
            .prop("checked", true);
    });
    // 全解除
    $("[data-target_off]").on("click", function() {
        const target = $(this).data("target_off");
        $(`[data-target='${target}']`)
            .find("input[type=checkbox]")
            .not(":disabled") // 操作対象は有効要素のみ
            .prop("checked", false);
    });
});

import React, { useState, useEffect } from "react";
import { render } from "react-dom";
import PageNation from "./components/PageNation";
import StaffListTable from "./components/StaffListTable";
import { useMountedRef } from "../../hooks/useMountedRef";

/**
 * ユーザー一覧
 *
 * @param {*} param0
 * @returns
 */
const StaffIndex = ({ agencyAccount, title, searchParam, formSelects }) => {
    const mounted = useMountedRef(); // マウント・アンマウント制御

    // ページャー関連変数
    const [page, setPage] = useState(0);
    const [lastPage, setLastPage] = useState(0);
    const [sort, setSort] = useState("id");
    const [direction, setDirection] = useState("desc");
    const [total, setTotal] = useState(0);
    const perPage = 4; // 1ページ表示件数

    const [isLoading, setIsLoading] = useState(false); // 重複読み込み防止

    const [sortParam, setSortParam] = useState({
        account: "asc",
        name: "asc",
        "agency_role.name": "asc",
        shozoku: "asc",
        email: "asc",
        state: "asc"
    });

    const [staffs, setStaffs] = useState([]); //スタッフ一覧

    const fetch = async () => {
        if (!mounted.current) return;
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
                if (mounted.current) {
                    setIsLoading(false);
                }
            });
        if (mounted.current && response?.data?.data) {
            setStaffs(response.data.data);
            // ページャー関連
            setPage(response.data.meta.current_page);
            setLastPage(response.data.meta.last_page);
            setTotal(response.data.meta.total);
        }
    };

    useEffect(() => {
        fetch(); // 一覧取得
    }, [page, sort, direction]);

    // ページリンクをクリックした挙動（PageNation用）
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
            {staffs.length === 0 ? null : (
                <>
                    <h2 className="subTit">
                        <span className="material-icons">person</span>
                        {title}
                    </h2>
                    <div className="tableWrap dragTable">
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
                    </div>
                </>
            )}
        </>
    );
};

const Element = document.getElementById("staffIndex");
if (Element) {
    const agencyAccount = Element.getAttribute("agencyAccount");
    const title = Element.getAttribute("title");
    const searchParam = Element.getAttribute("searchParam");
    const parsedSearchParam = searchParam && JSON.parse(searchParam);
    const formSelects = Element.getAttribute("formSelects");
    const parsedFormSelects = formSelects && JSON.parse(formSelects);
    render(
        <StaffIndex
            agencyAccount={agencyAccount}
            title={title}
            searchParam={parsedSearchParam}
            formSelects={parsedFormSelects}
        />,
        document.getElementById("staffIndex")
    );
}
