import React, {
    useContext,
    useCallback,
    useEffect,
    useState,
    useRef
} from "react";
import { SENDER } from "../../constants";
import { ConstContext } from "../ConstApp";
import { useMountedRef } from "../../../../hooks/useMountedRef";
import BrText from "../../BrText";
import classNames from "classnames";
import TextareaAutosize from "react-textarea-autosize";
import moment from "moment";

import Peer from "skyway-js";
const peer = new Peer({ key: process.env.MIX_SKYWAY_API_KEY });
let room = null;

// (function(_Time, _Unit, _EventNamesArray) {
//     var TimeUnits = new (function() {
//         (this.Second = 1000),
//             (this.Minute = this.Second * 60),
//             (this.Hour = this.Minute * 60);
//     })();
//     var Timer = {};
//     Timer.Limit = _Time * TimeUnits[_Unit];
//     Timer.Main = function() {
//         // 現在のタブが「相談一覧」なら更新
//         var tab = document.getElementById("consultationTab");
//         if (tab.classList.contains("tabstay")) {
//             Timer.RemoveEvent();

//             var url = new URL(window.location.href);
//             var params = url.searchParams;
//             if (params.has("tab")) {
//                 location.reload();
//             } else {
//                 location.href = document.URL + "?tab=consultation"; // 相談タブのパラメータをつけて画面再読込
//             }
//         }
//     };
//     Timer.SetTimeoutID = "";
//     Timer.SetTimeout = function() {
//         this.SetTimeoutID = setTimeout(this.Main, this.Limit);
//         return this;
//     };
//     Timer.ClearTimeout = function() {
//         clearTimeout(Timer.SetTimeoutID);
//         return this;
//     };
//     Timer.Manage = function() {
//         Timer.ClearTimeout().SetTimeout();
//     };
//     Timer.EventNames = _EventNamesArray || ["keydown", "mousemove", "click"];
//     Timer.EventNamesLength = Timer.EventNames.length;
//     Timer.SetEvent = function() {
//         var _Length = this.EventNamesLength;
//         while (_Length--) {
//             addEventListener(this.EventNames[_Length], this.Manage, false);
//         }
//     };
//     Timer.RemoveEvent = function() {
//         var _Length = this.EventNamesLength;
//         while (_Length--) {
//             removeEventListener(this.EventNames[_Length], this.Manage, false);
//         }
//     };

//     Timer.SetTimeout().SetEvent();
// })(5, "Minute"); // 5分、画面操作がなければリロード

const WebConsultationArea = ({ isShow, reserve, roomKey }) => {
    const { agencyAccount } = useContext(ConstContext);

    const mounted = useMountedRef(); // マウント・アンマウント制御

    const msgsRaw = useRef([]); // メッセージ一覧データ(メッセージレシーブ時になぜかrowsが毎回空配列で初期されてしまうので、rowsに渡すデータをmsgsRawで保持しておく)
    const [rows, setRows] = useState([]); // メッセージ一覧表示用配列

    const messageBoxElm = useRef(null);

    const hasNext = useRef(false);
    const [message, setMessage] = useState("");

    const [isLoading, setIsLoading] = useState(false); // メッセージ読み込み中
    const [isSending, setIsSending] = useState(false); // メッセージ送信中か否か
    const [isConnection, setIsConnection] = useState(false); // peer接続済みか否か
    const [isReadChecking, setIsReadChecking] = useState(false); // 既読チェック処理中か否か

    // useStateによる再レンダリング処理が実行されるとpeer・room関連のリスナー登録がメモリリークを起こしてしまうのでuseEffect内で処理
    useEffect(() => {
        peer.on("open", () => {
            console.log(`=== DataConnection has been opened ===\n`);
            room = peer.joinRoom(roomKey, {
                mode: "mesh"
            });
            setIsConnection(true);
        });

        if (!peer.open) return;

        room.on("data", ({ data, src }) => {
            console.log(`${src}: ${data}`);
            console.log(data);
            if (data) {
                // リスト更新
                msgsRaw.current = [...msgsRaw.current, data];
                setRows([...msgsRaw.current]);
                ////
                if (data.sender !== SENDER.CLIENT) {
                    // 既読処理
                    axios.post(
                        `/api/${agencyAccount}/web/estimate/${reserve.id}/message/read`,
                        {
                            id: data.id
                        }
                    );
                }
                scrollBottom("smooth"); //メッセージエリアをスクロール
            }
        });
        room.once("open", () => {
            console.log("you joined");
        });
        room.on("peerJoin", peerId => {
            console.log(`${peerId} joined`);
        });
        room.on("peerLeave", peerId => {
            console.log(`${peerId} left`);
        });
        room.once("close", () => {
            console.log("You left");
        });

        peer.on("error", error => {
            console.log("peer-error called.");
            console.log(`${error.type}: ${error.message}`);
            setIsConnection(false);

            if (error.type === "permission") {
                location.reload();
            } else {
                alert(
                    "通信エラー： メッセージの送受信処理に失敗しました\nお手数ですが画面を再読み込みの上、再度メッセージ送信お願いいたします。"
                );
            }
        });

        peer.on("disconnected", function() {
            console.log("disconnected");
        });

        peer.on("close", () => {
            alert("通信が切断しました。");
        });
    }, [isConnection]);

    // 一覧取得
    useEffect(() => {
        fetch(null, true);
    }, []);

    // 表示状態になったらメッセージボックスを一番下までスクロール
    useEffect(() => {
        if (isShow) scrollBottom("auto"); //メッセージエリアをスクロール
    }, [isShow]);

    // スクロール監視関数
    const surveScroll = () => {
        if (messageBoxElm.current.scrollTop <= 50) {
            // 50px以下になったら過去メッセージ取得API実行
            if (hasNext.current) {
                const id = msgsRaw.current[0].id ?? null;
                fetch(id, false);
            }
        }
    };

    // メッセージboxのスクロール位置の監視。一番上までスクロールしたら過去メッセージを取得
    useEffect(() => {
        messageBoxElm.current.addEventListener("scroll", surveScroll);
        return () => {
            messageBoxElm.current.removeEventListener("scroll", surveScroll);
        };
    }, []);

    // 既読を定期的にチェック(20秒間隔)
    useEffect(() => {
        const readCheck = async () => {
            if (!mounted.current) return;
            if (isReadChecking) return;

            setIsReadChecking(true);

            const ids = _.filter(msgsRaw.current, {
                sender: SENDER.CLIENT,
                read_at: null
            }).map(row => row.id); // 未読ID一覧を作成

            if (ids.length > 0) {
                const response = await axios
                    .post(
                        `/api/${agencyAccount}/web/estimate/${reserve.id}/message/read/check`,
                        {
                            ids
                        }
                    )
                    .finally(() => {
                        setIsReadChecking(false);
                    });
                if (mounted.current && response?.data?.data) {
                    let copylist = _.cloneDeep(msgsRaw.current); //メッセージ一覧
                    response.data.data.map((row, i) => {
                        if (row.read_at) {
                            const index = _.findIndex(copylist, { id: row.id });
                            if (index !== -1) {
                                //既読日時をセット
                                copylist[index].read_at = row.read_at;
                            }
                        }
                    });
                    // リスト更新
                    msgsRaw.current = [...copylist];
                    setRows([...msgsRaw.current]);
                    //
                }
            }
        };

        const interavlId = setInterval(() => {
            readCheck();
        }, 20000);

        return () => {
            clearInterval(interavlId);
        };
    }, []);

    /**
     *
     * @param {*} id
     * @param {*} scroll リスト取得後、一番下にスクロールする場合はtrue
     * @returns
     */
    const fetch = async (id, scroll) => {
        if (!mounted.current) return;
        if (isLoading) return;

        setIsLoading(true);
        const response = await axios
            .get(
                `/api/${agencyAccount}/web/estimate/${reserve.id}/message/list`,
                {
                    params: {
                        last_id: id,
                        limit: 10
                    }
                }
            )
            .finally(() => {
                setTimeout(function() {
                    if (mounted.current) {
                        setIsLoading(false);
                    }
                }, 800); //連続通信防止のため若干インターバル
            });
        if (mounted.current && response?.data?.data) {
            // リスト更新
            const list = _.uniqBy(
                [...response.data.data, ...msgsRaw.current],
                "id"
            ); // 追加で取得したデータ(古いデータ)を先頭に追加。念の為、重複削除
            msgsRaw.current = [...list];
            setRows([...list]);
            //
            hasNext.current = response.data.next_page;

            if (scroll) scrollBottom("auto");
        }
    };

    const handleClickSend = async e => {
        e.preventDefault();

        if (message.trim().length === 0) return;
        if (!mounted.current) return;
        if (isSending) return;

        setIsSending(true);

        const sendAt = moment().format("YYYY-MM-DD HH:mm:ss"); // 送信日時

        const response = await axios
            .post(`/api/${agencyAccount}/web/estimate/${reserve.id}/message`, {
                message: message,
                send_at: sendAt
            })
            .finally(() => {
                setTimeout(function() {
                    if (mounted.current) {
                        setIsSending(false);
                    }
                }, 300); //連続送信防止のため若干インターバル
            });
        if (mounted.current && response?.data?.data) {
            const msgObj = response.data.data;

            room.send(msgObj); // メッセージ送信通知

            // リスト更新
            msgsRaw.current = [...msgsRaw.current, msgObj];
            setRows([...msgsRaw.current]);
            ////

            setMessage("");

            scrollBottom("smooth"); //メッセージエリアをスクロール
        }
    };

    // メッセージエリアを一番下までスクロール
    const scrollBottom = useCallback(behavior => {
        if (messageBoxElm.current) {
            const scroll = messageBoxElm.current.scrollHeight;

            messageBoxElm.current.scroll({
                top: scroll,
                behavior
            });
        }
    });

    return (
        <div
            className={classNames("userList", {
                show: isShow
            })}
        >
            <h2>
                <span className="material-icons">question_answer</span>
                メッセージ
            </h2>
            <div id="message" ref={messageBoxElm}>
                {rows &&
                    rows.map((row, i) => (
                        <div className={row.sender} key={i}>
                            <p>
                                <BrText text={row.message ?? ""} />
                            </p>
                            <span className="time">
                                {row.sender === SENDER.CLIENT && !row.read_at && (
                                    <>
                                        未読
                                        <br />
                                    </>
                                )}
                                {moment(row.send_at).format("YYYY.MM.DD HH:mm")}
                            </span>
                        </div>
                    ))}
            </div>
            <ul className="messageInput">
                <li>
                    <TextareaAutosize
                        placeholder="ここにメッセージを入力してください"
                        className="auto-resize"
                        value={message}
                        onChange={e => setMessage(e.target.value)}
                    ></TextareaAutosize>
                </li>
                <li>
                    <button
                        className="material-icons"
                        onClick={handleClickSend}
                    >
                        send
                    </button>
                </li>
            </ul>
        </div>
    );
};

export default WebConsultationArea;
