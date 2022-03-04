import React, { useEffect, useState, useContext } from "react";
import { ConstContext } from "../ConstApp";
import { useMountedRef } from "../../../../hooks/useMountedRef";
import ConsultationModal from "../ConsultationModal";
import classNames from "classnames";
import PageNation from "../PageNation";
import StaffTd from "../StaffTd";
import ReactLoading from "react-loading";

// 一覧取得API URL
const getListApiUrl = (
    step,
    types,
    agencyAccount,
    estimateNumber,
    reserveNumber,
    targetConsultationNumber
) => {
    // TODO 対象の見積もりを絞り込む機能をつけようと思ったけど、URLをリセットする仕組みがないので一旦ナシ
    // const param = targetConsultationNumber
    //     ? `?control_number=${targetConsultationNumber}`
    //     : "";
    const param = "";

    switch (step) {
        case types.application_step_draft: // 見積
            return `/api/${agencyAccount}/estimate/${step}/${estimateNumber}/consultation/list${param}`;
        case types.application_step_reserve: // 予約
            return `/api/${agencyAccount}/estimate/${step}/${reserveNumber}/consultation/list${param}`;
        default:
            return null;
    }
};

// 作成API URL
const storeApiUrl = (
    step,
    types,
    agencyAccount,
    estimateNumber,
    reserveNumber
) => {
    switch (step) {
        case types.application_step_draft: // 見積
            return `/api/${agencyAccount}/estimate/${step}/${estimateNumber}/consultation`;
        case types.application_step_reserve: // 予約
            return `/api/${agencyAccount}/estimate/${step}/${reserveNumber}/consultation`;
        default:
            return null;
    }
};

// 更新API URL
const updateApiUrl = (
    step,
    types,
    agencyAccount,
    estimateNumber,
    reserveNumber,
    consulNumber
) => {
    switch (step) {
        case types.application_step_draft: // 見積
            return `/api/${agencyAccount}/estimate/${step}/${estimateNumber}/consultation/${consulNumber}`;
        case types.application_step_reserve: // 予約
            return `/api/${agencyAccount}/estimate/${step}/${reserveNumber}/consultation/${consulNumber}`;
        default:
            return null;
    }
};

/**
 *
 * @param {bool} isShow 本コンポーネントが表示状態か否か
 * @returns
 */
const ConsultationArea = ({
    isShow,
    targetConsultationNumber,
    applicationStep,
    applicationStepList,
    estimateNumber,
    reserveNumber,
    defaultValue,
    formSelects,
    consts,
    permission
}) => {
    const { agencyAccount } = useContext(ConstContext);

    const mounted = useMountedRef(); // マウント・アンマウント制御

    // ページャー関連変数
    const [page, setPage] = useState(1);
    const [lastPage, setLastPage] = useState(0);
    const [sort, setSort] = useState("created_at");
    const [direction, setDirection] = useState("desc");
    const [total, setTotal] = useState(0);
    const perPage = 5; // 1ページ表示件数

    const [sortParam, setSortParam] = useState({
        control_number: "asc",
        reception_date: "asc",
        title: "asc",
        kind: "asc",
        deadline: "asc",
        "manager.name": "asc",
        status: "asc"
    });

    const [rows, setRows] = useState([]);

    const [input, setInput] = useState({}); // 入力値
    const [editMode, setEditMode] = useState(null); // モーダル表示時の登録or編集を判定

    const [isLoading, setIsLoading] = useState(false); // リスト取得中
    const [isEditing, setIsEditing] = useState(false); // 編集処理中

    // 一覧取得
    const fetch = async () => {
        if (!mounted.current) return;
        if (isLoading) return;

        setIsLoading(true); // 二重読み込み禁止

        const response = await axios
            .get(
                getListApiUrl(
                    applicationStep,
                    applicationStepList,
                    agencyAccount,
                    estimateNumber,
                    reserveNumber,
                    targetConsultationNumber
                ),
                {
                    params: {
                        page: page,
                        sort: sort,
                        direction: direction,
                        per_page: perPage
                    }
                }
            )
            .finally(() => {
                if (mounted.current) {
                    setIsLoading(false);
                }
            });
        if (mounted.current && response?.data?.data) {
            setRows([...response.data.data]);
            // ページャー関連
            setPage(response.data.meta.current_page);
            setLastPage(response.data.meta.last_page);
            setTotal(response.data.meta.total);
        }
    };

    useEffect(() => {
        fetch(); // 一覧取得
    }, [isShow, page, sort, direction]);

    // ページリンクをクリックした挙動（ページネーションコンポーネント用）
    const handlePagerClick = (e, targetPage) => {
        e.preventDefault();
        setPage(targetPage);
    };

    // 入力値変更制御
    const handleChange = e => {
        setInput({ ...input, [e.target.name]: e.target.value });
    };

    // 追加モーダル表示
    const handleModalAdd = e => {
        e.preventDefault();
        setEditMode("create");
        setInput({ ...defaultValue }); // 初期値をセット
    };

    // 編集モーダル
    const handleModalEdit = (e, controlNumber) => {
        e.preventDefault();
        setEditMode("edit");

        const row = _.find(rows, { control_number: controlNumber });
        setInput({
            ...row,
            manager_id: row?.manager?.id ?? "",
            status: row?.status ?? defaultValue?.status,
            kind: row?.kind ?? defaultValue?.kind
        }); // ステータスと種別は値が無ければ初期化
    };

    // 追加処理
    const handleSubmit = async e => {
        e.preventDefault();

        if (!mounted.current) return;
        if (isEditing) return;
        setIsEditing(true); // 多重処理制御

        let response = null;
        if (editMode === "create") {
            // 新規登録
            response = await axios
                .post(
                    storeApiUrl(
                        applicationStep,
                        applicationStepList,
                        agencyAccount,
                        estimateNumber,
                        reserveNumber
                    ),
                    {
                        ...input
                    }
                )
                .finally(() => {
                    $(".js-modal-close").trigger("click"); // モーダルclose
                    setTimeout(function() {
                        if (mounted.current) {
                            setIsEditing(false);
                        }
                    }, 3000);
                });
        } else if (editMode === "edit") {
            // 編集
            if (!input) return;
            response = await axios
                .post(
                    updateApiUrl(
                        applicationStep,
                        applicationStepList,
                        agencyAccount,
                        estimateNumber,
                        reserveNumber,
                        input.control_number
                    ),
                    {
                        ...input,
                        _method: "put"
                    }
                )
                .finally(() => {
                    $(".js-modal-close").trigger("click"); // モーダルclose
                    setTimeout(function() {
                        if (mounted.current) {
                            setIsEditing(false);
                        }
                    }, 3000);
                });
        }

        if (response) {
            fetch(); // リスト再取得
        }
    };

    // 並び替えリンクをクリックした挙動
    const handleSortClick = column => {
        const direction = sortParam[column] === "asc" ? "desc" : "asc";
        setDirection(direction);
        setSort(column);

        setSortParam({ ...sortParam, [column]: direction });
    };

    return (
        <>
            <div
                className={classNames("userList", {
                    show: isShow
                })}
            >
                <h2>
                    <span className="material-icons">question_answer</span>
                    相談一覧
                </h2>
                {permission?.consultation_create && (
                    <ul className="clientContNav">
                        <li>
                            <button
                                data-target="mdAddConsul"
                                className="addBtn js-modal-open"
                                onClick={handleModalAdd}
                            >
                                <span className="material-icons">add</span>
                                新規相談追加
                            </button>
                        </li>
                    </ul>
                )}
                <div className="tableWrap dragTable">
                    <div className="tableCont pt00">
                        <table>
                            <thead>
                                <tr>
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
                                    <th
                                        className="sort"
                                        onClick={e => handleSortClick("kind")}
                                    >
                                        <span>種別</span>
                                    </th>
                                    <th
                                        className="sort"
                                        onClick={e =>
                                            handleSortClick("deadline")
                                        }
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
                            {permission?.consultation_read && (
                                <tbody>
                                    {isLoading && (
                                        <tr>
                                            <td colSpan={7}>
                                                <ReactLoading
                                                    type={"bubbles"}
                                                    color={"#dddddd"}
                                                />
                                            </td>
                                        </tr>
                                    )}
                                    {!isLoading && rows.length === 0 && (
                                        <tr>
                                            <td colSpan={7}>
                                                相談データはありません
                                            </td>
                                        </tr>
                                    )}
                                    {!isLoading &&
                                        rows.length > 0 &&
                                        rows.map((row, index) => (
                                            <tr key={index}>
                                                <td>
                                                    <a
                                                        data-target="mdAddConsul"
                                                        className={classNames({
                                                            "js-modal-open": !isEditing
                                                        })}
                                                        onClick={e =>
                                                            handleModalEdit(
                                                                e,
                                                                row.control_number
                                                            )
                                                        }
                                                    >
                                                        {row.control_number ??
                                                            "-"}
                                                    </a>
                                                </td>
                                                <td>
                                                    {row.reception_date ?? "-"}
                                                </td>
                                                <td>{row.title ?? "-"}</td>
                                                <td>{row.kind_label ?? "-"}</td>
                                                <td>{row.deadline ?? "-"}</td>
                                                <StaffTd
                                                    name={row?.manager?.name}
                                                    isDeleted={
                                                        row?.manager?.is_deleted
                                                    }
                                                />
                                                <td className="txtalc">
                                                    <span
                                                        className={classNames({
                                                            status: row?.status,
                                                            blue:
                                                                row?.status ==
                                                                consts
                                                                    ?.statusList
                                                                    .status_reception,
                                                            green:
                                                                row?.status ==
                                                                consts
                                                                    ?.statusList
                                                                    .status_responding
                                                        })}
                                                    >
                                                        {row.status_label ??
                                                            "-"}
                                                    </span>
                                                </td>
                                            </tr>
                                        ))}
                                </tbody>
                            )}
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
            </div>
            <ConsultationModal
                id="mdAddConsul"
                input={input}
                modalMode={editMode}
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

export default ConsultationArea;
