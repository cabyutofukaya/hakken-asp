import React, { useState, useContext } from "react";
import { ConstContext } from "../ConstApp";
import SmallDangerModal from "../SmallDangerModal";
import { useMountedRef } from "../../../../hooks/useMountedRef";

/**
 * アカウントの有効・無効、削除
 *
 * @param {string}} userNumber 顧客番号
 * @param {int} value ステータス値
 * @param {array} permission 認可情報
 * @returns
 */
const AccountControl = ({ value, userNumber, permission }) => {
    const { agencyAccount } = useContext(ConstContext);

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
            .post(`/api/${agencyAccount}/client/person/${userNumber}/status`, {
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
            .delete(`/api/${agencyAccount}/client/person/${userNumber}`, {
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
            location.href = `/${agencyAccount}/client/person/index`;
        }
    };

    return (
        <div className="acountControl">
            <span className="inputLabel">アカウント制御</span>
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
                {permission?.delete && (
                    <>
                        <li>
                            <button
                                className="redBtn js-modal-open"
                                data-target="mdDeleteUser"
                                disabled={isDeleting}
                            >
                                削除
                            </button>
                        </li>
                    </>
                )}
            </ul>
            {/** 削除モーダル */}
            <SmallDangerModal
                id="mdDeleteUser"
                title="この顧客を削除しますか？"
                handleAction={handleDelete}
                isActioning={isDeleting}
            />
        </div>
    );
};

export default AccountControl;
