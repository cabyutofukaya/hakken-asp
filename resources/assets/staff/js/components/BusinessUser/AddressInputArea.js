import React, { useState } from "react";
import KenAll from "ken-all";

/**
 * 住所
 *
 * @param {*} param0
 * @returns
 */
const AddressInputArea = ({ defaultValue, formSelects }) => {
    // form初期入力値
    const initialValue = {
        zip_code: defaultValue.zip_code ?? "",
        prefecture_code: defaultValue.prefecture_code ?? "",
        address1: defaultValue.address1 ?? "",
        address2: defaultValue.address2 ?? ""
    };

    const [input, setInput] = useState(initialValue); // form入力値
    const [isSearching, setIsSearching] = useState(false); // 検索中フラグ

    const handleChange = e => {
        if (e.target.name === "zip_code") {
            // 郵便番号の場合は入力値を数字のみに制御
            const re = /^[0-9\b]+$/;
            if (e.target.value === "" || re.test(e.target.value)) {
                setInput({ ...input, [e.target.name]: e.target.value });
            }
        } else {
            // 郵便番号以外の入力
            setInput({ ...input, [e.target.name]: e.target.value });
        }
    };

    // 住所検索
    const handleSearch = async e => {
        e.preventDefault();

        if (!input.zip_code) {
            alert("郵便番号を入力してください");
            return;
        }
        if (!/^[0-9]{7}$/.test(input.zip_code)) {
            alert("郵便番号の入力形式が正しくありません（半角数字7桁）");
            return;
        }

        if (isSearching) return;

        // 住所データ初期化
        let adr = {
            prefecture_code: "",
            address1: "",
            address2: ""
        };

        setIsSearching(true); // 検索中On

        const response = await KenAll(input.zip_code).finally(() => {
            setIsSearching(false); // 検索中Off
        });

        if (response && response.length > 0) {
            let address = response[0];
            let code = Object.keys(_.get(formSelects, "prefectures")).find(
                code => formSelects.prefectures[code] === address[0]
            );

            adr.prefecture_code = code;
            adr.address1 = `${address[1]}${address[2]}`;
        }

        setInput({ ...input, ...adr }); // 結果データをセット
    };

    return (
        <>
            <li className="wd40">
                <span className="inputLabel">郵便番号</span>
                <div className="buttonSet">
                    <input
                        type="text"
                        name="zip_code"
                        value={input.zip_code}
                        onChange={handleChange}
                        className="wd60"
                        maxLength="7"
                        placeholder="ハイフン無し7桁で登録"
                    />
                    <button
                        className="orangeBtn wd40"
                        onClick={handleSearch}
                        disabled={isSearching}
                    >
                        検索
                    </button>
                </div>
            </li>
            <li>
                <span className="inputLabel">住所</span>
                <div className="selectSet">
                    <div className="selectBox wd20">
                        <select
                            name="prefecture_code"
                            value={input.prefecture_code}
                            onChange={handleChange}
                        >
                            {formSelects?.prefectures &&
                                Object.keys(formSelects.prefectures)
                                    .sort((a, b) => a - b)
                                    .map((v, index) => (
                                        <option key={index} value={v}>
                                            {formSelects.prefectures[v]}
                                        </option>
                                    ))}
                        </select>
                    </div>
                    <input
                        type="text"
                        className="wd80"
                        name="address1"
                        value={input.address1}
                        onChange={handleChange}
                        maxLength="100"
                    />
                </div>
            </li>
            <li>
                <span className="inputLabel">ビル・建物名</span>
                <input
                    type="text"
                    name="address2"
                    value={input.address2}
                    onChange={handleChange}
                    maxLength="100"
                />
            </li>
        </>
    );
};

export default AddressInputArea;
