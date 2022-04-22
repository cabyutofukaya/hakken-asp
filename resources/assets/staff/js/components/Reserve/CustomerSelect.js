import React, { useState, useEffect, useCallback, useContext } from "react";
import { ConstContext } from "../ConstApp";
import { useMountedRef } from "../../../../hooks/useMountedRef";
import ReactLoading from "react-loading";
import AddUserModal from "./AddUserModal";
import classNames from "classnames";
import { getPathFromBracketName } from "../../libs";

/**
 *
 * @param {object} customerConsts 顧客区分データ
 * @param {object} customerKbns 顧客区分定数値
 * @param {string} customerType participant_type値
 * @param {function} handleChange 顧客区分変更時イベント
 * @param {function} clearUserNumber userNumberクリア処理
 * @returns
 */
const CustomerSelect = ({
    customerTypes,
    customerKbns,
    customerType,
    countries,
    sexes,
    ageKbns,
    birthdayYears,
    birthdayMonths,
    birthdayDays,
    prefectures,
    userNumber,
    searchUserNumber,
    searchUserName,
    getDeleted,
    handleChange,
    handleCustomerTypeChange,
    clearUserNumber,
    userAddModalDefaultValue
}) => {
    const { agencyAccount } = useContext(ConstContext);

    const mounted = useMountedRef(); // マウント・アンマウント制御

    const [rows, setRows] = useState([]);

    const [isLoading, setIsLoading] = useState(false); // リスト取得中
    const [isLoaded, setIsLoaded] = useState(false); // リスト取得完了

    /////// ユーザー追加関連
    const [input, setInput] = useState(userAddModalDefaultValue); // 入力値
    const [isEditing, setIsEditing] = useState(false); // 編集処理中

    // 登録・編集におけるinput制御
    const handleModalInputChange = e => {
        /**
         * ブラケットの配列表記をドットに直してパスを作成
         * 前後の[]をトリムして、[]をドットに置換
         *
         * aaa[bbb][ccc] → aaa.bbb.cccs
         */
        const name = getPathFromBracketName(e.target.name);

        let data = _.cloneDeep(input);
        _.set(data, name, e.target.value);

        setInput({ ...data });
    };
    // 住所の検索結果をセット
    const handleAddressSearchResult = address => {
        const userable = input.userable;
        setInput({ ...input, userable: { ...userable, ...address } });
    };

    // 新規登録モーダル
    const handleModalAdd = useCallback(e => {
        e.preventDefault();
        setInput(userAddModalDefaultValue); // デフォルト値で初期化
    }, []);
    // 登録処理
    const handleSubmit = async e => {
        e.preventDefault();

        if (!mounted.current) return;
        if (isEditing) return;
        setIsEditing(true); // 多重処理制御

        let response = null;
        //新規登録
        response = await axios
            .post(`/api/${agencyAccount}/client/person`, {
                ...input
            })
            .finally(() => {
                $(".js-modal-close").trigger("click"); // モーダルclose
                setTimeout(function() {
                    if (mounted.current) {
                        setIsEditing(false);
                    }
                }, 3000);
            });

        if (esponse?.data?.data) {
            if (searchUserName || searchUserNumber) {
                fetch(); //登録完了後、検索フィールドが空でなければ検索リストを更新
            }
        }
    };
    ////////////////

    /**
     *
     * @param {booleam} isClearUserNumber 検索後、userNumberをクリアする場合はtrue
     * @returns
     */
    const fetch = async (isClearUserNumber = true) => {
        if (!mounted.current) return;
        if (isLoading) return;

        if (!searchUserName && !searchUserNumber) {
            // 検索フレーズがない場合はリストクリア
            setRows([]);
            return;
        }

        let result = [];
        setIsLoading(true); // 二重読み込み禁止
        setIsLoaded(false);

        const response = await axios
            .get(`/api/${agencyAccount}/participant/search`, {
                params: {
                    participant_type: customerType,
                    user_number: searchUserNumber,
                    name: searchUserName,
                    get_deleted: getDeleted
                }
            })
            .finally(() => {
                setIsLoading(false);
                setIsLoaded(true); // 取得処理完了
            });
        if (response?.data?.data) {
            result = [...response.data.data];
        }
        if (mounted.current) {
            setRows(result); // 検索結果をrowsにセット&userNumber初期化

            if (isClearUserNumber) {
                clearUserNumber();
            }
        }
    };

    // 検索ボタン
    const handleSearch = e => {
        e.preventDefault();
        fetch();
    };

    // 法人・個人切り替え
    const handleSelectChange = e => {
        handleCustomerTypeChange(e); // 親のhandleChange関数をcall
        setRows([]);
    };

    useEffect(() => {
        fetch(false);
    }, []);

    return (
        <>
            <ul className="sideList mb20">
                <li className="wd15">
                    <span className="inputLabel">個人/法人</span>
                    <div className="selectBox">
                        <select
                            name="participant_type"
                            value={customerType ?? ""}
                            onChange={handleSelectChange}
                        >
                            {customerTypes &&
                                Object.keys(customerTypes).map((val, index) => (
                                    <option key={index} value={val}>
                                        {customerTypes[val]}
                                    </option>
                                ))}
                        </select>
                    </div>
                </li>
                <li className="wd20">
                    <span className="inputLabel">顧客番号</span>
                    <input
                        type="text"
                        name="search_user_number"
                        value={searchUserNumber ?? ""}
                        onChange={handleChange}
                        placeholder="ID111"
                    />
                </li>
                <li className="wd40">
                    <span className="inputLabel">顧客名</span>
                    <div className="buttonSet">
                        <input
                            type="text"
                            name="search_user_name"
                            value={searchUserName ?? ""}
                            onChange={handleChange}
                            className="mr10"
                            placeholder="ヤマダタロウ"
                        />
                        <button
                            className="orangeBtn wd30"
                            onClick={handleSearch}
                        >
                            検索
                        </button>
                    </div>
                </li>
                <li className="wd20 mr00">
                    <span className="inputLabel">新規顧客登録</span>
                    <div className="buttonSet">
                        <button
                            className="blueBtn js-modal-open"
                            data-target="mdAddUser"
                            onClick={handleModalAdd}
                        >
                            個人
                        </button>
                        <button
                            onClick={e => {
                                e.preventDefault();
                                location.href = `/${agencyAccount}/client/business/create`;
                            }}
                            className="blueBtn"
                        >
                            法人
                        </button>
                    </div>
                </li>
            </ul>
            <div className="tableWrap dragTable">
                <div className="tableCont">
                    <table>
                        {customerType === customerKbns.business && (
                            <thead>
                                <tr>
                                    <th className="txtalc wd10">
                                        <span>選択</span>
                                    </th>
                                    <th>
                                        <span>顧客番号</span>
                                    </th>
                                    <th>
                                        <span>法人名</span>
                                    </th>
                                    <th>
                                        <span>担当者名</span>
                                    </th>
                                    <th>
                                        <span>部署</span>
                                    </th>
                                    <th>
                                        <span>都道府県</span>
                                    </th>
                                    <th>
                                        <span>メールアドレス</span>
                                    </th>
                                    <th>
                                        <span>電話番号</span>
                                    </th>
                                    <th>
                                        <span>顧客区分</span>
                                    </th>
                                </tr>
                            </thead>
                        )}
                        {customerType === customerKbns.person && (
                            <thead>
                                <tr>
                                    <th className="txtalc wd10">
                                        <span>選択</span>
                                    </th>
                                    <th>
                                        <span>顧客番号</span>
                                    </th>
                                    <th>
                                        <span>氏名</span>
                                    </th>
                                    <th>
                                        <span>氏名(カナ)</span>
                                    </th>
                                    <th>
                                        <span>氏名(ローマ字)</span>
                                    </th>
                                    <th className="txtalc">
                                        <span>性別</span>
                                    </th>
                                    <th className="txtalc">
                                        <span>年齢</span>
                                    </th>
                                    <th className="txtalc">
                                        <span>年齢区分</span>
                                    </th>
                                    <th>
                                        <span>旅券番号</span>
                                    </th>
                                    <th>
                                        <span>旅券期限</span>
                                    </th>
                                    <th>
                                        <span>電話番号</span>
                                    </th>
                                </tr>
                            </thead>
                        )}
                        <tbody>
                            {isLoading && (
                                <tr>
                                    <td
                                        colSpan={
                                            customerType ===
                                            customerKbns.business
                                                ? 9
                                                : 11
                                        }
                                    >
                                        <ReactLoading
                                            type={"bubbles"}
                                            color={"#dddddd"}
                                        />
                                    </td>
                                </tr>
                            )}
                            {!rows.length && !isLoading && !isLoaded && (
                                <tr>
                                    <td
                                        colSpan={
                                            customerType ===
                                            customerKbns.business
                                                ? 9
                                                : 11
                                        }
                                    >
                                        顧客を選択してください
                                    </td>
                                </tr>
                            )}
                            {!rows.length && isLoaded && (
                                <tr>
                                    <td
                                        colSpan={
                                            customerType ===
                                            customerKbns.business
                                                ? 9
                                                : 11
                                        }
                                    >
                                        検索結果がありません
                                    </td>
                                </tr>
                            )}
                            {isLoaded &&
                                customerType === customerKbns.business &&
                                rows.map((row, index) => (
                                    <React.Fragment key={index}>
                                        <tr>
                                            <td className="txtalc checkBox">
                                                <input
                                                    type="radio"
                                                    id={`daihyou${index}`}
                                                    name="applicant_user_number"
                                                    value={
                                                        row?.user_number ?? ""
                                                    }
                                                    onChange={handleChange}
                                                    checked={
                                                        userNumber ==
                                                        row?.user_number
                                                    }
                                                />
                                                <label
                                                    htmlFor={`daihyou${index}`}
                                                >
                                                    &nbsp;
                                                </label>
                                            </td>
                                            <td>
                                                {row?.business_user
                                                    ?.user_number ?? "-"}
                                            </td>
                                            <td
                                                className={classNames({
                                                    txcGray:
                                                        row.business_user
                                                            .is_deleted
                                                })}
                                            >
                                                {row?.business_user?.name ??
                                                    "-"}
                                            </td>
                                            <td
                                                className={classNames({
                                                    txcGray: row.is_deleted
                                                })}
                                            >
                                                {row?.name ?? "-"}
                                            </td>
                                            <td>
                                                {row?.department_name ?? "-"}
                                            </td>
                                            <td>
                                                {row?.business_user
                                                    ?.prefecture_name ?? "-"}
                                            </td>
                                            <td>{row?.email ?? "-"}</td>
                                            <td>{row?.tel ?? "-"}</td>
                                            <td>
                                                {row?.business_user?.kbn?.val ??
                                                    "-"}
                                            </td>
                                        </tr>
                                    </React.Fragment>
                                ))}
                            {!isLoading &&
                                customerType === customerKbns.person &&
                                rows.map((row, index) => (
                                    <React.Fragment key={index}>
                                        <tr>
                                            <td className="txtalc checkBox">
                                                <input
                                                    type="radio"
                                                    id={`daihyou${index}`}
                                                    name="applicant_user_number"
                                                    value={
                                                        row?.user_number ?? ""
                                                    }
                                                    onChange={handleChange}
                                                    checked={
                                                        userNumber ==
                                                        row?.user_number
                                                    }
                                                />
                                                <label
                                                    htmlFor={`daihyou${index}`}
                                                >
                                                    &nbsp;
                                                </label>
                                            </td>
                                            <td>{row.user_number ?? "-"}</td>
                                            <td
                                                className={classNames({
                                                    txcGray: row.is_deleted
                                                })}
                                            >
                                                {row.userable?.name ?? "-"}
                                            </td>
                                            <td>
                                                {row.userable?.name_kana ?? "-"}
                                            </td>
                                            <td>
                                                {row.userable?.name_roman ??
                                                    "-"}
                                            </td>
                                            <td className="txtalc">
                                                {row.userable?.sex_label ?? "-"}
                                            </td>
                                            <td className="txtalc">
                                                {row?.userable?.age_calc
                                                    ? row.userable.age_calc
                                                    : "-"}
                                            </td>
                                            <td className="txtalc">
                                                {row.userable?.user_ext
                                                    ?.age_kbn_label ?? "-"}
                                            </td>
                                            <td>
                                                {row.userable
                                                    ?.passport_number ?? "-"}
                                            </td>
                                            <td>
                                                {row.userable
                                                    ?.passport_expiration_date ??
                                                    "-"}
                                            </td>
                                            <td>
                                                {row.userable?.mobile_phone ??
                                                    "-"}
                                            </td>
                                        </tr>
                                    </React.Fragment>
                                ))}
                        </tbody>
                    </table>
                </div>
            </div>
            {/** ユーザー追加モーダル */}
            <AddUserModal
                input={input}
                handleChange={handleModalInputChange}
                handleAddressSearchResult={handleAddressSearchResult}
                countries={countries}
                sexes={sexes}
                ageKbns={ageKbns}
                birthdayYears={birthdayYears}
                birthdayMonths={birthdayMonths}
                birthdayDays={birthdayDays}
                prefectures={prefectures}
                isEditing={isEditing}
                handleSubmit={handleSubmit}
            />
        </>
    );
};

export default CustomerSelect;
