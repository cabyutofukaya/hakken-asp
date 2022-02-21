import React, { useState, useEffect, useContext } from "react";
import { ConstContext } from "../ConstApp";
import classNames from "classnames";
import { useMountedRef } from "../../../../hooks/useMountedRef";
import ReserveAmountBreakdown from "./ReserveAmountBreakdown";
import WebReserveBasicInfoLeft from "./WebReserveBasicInfoLeft";
import BackToIndexButton from "./BackToIndexButton";

const WebReserveBasicInfoArea = ({
    isShow,
    reserveNumber,
    isDeparted,
    status,
    consts,
    constsCommon,
    customFields,
    permission
}) => {
    const { agencyAccount } = useContext(ConstContext);

    const mounted = useMountedRef(); // マウント・アンマウント制御

    const [data, setData] = useState({});

    const [isLoading, setIsLoading] = useState(false); // リスト取得中

    const fetch = async () => {
        // 表示状態になったらデータ取得
        if (!mounted.current) return;
        if (isLoading) return;

        setIsLoading(true); // 二重読み込み禁止

        const response = await axios
            .get(`/api/${agencyAccount}/web/reserve/${reserveNumber}`)
            .finally(() => {
                if (mounted.current) {
                    setIsLoading(false);
                }
            });

        if (mounted.current && response?.data?.data) {
            setData({ ...response.data.data });
        }
    };
    useEffect(() => {
        if (isShow) {
            fetch();
        }
    }, [isShow]);

    return (
        <div
            className={classNames("userList", {
                show: isShow
            })}
        >
            <ul className="sideList half">
                <li>
                    <h2>
                        <span className="material-icons">subject</span>
                        基本情報
                    </h2>
                    <WebReserveBasicInfoLeft
                        data={data}
                        customFields={customFields}
                        customFieldPositions={consts.customFieldPositions}
                    />
                </li>
                <li>
                    <h2>
                        <span className="material-icons">app_registration</span>
                        予約金額内訳
                    </h2>
                    <ReserveAmountBreakdown reserveData={data} />
                    <ul className="documentList">
                        {data.reserve_confirm?.url && (
                            <li>
                                <a
                                    href={data.reserve_confirm.url}
                                    className="normalBtn"
                                >
                                    <span className="material-icons">
                                        description
                                    </span>
                                    {data.reserve_confirm.label ?? "-"}
                                </a>
                            </li>
                        )}
                        {data.itinerary?.url && (
                            <li>
                                <a
                                    href={data.itinerary.url}
                                    className="normalBtn"
                                >
                                    <span className="material-icons">
                                        description
                                    </span>
                                    {data.itinerary.label ?? "-"}
                                </a>
                            </li>
                        )}
                        {data.invoice?.url && (
                            <li>
                                <a
                                    href={data.invoice.url}
                                    className="normalBtn"
                                >
                                    <span className="material-icons">
                                        description
                                    </span>
                                    {data.invoice.label ?? "-"}
                                </a>
                            </li>
                        )}
                        {data.receipt?.url && (
                            <li>
                                <a
                                    href={data.receipt.url}
                                    className="normalBtn"
                                >
                                    <span className="material-icons">
                                        description
                                    </span>
                                    {data.receipt.label ?? "-"}
                                </a>
                            </li>
                        )}
                    </ul>
                </li>
            </ul>
            <h2 className="mt40">
                <span className="material-icons">playlist_add_check</span>
                予約管理情報
            </h2>
            <ul className="sideList half">
                <li>
                    <table className="baseTable">
                        <tbody>
                            <tr>
                                <th>自社担当</th>
                                <td>{data?.manager?.name ?? "-"}&nbsp;</td>
                            </tr>
                            <tr>
                                <th>ステータス</th>
                                <td>
                                    <span
                                        className="status blue js-modal-open"
                                        data-target="mdStatus"
                                    >
                                        {status}
                                        <span className="material-icons settingIcon">
                                            settings
                                        </span>
                                    </span>
                                </td>
                            </tr>
                            {/*  カスタム項目（左列） */}
                            {customFields?.[
                                consts?.customFieldPositions?.estimates_custom
                            ]["left"] &&
                                Object.keys(
                                    customFields[
                                        consts.customFieldPositions
                                            .estimates_custom
                                    ]["left"]
                                ).map((key, index) => (
                                    <tr key={index}>
                                        <th>
                                            {
                                                customFields[
                                                    consts.customFieldPositions
                                                        .estimates_custom
                                                ]["left"][key]?.name
                                            }
                                        </th>
                                        <td>
                                            {customFields[
                                                consts.customFieldPositions
                                                    .estimates_custom
                                            ]["left"][key].val ?? "-"}
                                        </td>
                                    </tr>
                                ))}
                        </tbody>
                    </table>
                </li>
                <li>
                    <table className="baseTable">
                        <tbody>
                            {/*  カスタム項目（右列） */}
                            {customFields?.[
                                consts?.customFieldPositions?.estimates_custom
                            ]["right"] &&
                                Object.keys(
                                    customFields[
                                        consts.customFieldPositions
                                            .estimates_custom
                                    ]["right"]
                                ).map((key, index) => (
                                    <tr key={index}>
                                        <th>
                                            {
                                                customFields[
                                                    consts.customFieldPositions
                                                        .estimates_custom
                                                ]["right"][key]?.name
                                            }
                                        </th>
                                        <td>
                                            {customFields[
                                                consts.customFieldPositions
                                                    .estimates_custom
                                            ]["right"][key].val ?? "-"}
                                        </td>
                                    </tr>
                                ))}
                        </tbody>
                    </table>
                </li>
            </ul>
            <ul id="formControl">
                <li className="wd50">
                    <BackToIndexButton
                        isDeparted={isDeparted}
                        reserveIndexUrl={constsCommon?.reserveIndexUrl}
                        departedIndexUrl={constsCommon?.departedIndexUrl}
                    />
                </li>
                {/**催行済みの場合は編集リンクなし */}
                {!isDeparted && permission.reserve_update && (
                    <li className="wd50">
                        <button
                            className="blueBtn"
                            onClick={e => {
                                e.preventDefault();
                                window.location.href = consts.reserveEditUrl;
                            }}
                        >
                            予約基本情報を編集する
                        </button>
                    </li>
                )}
            </ul>
        </div>
    );
};

export default WebReserveBasicInfoArea;
