// const Peer = window.Peer;
import Peer from "skyway-js";
import _ from "lodash";
import moment from "moment";
import shortid from "shortid";

(async function main() {
    let peer = null;
    let room = null;

    // 相談IDを抽出
    const path = location.pathname.split("/chat/");
    const suggestionId = _.get(path, 1);

    const messageInput = document.getElementById("messageInput");

    const connect = () => {
        peer = window.peer = new Peer({
            key: process.env.MIX_SKYWAY_API_KEY,
            debug: 3
        });
    };

    // 経過時刻の計算に利用する、現在の時刻を取得
    let startTime = Date.now();

    // setInterval()で5秒毎に実行
    setInterval(function() {
        // 変数startTimeの時刻から2分経過した場合
        if (Date.now() > startTime + 2 * 60 * 1000) {
            // 実行させる処理を記述

            // 経過時刻の計算に利用する、現在の時刻を更新
            startTime = Date.now();
            console.log(startTime);
            console.log(peer.open);
            if (!peer.open) {
                console.log("re connect");
                connect();
            }
        }
    }, 5000);

    //ページを閉じる
    document.getElementById("close").addEventListener("click", e => {
        e.preventDefault();
        let nvua = navigator.userAgent;
        if (nvua.indexOf("MSIE") >= 0) {
            if (nvua.indexOf("MSIE 5.0") == -1) {
                top.opener = "";
            }
        } else if (nvua.indexOf("Gecko") >= 0) {
            top.name = "CLOSE_WINDOW";
            let wid = window.open("", "CLOSE_WINDOW");
        }
        top.close();
    });

    //　メッセージ送信
    document.getElementById("sendMessage").addEventListener("click", e => {
        e.preventDefault();
        sendMessage();
    });

    messageInput.addEventListener("keydown", e => {
        if (e.key === "Enter") {
            sendMessage();
        }
    });

    const sendMessage = () => {
        const msg = messageInput.value.trim();

        if (!msg) {
            alert("メッセージを入力してください。");
            return;
        }

        let obj = {
            message_id: shortid.generate(),
            // user_id: suggestion.user_id,
            user_id: 1,
            staff_id: null,
            message: msg,
            created_at: moment().format("YYYY-MM-DD HH:mm:ss"),
            read_at: null
        };
        room.send({
            type: "message",
            data: obj
        });
        console.log(obj);
    };

    // eslint-disable-next-line require-atomic-updates
    connect();

    peer.on("error", error => {
        console.log("peer-error called.");
        console.log(`${error.type}: ${error.message}`);
        alert(
            "通信エラー： メッセージの送受信処理に失敗しました\nお手数ですが画面を再読み込みの上、再度メッセージ送信お願いいたします。"
        );
    });

    peer.on("disconnected", function() {
        console.log("disconnected");
    });

    peer.on("close", () => {
        alert("通信が切断しました。");
    });

    peer.on("open", () => {
        console.log("peer is open");

        room = peer.joinRoom(suggestionId, {
            mode: "mesh"
        });
        room.once("open", () => {
            console.log("you joined");
        });
        room.on("peerJoin", peerId => {
            console.log(`${peerId} joined`);
        });
        room.on("data", ({ data, src }) => {
            console.log(`data called`);
            console.log(`${src}: ${data}`);
        });
        room.on("peerLeave", peerId => {
            console.log(`${peerId} left`);
        });
        room.once("close", () => {
            console.log("You left");
        });
    });

    // // Register join handler
    // joinTrigger.addEventListener("click", () => {
    //     // Note that you need to ensure the peer has connected to signaling server
    //     // before using methods of peer instance.
    //     if (!peer.open) {
    //         return;
    //     }

    //     const room = peer.joinRoom(roomId.value, {
    //         mode: getRoomModeByHash(),
    //         stream: localStream
    //     });

    //     room.once("open", () => {
    //         messages.textContent += "=== You joined ===\n";
    //     });
    //     room.on("peerJoin", peerId => {
    //         messages.textContent += `=== ${peerId} joined ===\n`;
    //     });

    //     // Render remote stream for new peer join in the room
    //     room.on("stream", async stream => {
    //         const newVideo = document.createElement("video");
    //         newVideo.srcObject = stream;
    //         newVideo.playsInline = true;
    //         // mark peerId to find it later at peerLeave event
    //         newVideo.setAttribute("data-peer-id", stream.peerId);
    //         remoteVideos.append(newVideo);
    //         await newVideo.play().catch(console.error);
    //     });

    //     room.on("data", ({ data, src }) => {
    //         // Show a message sent to the room and who sent
    //         messages.textContent += `${src}: ${data}\n`;
    //     });

    //     // for closing room members
    //     room.on("peerLeave", peerId => {
    //         const remoteVideo = remoteVideos.querySelector(
    //             `[data-peer-id="${peerId}"]`
    //         );
    //         remoteVideo.srcObject.getTracks().forEach(track => track.stop());
    //         remoteVideo.srcObject = null;
    //         remoteVideo.remove();

    //         messages.textContent += `=== ${peerId} left ===\n`;
    //     });

    //     // for closing myself
    //     room.once("close", () => {
    //         sendTrigger.removeEventListener("click", onClickSend);
    //         messages.textContent += "== You left ===\n";
    //         Array.from(remoteVideos.children).forEach(remoteVideo => {
    //             remoteVideo.srcObject
    //                 .getTracks()
    //                 .forEach(track => track.stop());
    //             remoteVideo.srcObject = null;
    //             remoteVideo.remove();
    //         });
    //     });

    //     sendTrigger.addEventListener("click", onClickSend);
    //     leaveTrigger.addEventListener("click", () => room.close(), {
    //         once: true
    //     });

    //     function onClickSend() {
    //         // Send message to all of the peers in the room via websocket
    //         room.send(localText.value);

    //         messages.textContent += `${peer.id}: ${localText.value}\n`;
    //         localText.value = "";
    //     }
    // });
})();
