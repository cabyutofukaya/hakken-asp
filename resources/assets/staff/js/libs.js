import moment from "moment";

/**
 * 税込価格を計算
 * 小数点以下は四捨五入
 * @param {*} nonTaxed 税抜き価格
 * @param {*} taxRate 税率
 */
export function calcTaxInclud(nonTaxed, taxRate) {
    if (/^[-]?\d*$/.test(nonTaxed) && /^[-]?\d*$/.test(taxRate)) {
        // 引数がともに数字であれば処理
        const tax = 1 + Number(taxRate) / 100;
        const num = Number(nonTaxed) * tax;
        return Math.round(num);
    }
    return nonTaxed;
}

/**
 * Net単価を計算
 * 小数点以下は四捨五入
 * @param {*} cost 仕入
 * @param {*} commissionRate 手数料率
 */
export function calcNet(cost, commissionRate) {
    if (/^[-]?\d*$/.test(cost) && /^[-]?\d*$/.test(commissionRate)) {
        // 引数がともに数字であれば処理
        const rate = Number(commissionRate) / 100;
        const num = Number(cost) * rate;
        return cost - Math.round(num);
    }
    return cost;
}

/**
 * 粗利を計算
 * 小数点以下は四捨五入でOK（確認済）
 * @param {*} gross 税込単価
 * @param {*} net NET単価
 */
export function calcGrossProfit(gross, net) {
    if (/^[-]?\d*$/.test(gross) && /^[-]?\d*$/.test(net)) {
        // 引数がともに数字であれば処理
        return Number(gross) - Number(net);
    }
    return gross;
}

/**
 * 利益率を計算
 *
 * @param {*} profit
 * @param {*} Sales
 */
export function calcProfitRate(profit, sales) {
    if (sales === 0) return 0;
    return (profit / sales) * 100;
}

/**
 * URLから会社アカウントを取得
 *
 * @returns
 */
export function getAgencyAccountFromUrl() {
    const path = location.pathname.replace(/^\/+|\/+$/g, ""); // pathnameの前後のスラッシュを削除
    const arr = path.split("/");
    return arr[0] ? arr[0] : null;
}

/**
 * GETパラメータを取得
 *
 * @param {*} name
 * @param {*} url
 * @returns
 */
export function getParam(name, url) {
    if (!url) url = window.location.href;
    name = name.replace(/[\[\]]/g, "\\$&");
    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return "";
    return decodeURIComponent(results[2].replace(/\+/g, " "));
}

// name_exのjsonをobjectに変換
export function getNameExObj(str) {
    try {
        const obj = JSON.parse(str);
        return {
            label: `${obj.code} ${obj.name}`,
            value: obj.id
        };
    } catch (error) {}
    return { label: "", value: "" };
}

/**
 * ブラケットの配列表記をドットに直してパス文字列を作成
 * 前後の[]をトリムして、 ][ と [ 、 ] をドットに置換
 *
 * aaa[bbb][ccc] → aaa.bbb.cccs
 */
export function getPathFromBracketName(name) {
    return name
        .replace(/^\[|\]$/, "")
        .replace(/\]\[/g, ".")
        .replace(/\[|\]/g, ".");
}

//オブジェクトが空かどうか
export function isEmptyObject(obj) {
    return !Object.keys(obj).length;
}

// 帰着日が本日以降の場合はtrue
export function checkReturnDate(returnDate) {
    const rd = new Date(returnDate);
    const dt = new Date();

    return moment({
        year: rd.getFullYear(),
        month: rd.getMonth(),
        day: rd.getDate()
    }).isSameOrAfter({
        year: dt.getFullYear(),
        month: dt.getMonth(),
        day: dt.getDate()
    });
}
