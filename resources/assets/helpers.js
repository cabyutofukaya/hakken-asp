import React from "react";
import moment from "moment";

// 生年月日から年齢を計算
export function birth2age(y, m, d) {
    if (y && m && d) {
        let ymd =
            ("000" + y).slice(-4) +
            "-" +
            ("0" + m).slice(-2) +
            "-" +
            ("0" + d).slice(-2);
        if (moment(ymd).isValid()) {
            let dateOfBirth = moment(new Date(ymd)); // 生年月日
            let today = moment(new Date()); // 今日の日付

            let baseAge = today.year() - dateOfBirth.year();

            let birthday = moment(
                new Date(
                    today.year() +
                        "-" +
                        (dateOfBirth.month() + 1) +
                        "-" +
                        dateOfBirth.date()
                )
            );

            // 今日が誕生日より前の日付である場合、算出した年齢から-1した値を返す
            if (today.isBefore(birthday)) {
                return baseAge - 1 + "歳";
            }

            return baseAge + "歳";
        }
    }
    return null;
}

// 7桁の数字を郵便番号形式に変換(XXX-XXXX)
export function toPostFormat(str) {
    if (str && str.trim().length == 7) {
        const s = str.trim();
        const h = s.substr(0, 3);
        const m = s.substr(3);
        return `${h}-${m}`;
    }
    return null;
}

// 「都道府県+住所1+住所2」の文字列を生成
export function concatAdress(obj) {
    if (
        !obj?.userable?.prefecture?.name &&
        !obj?.userable?.address1 &&
        !obj?.userable?.address2
    )
        return "-";
    return (
        (obj?.userable?.prefecture?.name ?? "") +
        (obj?.userable?.address1 ?? "") +
        (obj?.userable?.address2 ?? "")
    );
}

// 改行コードをbrに変換
export function nl2br(str) {
    return (
        <>
            {str.split("\n").map((s, index) => (
                <React.Fragment key={index}>
                    {s}
                    <br />
                </React.Fragment>
            ))}
        </>
    );
}
