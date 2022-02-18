import React from "react";
import classNames from "classnames";
import { isMobile } from "react-device-detect";

const VideoTitAreaLarge = ({
    webReserveExt,
    senderTypes,
    onlineRequestStatuses,
    handleClick
}) => {
    // 取り消し
    if (
        webReserveExt.web_online_schedule?.request_status ==
        onlineRequestStatuses.online_request_status_cancel
    ) {
        return (
            <p className="videoChat">
                <span className="videoTit">キャンセル</span>
                {webReserveExt.web_online_schedule.consult_date}~
            </p>
        );
    }

    // 会社側からの日程変更リクエスト中
    if (
        webReserveExt.web_online_schedule?.requester ==
            senderTypes.sender_type_client &&
        webReserveExt.web_online_schedule?.request_status ==
            onlineRequestStatuses.online_request_status_change
    ) {
        return (
            <p className="videoChat">
                <span className="videoTit">オンライン相談日時変更依頼中</span>
                {webReserveExt.web_online_schedule.consult_date}~
            </p>
        );
    }

    // 相談日決定状態
    if (
        webReserveExt.web_online_schedule?.request_status ==
        onlineRequestStatuses.online_request_status_consent
    ) {
        return (
            <p class="videoChat alart">
                <span className="videoTit">オンライン相談ご予約日時</span>
                {/**pcの場合は画面サイズ指定、スマホの場合は別ウィンドウ */}
                {isMobile && (
                    <a
                        href={webReserveExt.web_online_schedule?.zoom_start_url}
                        target="_blank"
                    >
                        <span className="material-icons mr05">voice_chat</span>
                        {webReserveExt.web_online_schedule.consult_date}~
                    </a>
                )}
                {!isMobile && (
                    <a
                        href="#"
                        onClick={e => {
                            e.preventDefault();
                            window.open(
                                webReserveExt.web_online_schedule
                                    ?.zoom_start_url,
                                "",
                                "width=500,height=400"
                            );
                        }}
                    >
                        <span className="material-icons mr05">voice_chat</span>
                        {webReserveExt.web_online_schedule.consult_date}~
                    </a>
                )}
            </p>
        );
    }

    // 相談リクエスト or 相談日時決定状態
    return (
        <p className="videoChat">
            <span className="videoTit">オンライン相談ご希望日時</span>
            <a
                href="#"
                className={classNames({
                    "js-modal-open":
                        webReserveExt.web_online_schedule?.requester !=
                        senderTypes.sender_type_client
                })}
                data-target="mdVideoReserve"
                onClick={e => {
                    e.preventDefault();
                    handleClick({
                        ...webReserveExt.web_online_schedule
                    });
                }}
            >
                {webReserveExt.web_online_schedule.consult_date}~
            </a>
        </p>
    );
};

export default VideoTitAreaLarge;
