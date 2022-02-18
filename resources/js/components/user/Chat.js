import React, { Component, useEffect, useState } from "react";
import ReactDOM from "react-dom";
import Peer from "skyway-js";

let peer = null;
let room = null;

const Chat = () => {
    const suggestionId = location.pathname.split("/chat/")[1]; // 提案ID

    const [suggestion, setSuggestion] = useState(null);
    const [chatData, setChatData] = useState(null);

    useEffect(() => {
        async function prepare() {
            try {
                const suggestion = axios.get(`/api/suggestion/${suggestionId}`);
                setSuggestion(suggestion);
                const chatData = await axios.get(
                    `/api/chat/suggestion/${suggestionId}`
                );
                // return chatData;
                setChatData(chatData);
                console.log(chatData);
            } catch (e) {
                console.log(e);
            }
        }

        prepare();
    }, []);

    useEffect(() => {
        if (!suggestion) return;
        // TODO実装予定
        // 1. メッセージリスト、初回表示時に未読メッセージを調べて既読にする準備をする（localstrageに書き出しておくとか）
        // 2. 画面を閉じる時などに既読状態をまとめて自APIサーバーへ送る
        peer = new Peer({
            key: process.env.MIX_SKYWAY_API_KEY,
            debug: 3
        });
        peer.on("open", () => {
            console.log("peer is open!");

            peer.on("error", error => {
                console.log(`${error.type}: ${error.message}`);
                alert(
                    "通信エラー： メッセージの送信処理に失敗しました\nお手数ですが画面を再読み込みの上、再度メッセージ送信お願いいたします。"
                );
            });

            room = peer.joinRoom(suggestionId, {
                mode: "mesh"
            });
            room.once("open", () => {
                console.log("you joined");
                // setJoinRoom(true);
            });
            room.on("peerJoin", peerId => {
                console.log(`${peerId} joined`);
            });
            room.on("data", ({ data, src }) => {
                console.log(`data called`);
                // if (data.type === "message") {
                //     // メッセージ受信
                //     dispatch(addMessage(data.data));
                // } else if (data.type === "read") {
                //     //既読受信
                //     dispatch(
                //         setReadAt({
                //             message_id: data.data.message_id,
                //             read_at: data.data.read_at
                //         })
                //     );
                // }
                console.log(`${src}: ${data}`);
            });
            room.on("peerLeave", peerId => {
                console.log(`${peerId} left`);
            });
            room.once("close", () => {
                console.log("You left");
            });
        });
    }, [suggestion]);

    const sendMessage = () => {
        console.log("send message.");
    };

    // InputエリアEnterで送信
    const handleKeyDown = e => {
        if (e.keyCode === 13) {
            sendMessage();
        }
    };

    const handleSend = e => {
        sendMessage();
    };

    const Loading = () => {
        return <div>...loading</div>;
    };

    return (
        <>
            {!chatData ? (
                <Loading />
            ) : (
                <>
                    <main>
                        <ul className="subNavi">
                            <li>
                                <a href="./">
                                    <img
                                        src="common/img/shared/ico_back.svg"
                                        alt=""
                                    />
                                </a>
                            </li>
                            <li>
                                <a href="">
                                    <i className="icf-ico_plan"></i>
                                </a>
                            </li>
                        </ul>
                        <div id="chat" ref={chatContainer}>
                            {chatData &&
                                chatData.data.map(row => {
                                    return (
                                        <div key={row.message_id}>
                                            {row.user_id && (
                                                <div
                                                    className="user"
                                                    key={row.message_id}
                                                >
                                                    <div className="comment">
                                                        <span className="time">
                                                            {row.read_at && (
                                                                <>
                                                                    既読
                                                                    <br />
                                                                </>
                                                            )}
                                                            {moment(
                                                                row.created_at
                                                            ).format(
                                                                "YYYY-MM-DD HH:mm"
                                                            )}
                                                        </span>
                                                        <p>{row.message}</p>
                                                    </div>
                                                </div>
                                            )}
                                            {row.staff_id && (
                                                <div
                                                    className="client"
                                                    key={row.message_id}
                                                >
                                                    <img
                                                        src="common/img/shared/prof_img.jpg"
                                                        alt=""
                                                    />
                                                    <span className="time">
                                                        {moment(
                                                            row.created_at
                                                        ).format(
                                                            "YYYY-MM-DD HH:mm"
                                                        )}
                                                    </span>
                                                    <p>{row.message}</p>
                                                </div>
                                            )}
                                            {row.staff_id &&
                                                !row.read_at &&
                                                handleRead(row.message_id)}
                                        </div>
                                    );
                                })}
                            <div id="chatInput">
                                <input
                                    type="text"
                                    value={comment}
                                    onChange={e => setComment(e.target.value)}
                                    onKeyDown={handleKeyDown}
                                />
                                <input
                                    type="submit"
                                    value=""
                                    onClick={handleSend}
                                    disabled={!comment}
                                />
                            </div>
                        </div>
                    </main>
                </>
            )}
        </>
    );
};

export default Chat;

if (document.getElementById("userChat")) {
    ReactDOM.render(<Chat />, document.getElementById("userChat"));
}
