//　オブジェクトかどうかを判別
export function isObject(val) {
    if (val !== null && typeof val === "object" && val.constructor === Object) {
        return true;
    }
    return false;
}
