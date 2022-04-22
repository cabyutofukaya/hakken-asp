import React, { useState, useEffect, useContext } from "react";
import { ConstContext } from "./components/ConstApp";
import ConstApp from "./components/ConstApp";
import { render } from "react-dom";
import PageNation from "./components/PageNation";
import DeclineMessage from "./components/DeclineMessage";
import { useMountedRef } from "../../hooks/useMountedRef";
import ReactLoading from "react-loading";
import StaffTd from "./components/StaffTd";
import SmallDangerModal from "./components/SmallDangerModal";
import classNames from "classnames";
import ConsultTd from "./components/Reserve/ConsultTd";
import OnlineRequestModal from "./portal/OnlineRequestModal";
import VideoTitArea from "./components/Reserve/VideoTitArea";
import { useOnlineRequest } from "../../hooks/useOnlineRequest";
import StatusTd from "./components/Reserve/StatusTd";

const ReserveList = ({ searchParam, consts }) => {
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
    const [isDeleting, setIsDeleting] = useState(false); // 削除処理中か否か

    const [declineMessage, setDeclineMessage] = useState("");

    /**オンライン相談関連 */
    // 更新された相談日程で古い相談日程を書き換え
    const updateOnlineSchedule = schedule => {
        // Web予約行に相談日程情報をセット
        for (let i = 0; i < lists.length; i++) {
            const webReserveExtId = _.get(lists[i], "web_reserve_ext.id");
            if (webReserveExtId == schedule.web_reserve_ext_id) {
                lists[i].web_reserve_ext.web_online_schedule = _.cloneDeep(
                    schedule
                );
                setLists([...lists]);
                break;
            }
        }
    };
    const [
        isOnlineRequesting,
        onlineRequestValues,
        onlineRequestInputValues,
        handleOnlineRequestInputChange,
        handleOnlineRequestClick,
        handleChangeOnlineRequest,
        handleConsentRequest
    ] = useOnlineRequest(agencyAccount, updateOnlineSchedule);

    // TODO 申込者のソートはつけなくて良いか相談
    const [sortParam, setSortParam] = useState({
        control_number: "desc",
        status: "desc",
        "manager.name": "desc",
        departure_date: "desc",
        return_date: "desc",
        "departure.name": "desc",
        "destination.name": "desc",
        representative_name: "desc",
        name: "desc",
        travel_type: "desc",
        application_date: "desc",
        web_online_schedule: "desc",
        application_type: "desc"
    });

    const [lists, setLists] = useState([]); //一覧
    const [deleteNumber, setDeleteNumber] = useState(null); // 削除対象ID

    // 一覧取得
    const fetch = async () => {
        if (!mounted.current) return;
        if (isLoading) return;

        setIsLoading(true);

        const response = await axios
            .get(`/api/${agencyAccount}/web/reserve/list`, {
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
            setDeleteNumber(null);
            setLists(response.data.data);
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

    // 並び替えリンクをクリックした挙動
    const handleSortClick = column => {
        const direction = sortParam[column] === "asc" ? "desc" : "asc";
        setDirection(direction);
        setSort(column);

        setSortParam({ ...sortParam, [column]: direction });
    };

    // 削除を押した時の挙動
    const handleDelete = async e => {
        if (!mounted.current) return;
        if (isDeleting) return;

        setIsDeleting(true);

        const response = await axios
            .delete(`/api/${agencyAccount}/web/reserve/${deleteNumber}`)
            .finally(() => {
                $(".js-modal-close").trigger("click"); // 削除モーダルclose
                setTimeout(function() {
                    if (mounted.current) {
                        setIsDeleting(false);
                    }
                }, 3000);
            });

        if (response) {
            const newTotal = total - 1; // 削除処理後の件数
            const newPage = Math.ceil(newTotal / perPage);

            if (newPage < page) {
                // 現在のページ番号よりも総ページ数が少なくなった場合はページ番号を再設定。totalはapiにより更新される
                setPage(newPage);
            } else {
                fetch(); // トータルのみの変更は手動fetch
            }

            setDeclineMessage("予約データの削除が完了しました");
        }
    };

    return (
        <>
            <DeclineMessage message={declineMessage} />
            <div className="tableCont">
                <table>
                    <thead>
                        <tr>
                            <th
                                className="sort"
                                onClick={e => handleSortClick("control_number")}
                            >
                                <span>予約番号</span>
                            </th>
                            <th
                                className="sort txtalc"
                                onClick={e => handleSortClick("status")}
                            >
                                <span>ステータス</span>
                            </th>
                            <th
                                className="sort"
                                onClick={e => handleSortClick("manager.name")}
                            >
                                <span>自社担当</span>
                            </th>
                            <th
                                className="sort txtalc"
                                onClick={e =>
                                    handleSortClick(
                                        "web_reserve_ext.agency_unread_count"
                                    )
                                }
                            >
                                <span>相談履歴</span>
                            </th>
                            <th
                                className="sort txtalc"
                                onClick={e =>
                                    handleSortClick("web_online_schedule")
                                }
                            >
                                <span>オンライン相談</span>
                            </th>
                            <th
                                className="sort"
                                onClick={e => handleSortClick("departure_date")}
                            >
                                <span>出発日</span>
                            </th>
                            <th
                                className="sort"
                                onClick={e => handleSortClick("return_date")}
                            >
                                <span>帰着日</span>
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
                            <th>
                                <span>申込者</span>
                            </th>
                            <th
                                className="sort"
                                onClick={e =>
                                    handleSortClick("representative_name")
                                }
                            >
                                <span>代表参加者</span>
                            </th>
                            <th
                                className="sort"
                                onClick={e => handleSortClick("name")}
                            >
                                <span>旅行名</span>
                            </th>
                            <th
                                className="sort"
                                onClick={e => handleSortClick("travel_type")}
                            >
                                <span>旅行種別</span>
                            </th>
                            <th
                                className="sort"
                                onClick={e =>
                                    handleSortClick("application_type")
                                }
                            >
                                <span>申込種別</span>
                            </th>
                            <th
                                className="sort"
                                onClick={e =>
                                    handleSortClick("application_date")
                                }
                            >
                                <span>申込日</span>
                            </th>
                            <th className="txtalc wd10">
                                <span>削除</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        {isLoading && (
                            <tr>
                                <td colSpan={16}>
                                    <ReactLoading
                                        type={"bubbles"}
                                        color={"#dddddd"}
                                    />
                                </td>
                            </tr>
                        )}
                        {!isLoading && lists.length === 0 && (
                            <tr>
                                <td colSpan={16}>予約データはありません</td>
                            </tr>
                        )}
                        {!isLoading &&
                            lists.length > 0 &&
                            lists.map((row, index) => (
                                <tr key={index}>
                                    <td>
                                        <a href={row.detail_url}>
                                            {row.control_number ?? "-"}
                                        </a>
                                    </td>
                                    <td className="txtalc">
                                        <StatusTd status={row?.status?.val} />
                                    </td>
                                    <StaffTd
                                        name={row?.manager?.name}
                                        isDeleted={row?.manager?.is_deleted}
                                    />
                                    <ConsultTd
                                        consultationUrl={
                                            row?.web_reserve_ext
                                                ?.consultation_url
                                        }
                                        agencyUnreadCount={
                                            row?.web_reserve_ext
                                                ?.agency_unread_count ?? 0
                                        }
                                    />
                                    <td className="txtalc">
                                        {row?.web_reserve_ext
                                            ?.web_online_schedule
                                            ?.consult_date && (
                                            <VideoTitArea
                                                webReserveExt={
                                                    row.web_reserve_ext
                                                }
                                                senderTypes={consts.senderTypes}
                                                onlineRequestStatuses={
                                                    consts.onlineRequestStatuses
                                                }
                                                handleClick={
                                                    handleOnlineRequestClick
                                                }
                                            />
                                        )}
                                    </td>
                                    <td>{row.departure_date ?? "-"}</td>
                                    <td>{row.return_date ?? "-"}</td>
                                    <td>{row.departure.name ?? "-"}</td>
                                    <td>{row.destination.name ?? "-"}</td>
                                    <td
                                        className={classNames({
                                            txcGray: row.applicant.is_deleted
                                        })}
                                    >
                                        {row.applicant.name ?? "-"}
                                    </td>
                                    <td
                                        className={classNames({
                                            txcGray:
                                                row.representative.is_deleted
                                        })}
                                    >
                                        {row.representative.state_inc_name ??
                                            "-"}
                                    </td>
                                    <td>{row.name ?? "-"}</td>
                                    <td>{row.travel_type.val ?? "-"}</td>
                                    <td>{row.application_type.val ?? "-"}</td>
                                    <td>{row.application_date.val ?? "-"}</td>
                                    <td className="txtalc">
                                        <span
                                            className={classNames(
                                                "material-icons",
                                                {
                                                    "js-modal-open": !isDeleting
                                                }
                                            )}
                                            data-target="mdDelete"
                                            onClick={() => {
                                                setDeleteNumber(row?.hash_id);
                                            }}
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
                id="mdDelete"
                title="この予約を削除しますか？"
                handleAction={handleDelete}
                isActioning={isDeleting}
            />
            {/**オンライン相談modal */}
            <OnlineRequestModal
                id="mdVideoReserve"
                values={onlineRequestValues}
                input={onlineRequestInputValues}
                handleAction={handleOnlineRequestInputChange}
                isActioning={isOnlineRequesting}
                handleChangeRequest={handleChangeOnlineRequest}
                handleConsentRequest={handleConsentRequest}
            />
        </>
    );
};

const Element = document.getElementById("reserveList");
if (Element) {
    const jsVars = Element.getAttribute("jsVars");
    const parsedJsVars = jsVars && JSON.parse(jsVars);
    const searchParam = Element.getAttribute("searchParam");
    const parsedSearchParam = searchParam && JSON.parse(searchParam);
    const formSelects = Element.getAttribute("formSelects");
    const parsedFormSelects = formSelects && JSON.parse(formSelects);
    const consts = Element.getAttribute("consts");
    const parsedConsts = consts && JSON.parse(consts);

    render(
        <ConstApp jsVars={parsedJsVars}>
            <ReserveList
                searchParam={parsedSearchParam}
                formSelects={parsedFormSelects}
                consts={parsedConsts}
            />
        </ConstApp>,
        document.getElementById("reserveList")
    );
}
