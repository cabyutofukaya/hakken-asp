import React, { useState, useContext } from "react";
import { ConstContext } from "./components/ConstApp";
import ConstApp from "./components/ConstApp";
import { useMountedRef } from "../../hooks/useMountedRef";
import { render } from "react-dom";
import TagArea from "./components/TagArea";
import AreaInput from "./components/AreaInput";
import PhotoArea from "./components/PhotoArea";
import OnlyNumberInput from "./components/OnlyNumberInput";
import SmallDangerModal from "./components/SmallDangerModal";

const IMAGE_MAX_SIZE = 2097152; // 画像アップロードサイズ(2MB)

/**
 * 登録エリア
 *
 * @param {*} param0
 * @returns
 */
const InputArea = ({ defaultValue, formSelects, consts }) => {
    const { agencyAccount } = useContext(ConstContext);

    const mounted = useMountedRef(); // マウント・アンマウント制御

    const [input, setInput] = useState(defaultValue);
    // 入力制御
    const handleChange = e => {
        setInput({ ...input, [e.target.name]: e.target.value });
    };

    /**
     * 写真関連処理
     */
    const [photo, setPhoto] = useState(null);

    const [isPhotoUploading, setIsPhotoUploading] = useState(false);

    // アップロードファイル名
    const [uploadPhoto, setUploadPhoto] = useState("");

    // 保存済みファイル名
    const [savedPhoto, setSavedPhoto] = useState(
        defaultValue.web_modelcourse_photo ?? ""
    ); // メイン画像

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
    const [tags, setTags] = useState(
        defaultValue.web_modelcourse_tags.tag ?? []
    ); // タグ一覧
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

    return (
        <>
            <div id="inputArea">
                <ul className="baseList">
                    <li className="wd70">
                        <span className="inputLabel">モデルコース名</span>
                        <input
                            type="text"
                            name="name"
                            value={input.name ?? ""}
                            onChange={handleChange}
                        />
                    </li>
                    <li>
                        <ul className="profilePhoto">
                            <li>
                                <span className="inputLabel">メイン写真</span>
                                <PhotoArea
                                    id="photo"
                                    image={photo}
                                    setImage={setPhoto}
                                    savedImage={savedPhoto}
                                    setSavedImage={setSavedPhoto}
                                    uploadImage={uploadPhoto}
                                    setUploadImage={setUploadPhoto}
                                    isUploading={isPhotoUploading}
                                    setIsUploading={setIsPhotoUploading}
                                    handleChange={handleChangeImage}
                                    savedField={"web_modelcourse_photo"}
                                    uploadField={"upload_web_modelcourse_photo"}
                                    thumbSBaseUrl={consts.thumbSBaseUrl}
                                    stack={false}
                                />
                                <p>
                                    モデルコースの一覧に表示されます。
                                    <br />
                                    ・横向き画像
                                    <br />
                                    ・760px x 570px以上
                                    <br />
                                    ・2MB以下
                                </p>
                            </li>
                        </ul>
                    </li>
                    <li className="wd100">
                        <span className="inputLabel">説明文</span>
                        <textarea
                            placeholder="モデルコースの説明を入力してください"
                            rows="5"
                            name="description"
                            value={input.description ?? ""}
                            onChange={handleChange}
                        ></textarea>
                    </li>
                </ul>
                <ul className="baseList">
                    <li className="wd30">
                        <span className="inputLabel">日数</span>
                        <div className="selectBox">
                            <select
                                name="stays"
                                value={input.stays ?? ""}
                                onChange={handleChange}
                            >
                                {Object.keys(formSelects.stays)
                                    .sort((a, b) => {
                                        return a - b;
                                    })
                                    .map((k, i) => (
                                        <option key={i} value={k}>
                                            {formSelects.stays[k]}
                                        </option>
                                    ))}
                            </select>
                        </div>
                    </li>
                </ul>
                {/* <ul className="sideList">
                    <li className="wd20">
                        <span className="inputLabel">大人</span>
                        <OnlyNumberInput
                            name="price_per_ad"
                            value={input.price_per_ad ?? ""}
                            handleChange={handleChange}
                            placeholder="1名あたりの金額"
                        />
                    </li>
                    <li className="wd20">
                        <span className="inputLabel">子供</span>
                        <OnlyNumberInput
                            name="price_per_ch"
                            value={input.price_per_ch ?? ""}
                            handleChange={handleChange}
                            placeholder="1名あたりの金額"
                        />
                    </li>
                    <li className="wd20">
                        <span className="inputLabel">幼児</span>
                        <OnlyNumberInput
                            name="price_per_inf"
                            value={input.price_per_inf ?? ""}
                            handleChange={handleChange}
                            placeholder="1名あたりの金額"
                        />
                    </li>
                </ul> */}
                <ul className="sideList half">
                    <li>
                        <span className="inputLabel">出発地</span>
                        <ul className="sideList">
                            <li className="wd40">
                                <AreaInput
                                    name="departure_id"
                                    defaultValue={input.departure ?? ""}
                                    defaultOptions={formSelects.defaultAreas}
                                    handleAreaChange={handleChange}
                                />
                            </li>
                            <li className="wd60">
                                <input
                                    type="text"
                                    placeholder="住所・名称"
                                    name="departure_place"
                                    value={input.departure_place ?? ""}
                                    onChange={handleChange}
                                />
                            </li>
                        </ul>
                    </li>
                    <li>
                        <span className="inputLabel">目的地</span>
                        <ul className="sideList">
                            <li className="wd40">
                                <AreaInput
                                    name="destination_id"
                                    defaultValue={input.destination ?? ""}
                                    defaultOptions={formSelects.defaultAreas}
                                    handleAreaChange={handleChange}
                                />
                            </li>
                            <li className="wd60">
                                <input
                                    type="text"
                                    placeholder="住所・名称"
                                    name="destination_place"
                                    value={input.destination_place ?? ""}
                                    onChange={handleChange}
                                />
                            </li>
                        </ul>
                    </li>
                    <TagArea
                        name="web_modelcourse_tags"
                        tags={tags}
                        tag={tag}
                        handleChange={handleTagChange}
                        handleAdd={handleTagAdd}
                        handleDeleteClick={handleTagDeleteClick}
                    />
                </ul>
            </div>
            <h2 className="subTit">
                <span className="material-icons"> playlist_add_check </span>
                モデルコース管理情報(カスタムフィールド)
            </h2>
            <div className="inputSubArea">
                <ul className="baseList">
                    <li>
                        <span className="inputLabel">作成者</span>
                        <div className="selectBox wd40">
                            <select
                                name="author_id"
                                value={input.author_id ?? ""}
                                onChange={handleChange}
                            >
                                {Object.keys(formSelects.staffs)
                                    .sort((a, b) => {
                                        return a - b;
                                    })
                                    .map((k, i) => (
                                        <option key={i} value={k}>
                                            {formSelects.staffs[k]}
                                        </option>
                                    ))}
                            </select>
                        </div>
                    </li>
                </ul>
            </div>
            <SmallDangerModal
                id="mdDeleteTag"
                title="このタグを削除しますか？"
                handleAction={handleTagDelete}
                isActioning={false}
            />
        </>
    );
};

const Element = document.getElementById("modelCourseInputArea");
if (Element) {
    const jsVars = Element.getAttribute("jsVars");
    const parsedJsVars = jsVars && JSON.parse(jsVars);
    const defaultValue = Element.getAttribute("defaultValue");
    const parsedDefaultValue = defaultValue && JSON.parse(defaultValue);
    const formSelects = Element.getAttribute("formSelects");
    const parsedFormSelects = formSelects && JSON.parse(formSelects);
    const consts = Element.getAttribute("consts");
    const parsedConsts = consts && JSON.parse(consts);

    render(
        <ConstApp jsVars={parsedJsVars}>
            <InputArea
                defaultValue={parsedDefaultValue}
                formSelects={parsedFormSelects}
                consts={parsedConsts}
            />
        </ConstApp>,
        document.getElementById("modelCourseInputArea")
    );
}
