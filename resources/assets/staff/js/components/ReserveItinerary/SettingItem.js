import _ from "lodash";
import React, { useContext } from "react";
import { ConstContext } from "../ConstApp";
import { ReserveItineraryConstContext } from "../ReserveItineraryConstApp";

// 一覧取得API URL
const getPdBasefUrl = (
    reception,
    step,
    types,
    agencyAccount,
    estimateNumber,
    reserveNumber
) => {
    switch (step) {
        case types.application_step_draft: // 見積
            return `/${agencyAccount}/estimates/${reception}/${step}/${estimateNumber}/itinerary/rooming_list/pdf`;
        case types.application_step_reserve: // 予約
            return `/${agencyAccount}/estimates/${reception}/${step}/${reserveNumber}/itinerary/rooming_list/pdf`;
        default:
            return null;
    }
};

/**
 * 仕入科目リストの「設定項目」枠
 *
 * @param {*} param0
 * @returns
 */
const SettingItem = ({ date, item } = {}) => {
    const {
        reception,
        applicationStep,
        applicationStepList,
        estimateNumber,
        reserveNumber,
        subjectCategoryTypes
    } = useContext(ReserveItineraryConstContext);

    const { agencyAccount, purchaseNormal } = useContext(ConstContext);

    const pdfBaseUrl = getPdBasefUrl(
        reception,
        applicationStep,
        applicationStepList,
        agencyAccount,
        estimateNumber,
        reserveNumber
    );

    if (item.subject !== subjectCategoryTypes.hotel) return <>-</>;

    // 有効参加者が一人もいない場合はリンク出力ナシ。条件は仕入タイプがpurchaseNormalで有効仕入
    if (
        item.subject === subjectCategoryTypes.hotel &&
        !_.some(
            item?.participants,
            p => p?.purchase_type == purchaseNormal && p?.valid
        )
    )
        return <>-</>;

    return (
        <>
            {item.subject === subjectCategoryTypes?.hotel && (
                <a
                    className="btn"
                    target="_blank"
                    href={
                        `${pdfBaseUrl}?dt=${date}&hotel_name=${item?.hotel_name ??
                            ""}&` +
                        item?.participants
                            .map(p => {
                                if (
                                    p?.purchase_type == purchaseNormal &&
                                    p?.valid
                                ) {
                                    return `rn[]=${p?.room_number ??
                                        ""}&pi[]=${p?.participant_id ?? ""}`;
                                }
                            })
                            .filter(v => v)
                            .join("&")
                    }
                >
                    <span className="material-icons">picture_as_pdf</span>
                    ルーミングリスト
                </a>
            )}
        </>
    );
};

export default SettingItem;
