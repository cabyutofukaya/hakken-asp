import React, {
    useState,
    useEffect,
    useContext,
    useImperativeHandle,
    forwardRef
} from "react";
import { ConstContext } from "../../ConstApp";
import PageNation from "../../PageNation";
import ReactLoading from "react-loading";
import DeclineMessage from "../../DeclineMessage";
import SuccessMessage from "../../SuccessMessage";
import { useMountedRef } from "../../../../../hooks/useMountedRef";

const Hotel = forwardRef(
    (
        {
            searchParam,
            requestId,
            successMsg,
            isDeleting,
            setIsDeleting,
            deleteInfo,
            setDeleteInfo
        },
        ref
    ) => {
        const { agencyAccount, subjectCategories } = useContext(ConstContext);

        const mounted = useMountedRef(); // マウント・アンマウント制御

        // ページャー関連変数
        const [page, setPage] = useState(1);
        const [lastPage, setLastPage] = useState(0);
        const [sort, setSort] = useState("id");
        const [direction, setDirection] = useState("asc");
        const [total, setTotal] = useState(0);
        const perPage = 10; // 1ページ表示件数

        const [isLoading, setIsLoading] = useState(false); // 重複読み込み防止

        const [successMessage, setSuccessMessage] = useState(successMsg); // 登録・編集処理完了メッセージ
        const [declineMessage, setDeclineMessage] = useState("");

        const [sortParam, setSortParam] = useState({
            id: "asc",
            code: "asc",
            kbn: "asc",
            hotel_name: "asc",
            room_type: "asc",
            meal_type: "asc",
            // "city.name": "asc",
            ad_gross: "asc",
            ad_net: "asc",
            "supplier.name": "asc"
        });

        const [lists, setLists] = useState([]); //一覧

        const fetch = async () => {
            if (!mounted.current) return;
            if (isLoading) return;

            setIsLoading(true);

            const response = await axios
                .get(`/api/${agencyAccount}/subject/hotel/list`, {
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
                setLists(response.data.data);
                // ページャー関連
                setPage(response.data.meta.current_page);
                setLastPage(response.data.meta.last_page);
                setTotal(response.data.meta.total);
            }
        };

        useEffect(() => {
            fetch(); // 一覧取得
        }, [page, sort, direction, requestId]);

        // ページリンクをクリックした挙動（ページネーションコンポーネント用）
        const handlePagerClick = (e, targetPage) => {
            e.preventDefault();
            setPage(targetPage);
        };

        // 親コンポーネント(subject_category-index.js)から呼び出される
        useImperativeHandle(ref, () => ({
            handleDelete: async e => {
                if (!mounted.current) return;
                if (isDeleting) return;
                if (
                    deleteInfo.subject !=
                    subjectCategories.subject_category_hotel
                )
                    return; // 念の為、削除対象の科目カテゴリを確認

                setIsDeleting(true);

                const response = await axios
                    .delete(
                        `/api/${agencyAccount}/subject/hotel/${deleteInfo?.id}`
                    )
                    .finally(() => {
                        $(".js-modal-close").trigger("click"); // 削除モーダルclose
                        setTimeout(function() {
                            if (mounted.current) {
                                setIsDeleting(false);
                            }
                        }, 3000);
                    });

                if (mounted.current && response) {
                    const newTotal = total - 1; // 削除処理後の件数
                    const newPage = Math.ceil(newTotal / perPage);

                    if (newPage < page) {
                        // 現在のページ番号よりも総ページ数が少なくなった場合はページ番号を再設定。totalはapiにより更新される
                        setPage(newPage);
                    } else {
                        fetch(); // トータルのみの変更は手動fetch
                    }

                    setDeclineMessage("項目の削除が完了しました");
                }
            }
        }));

        // 並び替えリンクをクリックした挙動
        const handleSortClick = column => {
            const direction = sortParam[column] === "asc" ? "desc" : "asc";
            setDirection(direction);
            setSort(column);

            setSortParam({ ...sortParam, [column]: direction });
        };

        return (
            <>
                <SuccessMessage message={successMessage} />
                <DeclineMessage message={declineMessage} />

                <div className="tableWrap dragTable">
                    <div className="tableCont">
                        <table>
                            <thead>
                                <tr>
                                    <th
                                        className="sort wd20"
                                        onClick={e => handleSortClick("code")}
                                    >
                                        <span>商品コード</span>
                                    </th>
                                    <th
                                        className="sort wd20"
                                        onClick={e => handleSortClick("kbn")}
                                    >
                                        <span>区分</span>
                                    </th>
                                    <th
                                        className="sort wd50"
                                        onClick={e =>
                                            handleSortClick("hotel_name")
                                        }
                                    >
                                        <span>ホテル名</span>
                                    </th>
                                    <th
                                        className="sort wd30 txtalc"
                                        onClick={e =>
                                            handleSortClick("room_type")
                                        }
                                    >
                                        <span>部屋タイプ</span>
                                    </th>
                                    <th
                                        className="sort wd30 txtalc"
                                        onClick={e =>
                                            handleSortClick("meal_type")
                                        }
                                    >
                                        <span>食事タイプ</span>
                                    </th>
                                    {/* <th
                                    className="sort wd30"
                                    onClick={e => handleSortClick("city.name")}
                                >
                                    <span>都市・空港</span>
                                </th> */}
                                    <th
                                        className="sort wd20 txtalr"
                                        onClick={e =>
                                            handleSortClick("ad_gross")
                                        }
                                    >
                                        <span>GROSS単価</span>
                                    </th>
                                    <th
                                        className="sort wd20 txtalr"
                                        onClick={e => handleSortClick("ad_net")}
                                    >
                                        <span>NET単価</span>
                                    </th>
                                    <th
                                        className="sort wd30"
                                        onClick={e =>
                                            handleSortClick("supplier.name")
                                        }
                                    >
                                        <span>仕入れ先</span>
                                    </th>
                                    <th className="wd30">
                                        <span>備考</span>
                                    </th>
                                    <th className="wd05 txtalc">
                                        <span>削除</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                {isLoading && (
                                    <tr>
                                        <td colSpan={11}>
                                            <ReactLoading
                                                type={"bubbles"}
                                                color={"#dddddd"}
                                            />
                                        </td>
                                    </tr>
                                )}
                                {!isLoading && lists.length === 0 && (
                                    <tr>
                                        <td colSpan={11}>
                                            科目データはありません
                                        </td>
                                    </tr>
                                )}
                                {!isLoading &&
                                    lists.length > 0 &&
                                    lists.map((row, index) => (
                                        <tr key={index}>
                                            <td>
                                                <a
                                                    href={`/${agencyAccount}/master/subject/hotel/${row?.id}/edit`}
                                                >
                                                    {row.code ?? "-"}
                                                </a>
                                            </td>
                                            <td>{row.kbn.val ?? "-"}</td>
                                            <td>{row.hotel_name ?? "-"}</td>
                                            <td className="txtalc">
                                                {row.room_type.val ?? "-"}
                                            </td>
                                            <td className="txtalc">
                                                {row.meal_type.val ?? "-"}
                                            </td>
                                            {/* <td>{row.city ?? "-"}</td> */}
                                            <td className="txtalr">
                                                {row?.ad_gross
                                                    ? "￥" +
                                                      row.ad_gross.toLocaleString()
                                                    : ""}
                                            </td>
                                            <td className="txtalr">
                                                {row?.ad_net
                                                    ? "￥" +
                                                      row.ad_net.toLocaleString()
                                                    : ""}
                                            </td>
                                            <td>{row.supplier.name ?? "-"}</td>
                                            <td>{row.note ?? "-"}</td>
                                            <td className="txtalc">
                                                <span
                                                    className="material-icons js-modal-open"
                                                    data-target="mdDeleteSubject"
                                                    onClick={() =>
                                                        setDeleteInfo({
                                                            id: row?.id,
                                                            subject:
                                                                subjectCategories.subject_category_hotel
                                                        })
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
                </div>
            </>
        );
    }
);

export default Hotel;
