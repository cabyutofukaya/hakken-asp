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
 * @param {array} statuses ステータス選択値
 * @returns
 */
const AccountControl = ({ value, userNumber, permission, statuses }) => {
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
            .post(
                `/api/${agencyAccount}/client/business/${userNumber}/status`,
                {
                    status: newStatus,
                    _method: "put"
                }
            )
            .finally(() => {
                if (mounted.current) {
                    setIsChanging(false);
                }
            });

        if (mounted.current && response) {
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
            .delete(`/api/${agencyAccount}/client/business/${userNumber}`, {
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

        if (response) {
            // 処理成功後はindexページへリダイレクト
            location.href = `/${agencyAccount}/client/business/index`;
        }
    };

    return (
        <div className="acountControl">
            <span className="inputLabel">アカウント制御</span>
            <ul className="slideRadio">
                <>
                    {statuses &&
                        Object.keys(statuses).map((val, index) => (
                            <li key={index}>
                                <input
                                    type="radio"
                                    id={`userStatus${index}`}
                                    name="status"
                                    value={val}
                                    checked={val == status}
                                    onChange={handleChange}
                                    disabled={isChanging}
                                />
                                <label htmlFor={`userStatus${index}`}>
                                    {statuses[val]}
                                </label>
                            </li>
                        ))}
                </>
                {permission?.delete && (
                    <>
                        <li>
                            <button
                                className="redBtn js-modal-open"
                                data-target="mdDeleteBusinessUser"
                            >
                                削除
                            </button>
                        </li>
                    </>
                )}
            </ul>
            {/** 削除モーダル */}
            <SmallDangerModal
                id="mdDeleteBusinessUser"
                title="この顧客を削除しますか？"
                handleAction={handleDelete}
                isActioning={isDeleting}
            />
        </div>
    );
};

export default AccountControl;
