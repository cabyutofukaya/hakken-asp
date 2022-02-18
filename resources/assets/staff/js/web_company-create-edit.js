import React, { useState, useContext } from "react";
import { ConstContext } from "./components/ConstApp";
import ConstApp from "./components/ConstApp";
import { useMountedRef } from "../../hooks/useMountedRef";
import { render } from "react-dom";
import ReactLoading from "react-loading";

const IMAGE_MAX_NUM = 3; // イメージ画像最大アップロード数
const IMAGE_MAX_SIZE = 2097152; // 画像アップロードサイズ(2MB)

// ロゴ画像選択
const handleChangeImage = async (
    ev,
    mounted,
    agencyAccount,
    setImage,
    setUploadFileName,
    resetLogoImage,
    setIsUploading
) => {
    if (!mounted.current) return;

    ev.persist(); // これを書かないと後述のvalueの値がクリアできない

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
        resetLogoImage();

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
                    setUploadFileName(response.data.file_name);
                }
                ev.target.value = "";
            };
            reader.readAsDataURL(file);
        } else {
            ev.target.value = "";
        }
    }
};

// イメージ画像選択
const handleMultipleChangeImage = async (
    ev,
    mounted,
    agencyAccount,
    images,
    setImages,
    setIsUploading
) => {
    if (!mounted.current) return;

    ev.persist(); // これを書かないと後述のvalueの値がクリアできない

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
                    if (images.length < IMAGE_MAX_NUM) {
                        // 枚数チェック
                        const result = _.cloneDeep(e.target.result);
                        const updata = {
                            upload_image: result, //ファイル
                            upload_file_name: response.data.file_name // ファイル名
                        };
                        images.push(updata);
                        setImages([...images]); // 画像情報をセット
                    }
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
 * 写真編集エリア
 *
 * @param {*} param0
 * @returns
 */
const InputArea = ({ consts, defaultValue }) => {
    const { agencyAccount } = useContext(ConstContext);

    const mounted = useMountedRef(); // マウント・アンマウント制御

    const [isLogoUploading, setIsLogoUploading] = useState(false); // ロゴ画像アップロード中か否か
    const [isImageUploading, setIsImageUploading] = useState(false); // イメージ画像アップロード中か否か

    /**
     * ロゴ画像
     */
    const [imageLogo, setImageLogo] = useState(null); // アップロードデータ
    const [uploadLogoFileName, setUploadLogoFileName] = useState(""); // アップロードファイル名
    const [logoFileName, setLogoFileName] = useState(
        defaultValue.logo_image ?? ""
    ); // 保存済画像ファイル

    /**
     * イメージ画像
     */
    const [images, setImages] = useState(defaultValue.images ?? []);

    // 入力制御(会社説明)
    const [input, setInput] = useState(
        _.omit(defaultValue, ["logo_image", "images"])
    ); // 画像カラムを除く

    const handleChange = e => {
        setInput({ ...input, [e.target.name]: e.target.value });
    };

    // ロゴ画像情報初期化
    const resetLogoImage = () => {
        setImageLogo(null);
        setUploadLogoFileName("");
        setLogoFileName("");
    };

    // ロゴ画像削除
    const handleLogoImageDelete = () => {
        resetLogoImage();
    };

    // イメージ画像削除
    const handleImageDelete = index => {
        setImages(images.filter((v, i) => i != index));
    };

    return (
        <>
            {/**画像がアップロードされていない場合に画像フィールドが確実に初期化されるように初期値をセット */}
            <input type="hidden" name="logo_image" value="" />
            <input type="hidden" name="images" value="" />

            <ul className="profilePhoto">
                <li>
                    <span className="inputLabel">会社ロゴ</span>
                    <input
                        type="file"
                        id="imageLogo"
                        accept="image/*"
                        onChange={e =>
                            handleChangeImage(
                                e,
                                mounted,
                                agencyAccount,
                                setImageLogo,
                                setUploadLogoFileName,
                                resetLogoImage,
                                setIsLogoUploading
                            )
                        }
                        disabled={isLogoUploading}
                    />
                    <label htmlFor="imageLogo">
                        <span className="material-icons">add_a_photo</span>
                    </label>
                    {/**画像をアップロードした場合 */}
                    {imageLogo && (
                        <>
                            <div>
                                <span
                                    className="material-icons"
                                    onClick={handleLogoImageDelete}
                                >
                                    cancel
                                </span>
                                <img src={imageLogo} alt="" />
                            </div>
                        </>
                    )}
                    {/**アップロード済画像がある場合 */}
                    {!imageLogo && logoFileName && (
                        <>
                            <div>
                                <span
                                    className="material-icons"
                                    onClick={handleLogoImageDelete}
                                >
                                    cancel
                                </span>
                                <img
                                    src={`${consts.thumbSBaseUrl}${logoFileName}`}
                                    alt=""
                                />
                            </div>
                        </>
                    )}
                    {isLogoUploading && (
                        <ReactLoading
                            type={"bubbles"}
                            color={"#dddddd"}
                            width={45}
                            height={45}
                        />
                    )}
                    <p>
                        会社概要に表示されます。
                        <br />
                        ・500px x 500px以上
                        <br />
                        ・2MB以下
                    </p>
                    <input
                        type="hidden"
                        name="logo_image"
                        value={logoFileName}
                    />
                    <input
                        type="hidden"
                        name="upload_logo_image"
                        value={uploadLogoFileName}
                    />
                </li>
                <li>
                    <span className="inputLabel">イメージ写真(3枚まで)</span>
                    <input
                        type="file"
                        id="images"
                        accept="image/*"
                        onChange={e =>
                            handleMultipleChangeImage(
                                e,
                                mounted,
                                agencyAccount,
                                images,
                                setImages,
                                setIsImageUploading
                            )
                        }
                        disabled={isImageUploading}
                    />
                    {images.length < IMAGE_MAX_NUM && (
                        <label htmlFor="images">
                            <span className="material-icons">add_a_photo</span>
                        </label>
                    )}
                    {Array.from(
                        Array(IMAGE_MAX_NUM),
                        (v, k) =>
                            images?.[k] && (
                                <div key={k}>
                                    <span
                                        className="material-icons"
                                        onClick={e => handleImageDelete(k)}
                                    >
                                        cancel
                                    </span>
                                    <img
                                        src={
                                            images[k]?.upload_image
                                                ? images[k]["upload_image"]
                                                : `${consts.thumbSBaseUrl}${images[k]}`
                                        }
                                        alt=""
                                    />
                                    {/**アップロードファイルがある場合はアップロードファイル名をセット */}
                                    {images[k]?.upload_image && (
                                        <input
                                            type="hidden"
                                            name={`upload_images[${k}]`}
                                            value={
                                                images[k].upload_file_name ?? ""
                                            }
                                        />
                                    )}
                                    {!images[k]?.upload_image && (
                                        <input
                                            type="hidden"
                                            name={`images[${k}]`}
                                            value={images[k] ?? ""}
                                        />
                                    )}
                                </div>
                            )
                    )}
                    {isImageUploading && (
                        <ReactLoading
                            type={"bubbles"}
                            color={"#dddddd"}
                            width={45}
                            height={45}
                        />
                    )}
                    <p>
                        会社概要に表示されます。
                        <br />
                        ・950px x 720px以上
                        <br />
                        ・2MB以下
                    </p>
                </li>
                <li>
                    <span className="inputLabel">会社説明</span>
                    <textarea
                        placeholder="会社の紹介文を記入してください"
                        rows="5"
                        name="explanation"
                        value={input.explanation ?? ""}
                        onChange={handleChange}
                    ></textarea>
                </li>
            </ul>
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
    render(
        <ConstApp jsVars={parsedJsVars}>
            <InputArea
                consts={parsedConsts}
                defaultValue={parsedDefaultValue}
            />
        </ConstApp>,
        document.getElementById("inputArea")
    );
}
