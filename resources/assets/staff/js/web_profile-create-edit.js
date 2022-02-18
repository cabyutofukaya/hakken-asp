import React, { useState, useContext } from "react";
import { ConstContext } from "./components/ConstApp";
import ConstApp from "./components/ConstApp";
import { useMountedRef } from "../../hooks/useMountedRef";
import { render } from "react-dom";
import SmallDangerModal from "./components/SmallDangerModal";
import TagArea from "./components/TagArea";
import PhotoArea from "./components/PhotoArea";

const IMAGE_MAX_SIZE = 2097152; // 画像アップロードサイズ(2MB)

/**
 * 写真編集エリア
 *
 * @param {*} param0
 * @returns
 */
const InputArea = ({ consts, defaultValue, formSelects }) => {
    const { agencyAccount } = useContext(ConstContext);

    const mounted = useMountedRef(); // マウント・アンマウント制御

    const [input, setInput] = useState(defaultValue);

    /**
     * 写真関連処理
     */
    const [photo1, setPhoto1] = useState(null);
    const [photo2, setPhoto2] = useState(null);

    const [isPhoto1Uploading, setIsPhoto1Uploading] = useState(false);
    const [isPhoto2Uploading, setIsPhoto2Uploading] = useState(false);

    // アップロードファイル名
    const [uploadPhoto1, setUploadPhoto1] = useState("");
    const [uploadPhoto2, setUploadPhoto2] = useState("");

    // 保存済みファイル名
    const [savedPhoto1, setSavedPhoto1] = useState(
        defaultValue.web_profile_profile_photo ?? ""
    ); // プロフィール画像
    const [savedPhoto2, setSavedPhoto2] = useState(
        defaultValue.web_profile_cover_photo ?? ""
    ); // カバー画像

    // 画像アップロード処理
    const handleChangeImage = async (
        ev,
        setImage,
        setUploadFile,
        isUploading,
        setIsUploading,
        handleDelete
    ) => {
        ev.persist(); // これを書かないと後述のvalueの値がクリアできない

        if (isUploading) {
            return;
        }
        if (!mounted.current) return;

        if (ev.target.files && ev.target.files[0]) {
            const file = ev.target.files[0];
            const name = file.name; // ファイル名
            const type = file.type; // MIMEタイプ
            const size = file.size; // ファイル容量（byte）
            const limit = IMAGE_MAX_SIZE; // 2MB
            // MIMEタイプの判定
            if (["image/jpeg", "image/png", "image/gif"].indexOf(type) === -1) {
                alert("画像はjpg/png/gif形式のファイルを選択してください");
                setImage(null);
                return;
            }
            // サイズの判定
            if (limit < size) {
                alert("2MBを超える画像はアップロードできません。");
                setImage(null);
                return;
            }

            setIsUploading(true); // アップロードフラグOn

            // ファイル情報初期化
            handleDelete();

            const data = new FormData();
            data.append("file", file, name);
            const response = await axios
                .post(`/api/${agencyAccount}/upload/image/temp`, data, {
                    headers: { "content-type": "multipart/form-data" }
                })
                .finally(() => {
                    if (mounted.current) {
                        setIsUploading(false); // アップロードフラグOff
                    }
                });

            if (response?.data?.file_name) {
                // アップロードパスが帰ってきたらサムネイルをセット
                const reader = new FileReader();
                reader.onload = e => {
                    if (mounted.current) {
                        setImage(e.target.result); // サムネイル画像セット
                        setUploadFile(response.data);
                    }
                    ev.target.value = "";
                };
                reader.readAsDataURL(file);
            } else {
                ev.target.value = "";
            }
        }
    };

    /**
     * タグ関連処理
     */
    const [tags, setTags] = useState(defaultValue.web_profile_tags.tag ?? []); // タグ一覧
    const [tag, setTag] = useState(""); // タグ入力
    const [deleteTag, setDeleteTag] = useState(""); // 削除タグ
    const handleTagChange = e => {
        setTag(e.target.value.trim());
    };
    // タグ追加処理
    const handleTagAdd = e => {
        e.preventDefault();
        if (tag.length === 0) {
            return;
        }
        if (!tags.find(s => s == tag)) {
            // 重複している場合は追加しない
            setTags([...tags, tag]);
        }
        setTag("");
    };
    // 削除Tagクリック
    const handleTagDeleteClick = tag => {
        setDeleteTag(tag);
    };
    // タグ削除処理
    const handleTagDelete = e => {
        e.preventDefault();
        setTags(tags.filter(tag => tag != deleteTag));
        setDeleteTag(""); // 一応、削除タグクリア
        $(".js-modal-close").trigger("click"); // モーダルclose
    };

    // 入力制御
    const handleChange = e => {
        setInput({ ...input, [e.target.name]: e.target.value });
    };

    // checkbox制御
    const handleChangeCheckbox = e => {
        const name = e.target.name.replace(/\[|\]/g, ""); // 配列の括弧を削除してname部分を取得
        const val = e.target.value;
        let tmp = [];
        if (
            _.findIndex(input[name], function(v) {
                return v == val;
            }) !== -1
        ) {
            tmp = input[name].filter(v => v != val);
        } else {
            tmp = _.uniq([...input[name], val]); // 重複削除
            tmp.sort(); //一応ソート
        }
        setInput({ ...input, [name]: tmp });
    };

    // HAKKEN機能の有効・無効
    const handleWebValidChange = e => {
        const staff = {
            web_valid: e.target.value
        };
        setInput({ ...input, staff });
    };
    return (
        <>
            <ul className="sideList">
                <li>
                    <span className="inputLabel">
                        アカウントのHAKKEN WEBの有効化
                    </span>
                    <ul className="slideRadio">
                        <li>
                            <input
                                type="radio"
                                id="webValid1"
                                name="staff[web_valid]"
                                value="1"
                                checked={input.staff.web_valid == 1}
                                onChange={handleWebValidChange}
                            />
                            <label htmlFor="webValid1">有効</label>
                        </li>
                        <li>
                            <input
                                type="radio"
                                id="webValid2"
                                name="staff[web_valid]"
                                value="0"
                                checked={input.staff.web_valid == 0}
                                onChange={handleWebValidChange}
                            />
                            <label htmlFor="webValid2">無効</label>
                        </li>
                    </ul>
                </li>
            </ul>

            <div
                style={{
                    display: input.staff.web_valid == 1 ? "block" : "none",
                    paddingTop: "30px"
                }}
            >
                <ul className="profilePhoto">
                    <li className="profilePh">
                        <span className="inputLabel">プロフィール写真</span>
                        <PhotoArea
                            id="photo1"
                            image={photo1}
                            setImage={setPhoto1}
                            savedImage={savedPhoto1}
                            setSavedImage={setSavedPhoto1}
                            uploadImage={uploadPhoto1}
                            setUploadImage={setUploadPhoto1}
                            isUploading={isPhoto1Uploading}
                            setIsUploading={setIsPhoto1Uploading}
                            handleChange={handleChangeImage}
                            savedField={"web_profile_profile_photo"}
                            uploadField={"upload_web_profile_profile_photo"}
                            thumbSBaseUrl={consts.thumbSBaseUrl}
                            stack={true}
                        />
                        <p>
                            プロフィール画面に表示されます。
                            <br />
                            ・500px x 500px以上
                            <br />
                            ・2MB以下
                            <br />
                            ・円内が表示領域です。
                        </p>
                    </li>
                    <li>
                        <span className="inputLabel">カバー写真</span>
                        <PhotoArea
                            id="photo2"
                            image={photo2}
                            setImage={setPhoto2}
                            savedImage={savedPhoto2}
                            setSavedImage={setSavedPhoto2}
                            uploadImage={uploadPhoto2}
                            setUploadImage={setUploadPhoto2}
                            isUploading={isPhoto2Uploading}
                            setIsUploading={setIsPhoto2Uploading}
                            handleChange={handleChangeImage}
                            savedField={"web_profile_cover_photo"}
                            uploadField={"upload_web_profile_cover_photo"}
                            thumbSBaseUrl={consts.thumbSBaseUrl}
                            stack={false}
                        />
                        <p>
                            プロフィール画面の背景に表示されます。
                            <br />
                            ・1300px x 600px以上
                            <br />
                            ・2MB以下
                        </p>
                    </li>
                    <li className="phSample">
                        <div>
                            <p>プロフィール写真</p>
                        </div>
                        <p>カバー写真</p>
                    </li>
                </ul>
                <ul className="sideList half">
                    <li>
                        <span className="inputLabel">社名</span>
                        <input
                            type="text"
                            placeholder="例）株式会社キャブステーション"
                            value={input.agency.company_name ?? ""}
                            disabled
                        />
                    </li>
                    <li>
                        <span className="inputLabel">
                            <font color="#333333">役職・名称</font>
                        </span>
                        <input
                            type="text"
                            name="post"
                            placeholder="例）ツアーコンサルタント"
                            value={input.post ?? ""}
                            onChange={handleChange}
                        />
                    </li>
                </ul>
                <ul className="sideList">
                    <li className="wd40">
                        <span className="inputLabel">氏名</span>
                        <input
                            type="text"
                            name="name"
                            placeholder="例）山田 太郎"
                            value={input.name ?? ""}
                            onChange={handleChange}
                        />
                    </li>
                    <li className="wd40">
                        <span className="inputLabel">氏名(カナ)</span>
                        <input
                            type="text"
                            name="name_kana"
                            placeholder="例）ヤマダ タロウ"
                            value={input.name_kana ?? ""}
                            onChange={handleChange}
                        />
                    </li>
                    <li className="wd40 mr00">
                        <span className="inputLabel">氏名(ローマ字)</span>
                        <input
                            type="text"
                            name="name_roman"
                            placeholder="例）YAMADA TAROU"
                            value={input.name_roman ?? ""}
                            onChange={handleChange}
                        />
                    </li>
                </ul>
                <ul className="sideList">
                    <li className="wd20">
                        <span className="inputLabel">性別</span>
                        <ul className="baseRadio sideList half mt10">
                            <li>
                                <input
                                    type="radio"
                                    id="sex1"
                                    name="sex"
                                    value={formSelects.sexes.sex_male}
                                    checked={
                                        input.sex === formSelects.sexes.sex_male
                                    }
                                    onChange={handleChange}
                                />
                                <label htmlFor="sex1">男性</label>
                            </li>
                            <li>
                                <input
                                    type="radio"
                                    id="sex2"
                                    name="sex"
                                    value={formSelects.sexes.sex_female}
                                    checked={
                                        input.sex ===
                                        formSelects.sexes.sex_female
                                    }
                                    onChange={handleChange}
                                />
                                <label htmlFor="sex2">女性</label>
                            </li>
                        </ul>
                    </li>
                    <li className="wd60">
                        <span className="inputLabel">生年月日</span>
                        <div className="selectSet wd100">
                            <div className="selectBox wd40 mr10">
                                <select
                                    name="birthday_y"
                                    value={input.birthday_y ?? ""}
                                    onChange={handleChange}
                                >
                                    {Object.keys(formSelects.birthdayYears)
                                        .sort((a, b) => a - b)
                                        .map(v => (
                                            <option key={v} value={v}>
                                                {formSelects.birthdayYears[v]}
                                            </option>
                                        ))}
                                </select>
                            </div>
                            <div className="selectBox wd30 mr10">
                                <select
                                    name="birthday_m"
                                    value={input.birthday_m ?? ""}
                                    onChange={handleChange}
                                >
                                    {Object.keys(formSelects.birthdayMonths)
                                        .sort((a, b) => a - b)
                                        .map(v => (
                                            <option key={v} value={v}>
                                                {formSelects.birthdayMonths[v]}
                                            </option>
                                        ))}
                                </select>
                            </div>
                            <div className="selectBox wd30">
                                <select
                                    name="birthday_d"
                                    value={input.birthday_d ?? ""}
                                    onChange={handleChange}
                                >
                                    {Object.keys(formSelects.birthdayDays)
                                        .sort((a, b) => a - b)
                                        .map(v => (
                                            <option key={v} value={v}>
                                                {formSelects.birthdayDays[v]}
                                            </option>
                                        ))}
                                </select>
                            </div>
                        </div>
                    </li>
                </ul>
                <hr className="sepBorder" />
                <ul className="sideList half">
                    <li>
                        <span className="inputLabel">メールアドレス</span>
                        <input
                            type="email"
                            name="email"
                            placeholder="例）info@hakken.jp"
                            value={input.email ?? ""}
                            onChange={handleChange}
                        />
                    </li>
                    <li>
                        <span className="inputLabel">電話番号</span>
                        <input
                            type="text"
                            name="tel"
                            placeholder="例）0000000000"
                            value={input.tel ?? ""}
                            onChange={handleChange}
                        />
                    </li>
                </ul>
                <hr className="sepBorder" />

                <ul className="baseList">
                    <li>
                        <span className="inputLabel">自己紹介欄</span>
                        <textarea
                            name="introduction"
                            rows="5"
                            placeholder="趣味や興味のある事、得意なコースプランニング等、自己紹介を記入してください"
                            value={input.introduction ?? ""}
                            onChange={handleChange}
                        ></textarea>
                    </li>

                    <li className="areaSelect">
                        <span className="inputLabel">提案可能エリア</span>
                        <dl>
                            {formSelects?.masterDirections &&
                                Object.keys(formSelects.masterDirections).map(
                                    i => {
                                        return (
                                            <React.Fragment key={i}>
                                                <dt>
                                                    {
                                                        formSelects
                                                            .masterDirections[i]
                                                            ?.name
                                                    }
                                                </dt>
                                                <dd>
                                                    <ul className="sideList checkBox">
                                                        {formSelects
                                                            .masterDirections[i]
                                                            ?.areas &&
                                                            Object.keys(
                                                                formSelects
                                                                    .masterDirections[
                                                                    i
                                                                ].areas
                                                            ).map(j => (
                                                                <li
                                                                    key={`${i}_${j}`}
                                                                >
                                                                    <input
                                                                        type="checkbox"
                                                                        name="business_area[]"
                                                                        id={`businessarea_${i}_${j}`}
                                                                        value={`${formSelects.masterDirections[i].areas[j]?.code}`}
                                                                        onChange={
                                                                            handleChangeCheckbox
                                                                        }
                                                                        checked={
                                                                            _.findIndex(
                                                                                input?.business_area,
                                                                                function(
                                                                                    val
                                                                                ) {
                                                                                    return (
                                                                                        val ==
                                                                                        `${formSelects.masterDirections[i].areas[j]?.code}`
                                                                                    );
                                                                                }
                                                                            ) !==
                                                                            -1
                                                                        }
                                                                    />
                                                                    <label
                                                                        htmlFor={`businessarea_${i}_${j}`}
                                                                    >
                                                                        {
                                                                            formSelects
                                                                                .masterDirections[
                                                                                i
                                                                            ]
                                                                                .areas[
                                                                                j
                                                                            ]
                                                                                ?.name
                                                                        }
                                                                    </label>
                                                                </li>
                                                            ))}
                                                    </ul>
                                                </dd>
                                            </React.Fragment>
                                        );
                                    }
                                )}
                        </dl>
                    </li>
                    <li className="purpose">
                        <span className="inputLabel">得意な旅行分野</span>
                        <ul className="checkBox sideList">
                            {formSelects?.purposes &&
                                Object.keys(formSelects.purposes).map(id => (
                                    <li key={id}>
                                        <input
                                            type="checkbox"
                                            name="purpose[]"
                                            value={id}
                                            id={`purpose_${id}`}
                                            onChange={handleChangeCheckbox}
                                            checked={
                                                _.findIndex(
                                                    input?.purpose,
                                                    function(val) {
                                                        return val == id;
                                                    }
                                                ) !== -1
                                            }
                                        />
                                        <label htmlFor={`purpose_${id}`}>
                                            {formSelects.purposes[id]}
                                        </label>
                                    </li>
                                ))}
                        </ul>
                    </li>
                    <li className="purpose">
                        <span className="inputLabel">得意な旅行内容</span>
                        <ul className="checkBox sideList">
                            {formSelects?.interests &&
                                Object.keys(formSelects.interests).map(id => (
                                    <li key={id}>
                                        <input
                                            type="checkbox"
                                            name="interest[]"
                                            value={id}
                                            id={`interest_${id}`}
                                            onChange={handleChangeCheckbox}
                                            checked={
                                                _.findIndex(
                                                    input?.interest,
                                                    function(val) {
                                                        return val == id;
                                                    }
                                                ) !== -1
                                            }
                                        />
                                        <label htmlFor={`interest_${id}`}>
                                            {formSelects.interests[id]}
                                        </label>
                                    </li>
                                ))}
                        </ul>
                    </li>
                    {/* <TagArea
                        name="web_profile_tags"
                        tags={tags}
                        tag={tag}
                        handleChange={handleTagChange}
                        handleAdd={handleTagAdd}
                        handleDeleteClick={handleTagDeleteClick}
                    /> */}
                </ul>
            </div>
            {/* <SmallDangerModal
                id="mdDeleteTag"
                title="このタグを削除しますか？"
                handleAction={handleTagDelete}
                isActioning={false}
            /> */}
        </>
    );
};

const Element = document.getElementById("inputArea");
if (Element) {
    const jsVars = Element.getAttribute("jsVars");
    const parsedJsVars = jsVars && JSON.parse(jsVars);
    const consts = Element.getAttribute("consts");
    const parsedConsts = consts && JSON.parse(consts);
    const defaultValue = Element.getAttribute("defaultValue");
    const parsedDefaultValue = defaultValue && JSON.parse(defaultValue);
    const formSelects = Element.getAttribute("formSelects");
    const parsedFormSelects = formSelects && JSON.parse(formSelects);

    render(
        <ConstApp jsVars={parsedJsVars}>
            <InputArea
                consts={parsedConsts}
                defaultValue={parsedDefaultValue}
                formSelects={parsedFormSelects}
            />
        </ConstApp>,
        document.getElementById("inputArea")
    );
}
