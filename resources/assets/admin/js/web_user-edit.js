import React, { useState } from "react";
import { render } from "react-dom";
import SmallDangerModal from "./components/SmallDangerModal";
import { useMountedRef } from "../../hooks/useMountedRef";

const AccountControl = ({ webUserId, value }) => {
    const mounted = useMountedRef(); // マウント・アンマウント制御

    const [status, setStatus] = useState(value);

    const [isChanging, setIsChanging] = useState(false); // ステータス変更中か否か
    const [isDeleting, setIsDeleting] = useState(false); // 削除処理中か否か

    // ステータス変更
    const handleChange = async e => {
        if (!mounted.current) return;
        if (isChanging) return;
        setIsChanging(true); // 処理が完了するまでクリック禁止

        const newStatus = e.target.value;

        const response = await axios
            .post(`/api/web_user/${webUserId}/status`, {
                status: newStatus,
                _method: "put"
            })
            .finally(() => {
                setIsChanging(false);
            });

        if (mounted.current && response?.status == 200) {
            // ステータス更新成功
            setStatus(newStatus);
        }
    };

    // 削除処理
    const handleDelete = async e => {
        if (!mounted.current) return;
        if (isDeleting) return;

        setIsDeleting(true);

        const response = await axios
            .delete(`/api/web_user/${webUserId}`, {
                data: {
                    set_message: true // API処理完了後、flashメッセージセットを要求
                }
            })
            .finally(() => {
                $(".js-modal-close").trigger("click"); // 削除モーダルclose
                setTimeout(function() {
                    if (mounted.current) {
                        setIsDeleting(false);
                    }
                }, 3000);
            });

        if (response?.status == 200) {
            // 処理成功後はindexページへリダイレクト
            location.href = `/web_users`;
        }
    };

    return (
        <>
            <div className="acountControl">
                <span className="inputLabel">アカウント制御(ログイン)</span>
                <ul className="slideRadio">
                    <li>
                        <input
                            type="radio"
                            id="status1"
                            name="status"
                            value={1}
                            checked={1 == status}
                            onChange={handleChange}
                            disabled={isChanging}
                        />
                        <label htmlFor="status1">有効</label>
                    </li>
                    <li>
                        <input
                            type="radio"
                            id="status0"
                            name="status"
                            value={0}
                            checked={0 == status}
                            onChange={handleChange}
                            disabled={isChanging}
                        />
                        <label htmlFor="status0">無効</label>
                    </li>
                    <li>
                        <button
                            className="redBtn js-modal-open"
                            data-target="mdDeleteWebUser"
                            disabled={isDeleting}
                        >
                            削除
                        </button>
                    </li>
                </ul>
            </div>
            <SmallDangerModal
                id="mdDeleteWebUser"
                title="ユーザーを削除しますか？"
                handleAction={handleDelete}
                isActioning={isDeleting}
            />
        </>
    );
};

const Element = document.getElementById("acountControl");
if (Element) {
    const webUserId = Element.getAttribute("webUserId");
    const status = Element.getAttribute("status");

    render(
        <AccountControl webUserId={webUserId} value={status} />,
        document.getElementById("acountControl")
    );
}
