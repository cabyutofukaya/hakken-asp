import React, { useState, useEffect, useContext, useRef } from "react";
import { ConstContext } from "./components/ConstApp";
import ConstApp from "./components/ConstApp";
import { render } from "react-dom";
import { useMountedRef } from "../../hooks/useMountedRef";
import ReactLoading from "react-loading";
import classNames from "classnames";

const News = ({}) => {
    const { agencyAccount } = useContext(ConstContext);

    const mounted = useMountedRef(); // マウント・アンマウント制御

    const msgsRaw = useRef([]); // メッセージ一覧データ(メッセージレシーブ時になぜかrowsが毎回空配列で初期されてしまうので、rowsに渡すデータをmsgsRawで保持しておく)
    const [lists, setLists] = useState([]); // 通知一覧表示用配列

    const [isLoading, setIsLoading] = useState(false);
    const [isReading, setIsReading] = useState(false); // 既読処理中か否か

    // ページャー関連変数
    const page = useRef(1); // 現在のページ
    const hasNext = useRef(false); // 次ページがあるか

    // 一覧取得
    const fetch = async p => {
        if (!mounted.current) return;
        if (isLoading) return;

        setIsLoading(true);

        const response = await axios
            .get(`/api/${agencyAccount}/agency-notification/list`, {
                params: {
                    page: p,
                    per_page: p <= 1 ? 10 : 3 // ページ数によって取得件数を変更。1P目は必ず表示枠がスクロール状態となるように多目に(limit=10)。2ページ目以降は少しずつ取得(limit=3)。下までスクロールしきったところで既読処理をしたいので取得件数は基本的にはこまめに追加した方が都合が良い
                }
            })
            .finally(() => {
                if (mounted.current) {
                    setIsLoading(false);
                }
            });
        if (mounted.current && response?.data?.data) {
            const data = response.data.data;

            // リスト更新
            const list = _.uniqBy(
                [...msgsRaw.current, ...response.data.data],
                "id"
            ); // 追加で取得したデータ末尾に追加。念の為、重複削除
            msgsRaw.current = [...list];
            setLists([...list]);
            //

            // ページャー関連
            page.current = response.data.meta.current_page;
            hasNext.current = response.data.links.next ? true : false;
        }
    };
    useEffect(() => {
        fetch(page.current); // 一覧取得
    }, []);

    /**
     * 既読処理
     *
     * 以下の2つのシチュエーションで実行
     * ・メッセージBoxがスライドオープンした時(スクロールが不要な通知数しかない時のために)
     * ・メッセージを一番下までスクロールした時
     */
    const read = async () => {
        // 既読にするID一覧を取得
        let results = _.filter(msgsRaw.current, function(row) {
            return !row?.read_at;
        });
        const ids = results.map(row => row.id);

        if (ids.length > 0) {
            if (!mounted.current) return;
            if (isReading) return;

            setIsReading(true);

            // 既読API
            const response = await axios
                .put(`/api/${agencyAccount}/agency-notification/read`, {
                    ids,
                    _method: "put"
                })
                .finally(() => {
                    if (mounted.current) {
                        setIsReading(false);
                    }
                });
            if (mounted.current && response?.data) {
                try {
                    const num = response.data.length;
                    if ($(".el_news .count").length > 0) {
                        // 通知バッチを更新
                        $(".el_news .count").each(function(index) {
                            const currNum = parseInt($(this).text());
                            const n = currNum - num;
                            // 未通知がなくなったらバッジ非表示。残りがある場合は数値更新
                            if (n <= 0) {
                                $(this).hide();
                            } else {
                                $(this).text(n);
                            }
                        });
                    }
                } catch (error) {
                    console.error(error);
                }
            }
        }
    };

    // スクロール監視関数
    const surveScroll = () => {
        const margin = 50; // 一番下に行く前に少しマージン取っておく
        const element = document.getElementById("news");
        const clientHeight = element.clientHeight;
        const scrollHeight = element.scrollHeight;
        element.onscroll = async function() {
            if (scrollHeight - (clientHeight + this.scrollTop) <= margin) {
                await read();
                if (hasNext.current) {
                    fetch(page.current + 1);
                }
            }
        };
    };

    // 通知boxのスクロール位置の監視。一番上までスクロールしたら過去通知を取得
    useEffect(() => {
        document.getElementById("news").addEventListener("scroll", surveScroll);
        return () => {
            document
                .getElementById("news")
                .removeEventListener("scroll", surveScroll);
        };
    }, []);

    // 通知boxのスライドINの監視。スライドするとbodyタグのclassが変化する
    useEffect(() => {
        const body = document.getElementsByTagName("body");

        // オブザーバーの作成
        const observer = new MutationObserver(mutations => {
            mutations.forEach(mutation => {
                if (mutation.attributeName === "class") {
                    const isOpen = mutation.target.classList.contains(
                        "js_newsOpen"
                    );
                    if (isOpen) {
                        // メッセージBoxが開いたら既読処理
                        read();
                    }
                }
            });
        });

        // 監視の開始
        observer.observe(body[0], {
            attributes: true,
            attributeFilter: ["class"]
        });

        return () => {
            // 監視解除
            observer.disconnect();
        };
    }, []);

    return (
        <div id="news">
            <h2>NEWS</h2>
            <ul>
                {lists.length > 0 &&
                    lists.map((row, i) => (
                        <li
                            className={classNames({
                                new: !row?.read_at
                            })}
                            key={i}
                        >
                            <span>{row?.regist_date}</span>
                            <div
                                dangerouslySetInnerHTML={{
                                    __html: row?.content
                                }}
                            />
                        </li>
                    ))}
                {isLoading && (
                    <li>
                        <ReactLoading type={"bubbles"} color={"#dddddd"} />
                    </li>
                )}
                {!isLoading && lists.length === 0 && <li>通知はありません</li>}
            </ul>
        </div>
    );
};
// 入力画面
const Element = document.getElementById("newsArea");
if (Element) {
    const jsVars = Element.getAttribute("jsVars");
    const parsedJsVars = jsVars && JSON.parse(jsVars);

    render(
        <ConstApp jsVars={parsedJsVars}>
            <News />
        </ConstApp>,
        document.getElementById("newsArea")
    );
}
