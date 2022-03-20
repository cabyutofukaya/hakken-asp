import React, { useState, useEffect, useContext } from "react";
import { ConstContext } from "../ConstApp";
import classNames from "classnames";
import { useMountedRef } from "../../../../hooks/useMountedRef";
import DetermineModal from "./DetermineModal";
import ReserveAmountBreakdown from "./ReserveAmountBreakdown";

const EstimateBasicInfoArea = ({
    isShow,
    estimateNumber,
    status,
    setStatus,
    updatedAt,
    setUpdatedAt,
    consts,
    constsCommon,
    customFields,
    permission,
    errorMessage,
    setErrorMessage
}) => {
    const { agencyAccount } = useContext(ConstContext);

    const mounted = useMountedRef(); // マウント・アンマウント制御

    const [data, setData] = useState({});

    const [isLoading, setIsLoading] = useState(false); // リスト取得中
    const [isConfirming, setIsConfirming] = useState(false); // 確定処理中か否か

    const fetch = async () => {
        // 表示状態になったらデータ取得
        if (!mounted.current) return;
        if (isLoading) return;

        setIsLoading(true); // 二重読み込み禁止

        const response = await axios
            .get(`/api/${agencyAccount}/estimate/${estimateNumber}`)
            .finally(() => {
                setIsLoading(false);
            });

        if (mounted.current && response?.data?.data) {
            setData({ ...response.data.data });
            // 最新ステータスと予約情報更新日時をセット
            setStatus(response.data.data.status?.val);
            setUpdatedAt(response.data.data.updated_at);
        }
    };
    useEffect(() => {
        if (isShow) {
            fetch();
        }
    }, [isShow]);

    return (
        <>
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
                        <table className="baseTable">
                            <tbody>
                                <tr>
                                    <th>見積番号</th>
                                    <td>{data?.estimate_number ?? "-"}</td>
                                </tr>
                                <tr>
                                    <th>顧客種別</th>
                                    <td>
                                        {data?.applicant
                                            ?.applicant_type_label ?? "-"}
                                    </td>
                                </tr>
                                <tr>
                                    <th>申込者</th>
                                    <td>
                                        <>
                                            {!data?.applicant?.is_deleted && (
                                                <a
                                                    href={
                                                        data?.applicant
                                                            ?.detail_url
                                                    }
                                                >
                                                    {data?.applicant
                                                        ?.user_number ?? "-"}
                                                </a>
                                            )}
                                            {data?.applicant?.is_deleted &&
                                                (data?.applicant?.user_number ??
                                                    "-")}
                                            <br />
                                            {data?.applicant?.name ?? "-"}(
                                            {data?.applicant?.name_kana ?? "-"})
                                        </>
                                    </td>
                                </tr>
                                <tr>
                                    <th>案件名</th>
                                    <td>{data?.name ?? "-"}</td>
                                </tr>
                                <tr>
                                    <th>旅行種別&nbsp;</th>
                                    <td>{data?.travel_type?.val ?? "-"}</td>
                                </tr>
                                <tr>
                                    <th>出発日</th>
                                    <td>{data?.departure_date ?? "-"}</td>
                                </tr>
                                <tr>
                                    <th>帰着日</th>
                                    <td>{data?.return_date ?? "-"}</td>
                                </tr>
                                <tr>
                                    <th>出発地</th>
                                    <td>{data?.departure ?? "-"}</td>
                                </tr>
                                <tr>
                                    <th>目的地</th>
                                    <td>{data?.destination ?? "-"}</td>
                                </tr>
                                {/**(システム管理コード無しの)基本情報のカスタム項目*/}
                                {_.filter(
                                    customFields?.[
                                        consts.customFieldPositions
                                            .estimates_base
                                    ],
                                    {
                                        code: null
                                    }
                                ) &&
                                    _.filter(
                                        customFields[
                                            consts.customFieldPositions
                                                .estimates_base
                                        ],
                                        {
                                            code: null
                                        }
                                    ).map((row, index) => (
                                        <tr key={index}>
                                            <th>{row.name ?? "-"}</th>
                                            <td>{row.val ?? "-"}</td>
                                        </tr>
                                    ))}
                                <tr>
                                    <th>備考</th>
                                    <td>{data?.note ?? "-"}</td>
                                </tr>
                            </tbody>
                        </table>
                    </li>
                    <li>
                        <h2>
                            <span className="material-icons">
                                app_registration
                            </span>
                            見積金額内訳
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
                        </ul>
                    </li>
                </ul>
                <h2 className="mt40">
                    <span className="material-icons">playlist_add_check</span>
                    見積管理情報
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
                                    consts?.customFieldPositions
                                        ?.estimates_custom
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
                                                        consts
                                                            .customFieldPositions
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
                                    consts?.customFieldPositions
                                        ?.estimates_custom
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
                                                        consts
                                                            .customFieldPositions
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
                {permission.reserve_update && (
                    <div
                        className="changeStatus js-modal-open"
                        data-target="mdChangeStatus"
                    >
                        <button
                            className="blueBtn"
                            disabled={!data || isConfirming}
                        >
                            <span className="material-icons">verified</span>
                            この見積を予約確定する
                        </button>
                    </div>
                )}
                <ul id="formControl">
                    <li className="wd50">
                        <button
                            className="grayBtn"
                            onClick={e => {
                                e.preventDefault();
                                window.location.href =
                                    constsCommon?.estimateIndexUrl;
                            }}
                        >
                            <span className="material-icons">
                                arrow_back_ios
                            </span>
                            一覧に戻る
                        </button>
                    </li>
                    {permission.reserve_update && (
                        <li className="wd50">
                            <button
                                className="blueBtn"
                                onClick={e => {
                                    e.preventDefault();
                                    window.location.href =
                                        consts.estimateEditUrl;
                                }}
                            >
                                見積基本情報を編集する
                            </button>
                        </li>
                    )}
                </ul>
            </div>

            <DetermineModal
                id="mdChangeStatus"
                estimate={data}
                updatedAt={updatedAt}
                isConfirming={isConfirming}
                setIsConfirming={setIsConfirming}
                determineUrl={consts.determineUrl}
                reserveIndexUrl={consts.reserveIndexUrl}
                departedIndexUrl={consts.departedIndexUrl}
            />
        </>
    );
};

export default EstimateBasicInfoArea;
