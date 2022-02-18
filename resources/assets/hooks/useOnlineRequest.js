import { useState } from "react";
import moment from "moment";
import { useMountedRef } from "./useMountedRef";

/**
 * オンライン相談依頼関連
 *
 * @param {*} callback API実行後に呼び出す処理
 * @returns
 */
export function useOnlineRequest(agencyAccount, callback) {
    const mounted = useMountedRef(); // マウント・アンマウント制御

    const [isOnlineRequesting, setIsOnlineRequesting] = useState(false);
    const [onlineRequestValues, setOnlineRequestValues] = useState({}); // 表示用データ
    const [onlineRequestInputValues, setOnlineRequestInputValues] = useState(); // 入力用データ
    const handleOnlineRequestInputChange = e => {
        setOnlineRequestInputValues({
            ...onlineRequestInputValues,
            [e.target.name]: e.target.value
        });
    }; //入力制御

    const handleOnlineRequestClick = row => {
        setOnlineRequestValues(row);
        setOnlineRequestInputValues({
            is_change: false, // 日時変更切り替えフラグ
            web_reserve_ext_id: row.web_reserve_ext_id,
            reserve_id: row.reserve_id,
            consult_date: moment(row.consult_date, "YYYY/MM/DD HH:mm").format(
                "YYYY/MM/DD"
            ),
            hour: moment(row.consult_date, "YYYY/MM/DD HH:mm").format("HH"),
            minute: moment(row.consult_date, "YYYY/MM/DD HH:mm").format("mm")
        });
    };

    // 変更依頼を押した時の挙動
    const handleChangeOnlineRequest = async e => {
        e.preventDefault();

        if (!mounted.current) return;
        if (isOnlineRequesting) return;

        setIsOnlineRequesting(true);

        const param = { ...onlineRequestInputValues, _method: "put" }; // PUTリクエスト

        const response = await axios
            .post(
                `/api/${agencyAccount}/web/webreserveext/${param.web_reserve_ext_id}/online/change_request`,
                param
            )
            .finally(() => {
                $(".js-modal-close").trigger("click"); // 削除モーダルclose
                setTimeout(function() {
                    if (mounted.current) {
                        setIsOnlineRequesting(false);
                    }
                }, 3000);
            });

        if (mounted.current && response?.data?.data) {
            const schedule = response.data.data;
            // Web予約行に相談日程情報をセット
            callback(schedule);
        }
    };

    /**
     * オンライン相談承諾処理
     */
    const handleConsentRequest = async e => {
        e.preventDefault();

        if (!mounted.current) return;
        if (isOnlineRequesting) return;

        setIsOnlineRequesting(true);

        const response = await axios
            .put(
                `/api/${agencyAccount}/web/webreserveext/online/${onlineRequestValues.id}/consent_request`,
                { _method: "put" }
            )
            .finally(() => {
                $(".js-modal-close").trigger("click"); // 削除モーダルclose
                setTimeout(function() {
                    if (mounted.current) {
                        setIsOnlineRequesting(false);
                    }
                }, 3000);
            });

        if (mounted.current && response?.data?.data) {
            const schedule = response.data.data;
            // Web予約行に相談日程情報をセット
            callback(schedule);
        }
    };

    return [
        isOnlineRequesting,
        onlineRequestValues,
        onlineRequestInputValues,
        handleOnlineRequestInputChange,
        handleOnlineRequestClick,
        handleChangeOnlineRequest,
        handleConsentRequest
    ];
}
