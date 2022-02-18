import React, { useState, useEffect } from "react";
import { render } from "react-dom";
import SmallDangerModal from "./components/SmallDangerModal";
import PageNation from "./components/PageNation";
import ReactLoading from "react-loading";
import { useMountedRef } from "../../hooks/useMountedRef";
import DeclineMessage from "./portal/DeclineMessage";

const TableWrap = ({ agencyAccount }) => {
    const mounted = useMountedRef(); // マウント・アンマウント制御

    // ページャー関連変数
    const [page, setPage] = useState(0);
    const [lastPage, setLastPage] = useState(0);
    const [sort, setSort] = useState("id");
    const [direction, setDirection] = useState("asc");
    const perPage = 10; // 1ページ表示件数

    const [isLoading, setIsLoading] = useState(false); // 重複読み込み防止
    const [isDeleting, setIsDeleting] = useState(false); // 削除処理中か否か

    const [showDeclineMessage, setShowDeclineMessage] = useState(false);
    const [declineMessage, setDeclineMessage] = useState(null);

    const [mailTemplates, setMailTemplates] = useState([]); // メールテンプレート一覧
    const [deleteId, setDeleteId] = useState(null); // 削除対象ID

    const setData = data => {
        setMailTemplates(data.data);
        // ページャー関連
        setPage(data.current_page);
        setLastPage(data.last_page);
    };

    const fetch = async () => {
        if (!mounted.current) return;
        if (isLoading) return;

        setIsLoading(true);

        const response = await axios
            .get(`/api/${agencyAccount}/mail/list`, {
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

        if (mounted.current && response?.data) {
            setData(response.data);
        }
    };

    useEffect(() => {
        fetch();
    }, [page, sort, direction]);

    // ページリンクをクリックした挙動（ページネーションコンポーネント用）
    const handlePagerClick = (e, targetPage) => {
        e.preventDefault();
        setPage(targetPage);
    };

    // 削除ボタンをクリックした時の挙動
    const handleDelete = async e => {
        if (!mounted.current) return;
        if (isDeleting) return;

        setIsDeleting(true);

        const response = await axios
            .delete(`/api/${agencyAccount}/mail/${deleteId}`)
            .finally(() => {
                $(".js-modal-close").trigger("click");
                setTimeout(function() {
                    if (mounted.current) {
                        setIsDeleting(false);
                    }
                }, 3000);
            });

        if (mounted.current && response) {
            setShowDeclineMessage(true); // decline Messageエリア表示
            setDeclineMessage(`メールテンプレートを削除しました。`);

            fetch(); // 一覧再取得
        }
    };

    return (
        <>
            <DeclineMessage
                show={showDeclineMessage}
                message={declineMessage}
            />
            <table>
                <thead>
                    <tr>
                        <th className="wd40">テンプレート名</th>
                        <th className="wd60">説明</th>
                        <th>削除</th>
                    </tr>
                </thead>
                <tbody>
                    {isLoading && (
                        <tr>
                            <td colSpan={3}>
                                <ReactLoading
                                    type={"bubbles"}
                                    color={"#dddddd"}
                                />
                            </td>
                        </tr>
                    )}
                    {!isLoading && mailTemplates.length === 0 && (
                        <tr>
                            <td colSpan={3}>メール定型文データはありません</td>
                        </tr>
                    )}
                    {!isLoading &&
                        mailTemplates.length > 0 &&
                        mailTemplates.map(mailTemplate => (
                            <>
                                <tr key={mailTemplate.id}>
                                    <td>
                                        <a
                                            href={`/${agencyAccount}/system/mail/${mailTemplate.id}/edit`}
                                        >
                                            {mailTemplate.name}
                                        </a>
                                    </td>
                                    <td>{mailTemplate.description}</td>
                                    <td>
                                        <span
                                            className="material-icons js-modal-open"
                                            data-target="mdDeleteMailTemplate"
                                            onClick={() =>
                                                setDeleteId(mailTemplate.id)
                                            }
                                        >
                                            delete
                                        </span>
                                    </td>
                                </tr>
                            </>
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
            <SmallDangerModal
                id="mdDeleteMailTemplate"
                title="この設定を削除しますか？"
                handleAction={handleDelete}
                isActioning={isDeleting}
            />
        </>
    );
};

// // 削除完了メッセージ
// const DeclineMessage = () => {
//     const [message, setMessage] = useState(null);

//     return (
//         <>
//             <div id="declineMessage">
//                 <p>
//                     <span className="material-icons">do_not_disturb_on</span>
//                     &nbsp;{message}
//                 </p>
//                 <span className="material-icons closeIcon">cancel</span>
//             </div>
//         </>
//     );
// };

// // 削除完了メッセージ
// const DeclineMessageElement = document.getElementById("declineMessageWrap");

// if (DeclineMessageElement) {
//     let declineMessage = render(
//         <DeclineMessage />,
//         document.getElementById("declineMessageWrap")
//     );
// }

// 一覧
const Element = document.getElementById("tableWrap");
if (Element) {
    const agencyAccount = Element.getAttribute("agencyAccount");
    render(
        <TableWrap agencyAccount={agencyAccount} />,
        document.getElementById("tableWrap")
    );
}
