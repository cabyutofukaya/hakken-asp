import React, { useState, useEffect, useContext } from "react";
import { ConstContext } from "./components/ConstApp";
import ConstApp from "./components/ConstApp";
import { render } from "react-dom";
import PageNation from "./components/PageNation";
import SmallDangerModal from "./components/SmallDangerModal";
import DeclineMessage from "./components/DeclineMessage";
import SuccessMessage from "./components/SuccessMessage";
import { useMountedRef } from "../../hooks/useMountedRef";
import ReactLoading from "react-loading";
import StaffTd from "./components/StaffTd";
import moment from "moment";
import classNames from "classnames";
import SmallPrimaryModal from "./components/SmallPrimaryModal";

const ModelcourseList = ({ myId }) => {
    const { agencyAccount } = useContext(ConstContext);

    const mounted = useMountedRef(); // マウント・アンマウント制御

    // ページャー関連変数
    const [page, setPage] = useState(1);
    const [lastPage, setLastPage] = useState(0);
    const [sort, setSort] = useState("course_no");
    const [direction, setDirection] = useState("desc");
    const [total, setTotal] = useState(0);
    const perPage = 10; // 1ページ表示件数

    const [isLoading, setIsLoading] = useState(false); // 重複読み込み防止

    const [isShowChanging, setIsShowChanging] = useState(false); // 表示・非表示切り替え処理中か否か
    const [isDeleting, setIsDeleting] = useState(false); // 削除処理中か否か
    const [isCopying, setIsCopying] = useState(false); // コピー処理中か否か

    const [declineMessage, setDeclineMessage] = useState("");
    const [successMessage, setSuccessMessage] = useState("");

    const [sortParam, setSortParam] = useState({
        course_no: "desc",
        "departure.name": "desc",
        "destination.name": "desc",
        name: "desc",
        "author.name": "desc",
        price_per_ad: "desc",
        updated_at: "desc"
    });

    const [courses, setCourses] = useState([]); //一覧
    const [deleteId, setDeleteId] = useState(null); // 削除対象ID
    const [copyId, setCopyId] = useState(null); // コピー対象ID

    // ページネーションパラメータリセット
    const resertPagerParams = () => {
        setPage(1);
        setSort("course_no");
        setDirection("desc");
    };

    const fetch = async () => {
        if (!mounted.current) return;
        if (isLoading) return;

        setIsLoading(true);

        const response = await axios
            .get(`/api/${agencyAccount}/web/modelcourse/list`, {
                params: {
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
            setCourses(response.data.data);
            // ページャー関連
            setPage(response.data.meta.current_page);
            setLastPage(response.data.meta.last_page);
            setTotal(response.data.meta.total);
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

    // 削除アイコンをクリックした時の挙動(modalオープン)
    const handleDeleteClick = id => {
        setDeleteId(id);
    };

    // 削除ボタンを押した時の挙動
    const handleDelete = async e => {
        if (!mounted.current) return;
        if (isDeleting) return;

        setIsDeleting(true);
        const row = _.find(courses, ["id", deleteId]);

        const response = await axios
            .delete(`/api/${agencyAccount}/web/modelcourse/${deleteId}`)
            .finally(() => {
                $(".js-modal-close").trigger("click"); // 削除モーダルclose
                setTimeout(function() {
                    if (mounted.current) {
                        setIsDeleting(false);
                    }
                }, 3000);
            });

        if (response?.status == 200) {
            const newTotal = total - 1; // 削除処理後の件数
            const newPage = Math.ceil(newTotal / perPage);
            if (newPage < page) {
                // 現在のページ番号よりも総ページ数が少なくなった場合はページ番号を再設定。totalはapiにより更新される

                if (mounted.current) {
                    setPage(newPage);
                }
            } else {
                fetch(); // トータルのみの変更は手動fetch
            }

            if (mounted.current) {
                setDeclineMessage(
                    `モデルコース「${row.course_no}」を削除しました`
                );
            }
        }
    };

    // 複製アイコンをクリックした時の挙動(modalオープン)
    const handleCopyClick = id => {
        setCopyId(id);
    };

    // 複製ボタンを押した時の挙動
    const handleCopy = async e => {
        if (!mounted.current) return;
        if (isCopying) return;

        setIsCopying(true);
        const row = _.find(courses, ["id", copyId]);

        const response = await axios
            .post(`/api/${agencyAccount}/web/modelcourse/copy/${copyId}`, {
                author_id: myId
            })
            .finally(() => {
                $(".js-modal-close").trigger("click"); // 複製モーダルclose
                setTimeout(function() {
                    if (mounted.current) {
                        setIsCopying(false);
                    }
                }, 3000);
            });

        if (response) {
            // ページネーションパラメータが初期値でなければリセットしてリスト再取得。初期値のままであれば、同パラメータのままリスト再取得
            if (page != 1 || sort != "course_no" || direction != "desc") {
                resertPagerParams();
            } else {
                fetch();
            }

            if (mounted.current) {
                setSuccessMessage(
                    `モデルコース「${row.course_no}」をコピーしました`
                );
            }
        }
    };

    // 並び替えリンクをクリックした挙動
    const handleSortClick = column => {
        const direction = sortParam[column] === "asc" ? "desc" : "asc";
        setDirection(direction);
        setSort(column);

        setSortParam({ ...sortParam, [column]: direction });
    };

    // 表示・非表示切り替え
    const handleChangeShow = async (e, id) => {
        const list = _.cloneDeep(courses);
        const row = _.find(list, ["id", id]);
        if (row) {
            if (!mounted.current) return;

            setIsShowChanging(true);

            const show = row.show == 1 ? 0 : 1; // 更新値
            const response = await axios
                .post(`/api/${agencyAccount}/web/modelcourse/${id}/show`, {
                    show,
                    _method: "put"
                })
                .finally(() => {
                    setTimeout(function() {
                        if (mounted.current) {
                            setIsShowChanging(false);
                        }
                    }, 500); // 連続クリック防止のためちょっとだけインターバル
                });

            if (mounted.current && response?.status == 200) {
                row.show = show; // フラグ切り替え
                setCourses([...list]);
            }
        }
    };

    return (
        <>
            <SuccessMessage message={successMessage} />
            <DeclineMessage message={declineMessage} />
            <div className="tableCont managemnetTable">
                <table>
                    <thead>
                        <tr>
                            <th className="txtalc">
                                <span>WEB表示</span>
                            </th>
                            <th
                                className="sort"
                                onClick={e => handleSortClick("course_no")}
                            >
                                <span>モデルコースNo</span>
                            </th>
                            <th
                                className="sort"
                                onClick={e => handleSortClick("departure.name")}
                            >
                                <span>出発地</span>
                            </th>
                            <th
                                className="sort"
                                onClick={e =>
                                    handleSortClick("destination.name")
                                }
                            >
                                <span>目的地</span>
                            </th>
                            <th
                                className="sort"
                                onClick={e => handleSortClick("name")}
                            >
                                <span>モデルコース名</span>
                            </th>
                            <th
                                className="sort"
                                onClick={e => handleSortClick("author.name")}
                            >
                                <span>作成者</span>
                            </th>
                            {/* <th
                                className="sort"
                                onClick={e => handleSortClick("price_per_ad")}
                            >
                                <span>料金</span>
                            </th> */}
                            <th
                                className="sort"
                                onClick={e => handleSortClick("updated_at")}
                            >
                                <span>更新日</span>
                            </th>
                            <th className="txtalc">
                                <span>プレビュー</span>
                            </th>
                            <th className="txtalc">
                                <span>複製</span>
                            </th>
                            <th className="txtalc wd10">
                                <span>削除</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        {isLoading && (
                            <tr>
                                <td colSpan={10}>
                                    <ReactLoading
                                        type={"bubbles"}
                                        color={"#dddddd"}
                                    />
                                </td>
                            </tr>
                        )}
                        {!isLoading && courses.length === 0 && (
                            <tr>
                                <td colSpan={10}>
                                    モデルコースデータはありません
                                </td>
                            </tr>
                        )}
                        {!isLoading &&
                            courses.length > 0 &&
                            courses.map((row, index) => (
                                <tr key={index}>
                                    <td className="txtalc checkBox">
                                        <input
                                            type="checkbox"
                                            id={`show${index}`}
                                            value="1"
                                            checked={row?.show == 1}
                                            onChange={e =>
                                                handleChangeShow(e, row.id)
                                            }
                                            disabled={isShowChanging}
                                        />
                                        <label htmlFor={`show${index}`}>
                                            &nbsp;
                                        </label>
                                    </td>
                                    <td>
                                        <a
                                            href={`/${agencyAccount}/front/modelcourse/${row?.course_no}`}
                                        >
                                            {row.course_no ?? "-"}
                                        </a>
                                    </td>
                                    <td>{row.departure.name ?? "-"}</td>
                                    <td>{row.destination.name ?? "-"}</td>
                                    <td>{row.name ?? "-"}</td>
                                    <StaffTd
                                        name={row?.author?.name}
                                        isDeleted={row?.author?.is_deleted}
                                    />
                                    {/* <td>
                                        大人1名￥
                                        {(
                                            row.price_per_ad ?? 0
                                        ).toLocaleString()}
                                        　子供1名￥
                                        {(
                                            row.price_per_ch ?? 0
                                        ).toLocaleString()}
                                    </td> */}
                                    <td>
                                        {moment(row.updated_at).format(
                                            "YYYY/MM/DD"
                                        )}
                                    </td>
                                    <td className="txtalc">
                                        {row?.preview_url ? (
                                            <span className="material-icons">
                                                <a
                                                    href={row.preview_url}
                                                    target="_blank"
                                                >
                                                    launch
                                                </a>
                                            </span>
                                        ) : (
                                            "-"
                                        )}
                                    </td>
                                    <td className="txtalc">
                                        <span
                                            className={classNames(
                                                "material-icons",
                                                {
                                                    "js-modal-open": !isCopying
                                                }
                                            )}
                                            data-target="mdCopy"
                                            onClick={e => {
                                                handleCopyClick(row?.id);
                                            }}
                                        >
                                            file_copy
                                        </span>
                                    </td>
                                    <td className="txtalc">
                                        <span
                                            data-target="mdDeleteCourse"
                                            className={classNames(
                                                "material-icons",
                                                {
                                                    "js-modal-open": !isDeleting
                                                }
                                            )}
                                            onClick={e =>
                                                handleDeleteClick(row?.id)
                                            }
                                        >
                                            delete
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
            <SmallDangerModal
                id="mdDeleteCourse"
                title="このモデルコースを削除しますか？"
                handleAction={handleDelete}
                isActioning={isDeleting}
            />
            <SmallPrimaryModal
                id="mdCopy"
                title="このモデルコースを自身を作成者として複製しますか？"
                handleAction={handleCopy}
                actionLabel="複製する"
                isActioning={isDeleting}
            />
        </>
    );
};

const Element = document.getElementById("modelcourseList");
if (Element) {
    const myId = Element.getAttribute("myId");
    const jsVars = Element.getAttribute("jsVars");
    const parsedJsVars = jsVars && JSON.parse(jsVars);

    render(
        <ConstApp jsVars={parsedJsVars}>
            <ModelcourseList myId={myId} />
        </ConstApp>,
        document.getElementById("modelcourseList")
    );
}
