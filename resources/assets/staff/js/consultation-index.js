import React, { useState, useEffect, useContext } from "react";
import { ConstContext } from "./components/ConstApp";
import { render } from "react-dom";
import ConstApp from "./components/ConstApp";
import PageNation from "./components/PageNation";
import DeclineMessage from "./components/DeclineMessage";
import { useMountedRef } from "../../hooks/useMountedRef";
import ReactLoading from "react-loading";
import StaffTd from "./components/StaffTd";
import classNames from "classnames";
import ConsultationModal from "./components/ConsultationModal";

const ConsultationList = ({
    defaultValue,
    searchParam,
    consts,
    formSelects
}) => {
    const { agencyAccount } = useContext(ConstContext);

    const mounted = useMountedRef(); // マウント・アンマウント制御

    // ページャー関連変数
    const [page, setPage] = useState(1);
    const [lastPage, setLastPage] = useState(0);
    const [sort, setSort] = useState("control_number");
    const [direction, setDirection] = useState("desc");
    const [total, setTotal] = useState(0);
    const perPage = 10; // 1ページ表示件数

    const [isLoading, setIsLoading] = useState(false); // 重複読み込み防止
    const [isEditing, setIsEditing] = useState(false); // 編集処理中

    const [declineMessage, setDeclineMessage] = useState("");

    // TODO 申込者のソートはつけなくて良いか相談
    const [sortParam, setSortParam] = useState({
        "reserve.reserve_estimate_number": "desc",
        control_number: "desc",
        reception_date: "desc",
        title: "desc",
        kind: "desc",
        deadline: "desc",
        "manager.name": "desc",
        status: "desc"
    });

    const [lists, setLists] = useState([]); //一覧
    const [input, setInput] = useState({}); // 入力値

    // 一覧取得
    const fetch = async () => {
        if (!mounted.current) return;
        if (isLoading) return;

        setIsLoading(true);

        const response = await axios
            .get(`/api/${agencyAccount}/consultation/list`, {
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
            setLists(response.data.data);
            // ページャー関連
            setPage(response.data.meta.current_page);
            setLastPage(response.data.meta.last_page);
            setTotal(response.data.meta.total);
        }
    };

    // 編集処理
    const handleSubmit = async e => {
        e.preventDefault();

        if (!mounted.current) return;
        if (isEditing) return;
        if (!input) return;

        setIsEditing(true); // 多重処理制御

        const response = await axios
            .post(`/api/${agencyAccount}/consultation/${input.id}`, {
                ...input,
                _method: "put"
            })
            .finally(() => {
                $(".js-modal-close").trigger("click"); // モーダルclose
                setTimeout(function() {
                    if (mounted.current) {
                        setIsEditing(false);
                    }
                }, 3000);
            });

        if (response) {
            fetch(); // リスト再取得
        }
    };

    useEffect(() => {
        fetch(); // 一覧取得
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

    // 入力値変更制御
    const handleChange = e => {
        setInput({ ...input, [e.target.name]: e.target.value });
    };

    // 編集モーダル
    const handleModalEdit = (e, id) => {
        e.preventDefault();

        // 相談番号がない場合があるのでidで検索
        const row = _.find(lists, { id: id });
        setInput({
            ...row,
            manager_id: row?.manager?.id ?? "",
            status: row?.status ?? defaultValue?.status,
            kind: row?.kind ?? defaultValue?.kind
        }); // ステータスと種別は値が無ければ初期化
    };

    return (
        <>
            <div className="tableWrap dragTable">
                <DeclineMessage message={declineMessage} />
                <div className="tableCont">
                    <table>
                        <thead>
                            <tr>
                                <th
                                    className="sort"
                                    onClick={e =>
                                        handleSortClick(
                                            "reserve.reserve_estimate_number"
                                        )
                                    }
                                >
                                    <span>見積/予約番号</span>
                                </th>
                                <th
                                    className="sort"
                                    onClick={e =>
                                        handleSortClick("control_number")
                                    }
                                >
                                    <span>相談番号</span>
                                </th>
                                <th
                                    className="sort"
                                    onClick={e =>
                                        handleSortClick("reception_date")
                                    }
                                >
                                    <span>受付日</span>
                                </th>
                                <th
                                    className="sort"
                                    onClick={e => handleSortClick("title")}
                                >
                                    <span>タイトル</span>
                                </th>
                                <th>
                                    <span>申込者</span>
                                </th>
                                <th
                                    className="sort"
                                    onClick={e => handleSortClick("kind")}
                                >
                                    <span>種別</span>
                                </th>
                                <th
                                    className="sort"
                                    onClick={e => handleSortClick("deadline")}
                                >
                                    <span>期限</span>
                                </th>
                                <th
                                    className="sort"
                                    onClick={e =>
                                        handleSortClick("manager.name")
                                    }
                                >
                                    <span>自社担当</span>
                                </th>
                                <th
                                    className="sort txtalc"
                                    onClick={e => handleSortClick("status")}
                                >
                                    <span>ステータス</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {isLoading && (
                                <tr>
                                    <td colSpan={9}>
                                        <ReactLoading
                                            type={"bubbles"}
                                            color={"#dddddd"}
                                        />
                                    </td>
                                </tr>
                            )}
                            {!isLoading && lists.length === 0 && (
                                <tr>
                                    <td colSpan={9}>相談データはありません</td>
                                </tr>
                            )}
                            {!isLoading &&
                                lists.length > 0 &&
                                lists.map((row, index) => (
                                    <tr key={index}>
                                        <td>
                                            {/**見積 */}
                                            {row.taxonomy ===
                                                consts.taxonomyList
                                                    .taxonomy_reserve &&
                                                row.reserve
                                                    ?.application_step ===
                                                    consts.applicationStepList
                                                        .application_step_draft && (
                                                    <a
                                                        href={`/${agencyAccount}/estimates/normal/${row.reserve?.estimate_number}?tab=${consts.reserveTabCodes.tab_consultation}&consultation_number=${row?.control_number}`}
                                                    >
                                                        {row.reserve
                                                            ?.estimate_number ??
                                                            "-"}
                                                    </a>
                                                )}
                                            {/**予約 */}
                                            {row.taxonomy ===
                                                consts.taxonomyList
                                                    .taxonomy_reserve &&
                                                row.reserve
                                                    ?.application_step ===
                                                    consts.applicationStepList
                                                        .application_step_reserve && (
                                                    <a
                                                        href={`/${agencyAccount}/estimates/reserve/${row.reserve?.control_number}?tab=${consts.reserveTabCodes.tab_consultation}&consultation_number=${row?.control_number}`}
                                                    >
                                                        {row.reserve
                                                            ?.control_number ??
                                                            "-"}
                                                    </a>
                                                )}
                                        </td>
                                        <td>
                                            <a
                                                href="#"
                                                data-target="mdAddConsul"
                                                className={classNames({
                                                    "js-modal-open": !isEditing
                                                })}
                                                onClick={e =>
                                                    handleModalEdit(e, row.id)
                                                }
                                            >
                                                {row.control_number ?? "-"}
                                            </a>
                                        </td>
                                        <td>{row.reception_date ?? "-"}</td>
                                        <td>{row.title ?? "-"}</td>
                                        <td
                                            className={classNames({
                                                txcGray:
                                                    row.reserve?.applicant
                                                        .is_deleted
                                            })}
                                        >
                                            {row.reserve?.applicant.name ?? "-"}
                                        </td>
                                        <td>{row.kind_label ?? "-"}</td>
                                        <td>{row.deadline ?? "-"}</td>
                                        <StaffTd
                                            name={row?.manager?.name}
                                            isDeleted={row?.manager?.is_deleted}
                                        />
                                        <td className="txtalc">
                                            <span
                                                className={classNames({
                                                    status: row?.status,
                                                    blue:
                                                        row?.status ==
                                                        consts?.statusList
                                                            .status_reception,
                                                    green:
                                                        row?.status ==
                                                        consts?.statusList
                                                            .status_responding
                                                })}
                                            >
                                                {row.status_label ?? "-"}
                                            </span>
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
            </div>
            <ConsultationModal
                id="mdAddConsul"
                input={input}
                modalMode={"edit"}
                handleSubmit={handleSubmit}
                isEditing={isEditing}
                handleChange={handleChange}
                staffs={formSelects?.staffs}
                statuses={formSelects?.statuses}
                kinds={formSelects?.kinds}
                customFields={formSelects?.userCustomItems?.consultation_custom}
            />
        </>
    );
};

const Element = document.getElementById("consultationList");
if (Element) {
    const jsVars = Element.getAttribute("jsVars");
    const parsedJsVars = jsVars && JSON.parse(jsVars);
    const defaultValue = Element.getAttribute("defaultValue");
    const parsedDefaultValue = defaultValue && JSON.parse(defaultValue);
    const searchParam = Element.getAttribute("searchParam");
    const parsedSearchParam = searchParam && JSON.parse(searchParam);
    const formSelects = Element.getAttribute("formSelects");
    const parsedFormSelects = formSelects && JSON.parse(formSelects);
    const consts = Element.getAttribute("consts");
    const parsedConsts = consts && JSON.parse(consts);

    render(
        <ConstApp jsVars={parsedJsVars}>
            <ConsultationList
                defaultValue={parsedDefaultValue}
                searchParam={parsedSearchParam}
                formSelects={parsedFormSelects}
                consts={parsedConsts}
            />
        </ConstApp>,
        document.getElementById("consultationList")
    );
}
