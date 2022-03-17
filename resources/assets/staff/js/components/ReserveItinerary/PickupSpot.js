import React, { useState, useContext } from "react";
import { ConstContext } from "../ConstApp";
import { useMountedRef } from "../../../../hooks/useMountedRef";
import ReactLoading from "react-loading";

/**
 * 画像選択＆アップロードエリア
 *
 * @param {int} no 写真番号。現状は0のみ
 * @returns
 */
const PickupSpot = ({
    index,
    date,
    no,
    input,
    inputName,
    thumbSBaseUrl,
    handleUploadPhoto,
    handleClearPhoto,
    handleChangePhoto,
    handleChangePhotoExplanation
}) => {
    const { agencyAccount } = useContext(ConstContext);

    const mounted = useMountedRef(); // マウント・アンマウント制御

    const [isUploading, setIsUploading] = useState(false); // 画像アップロード中か否か

    // 画像選択
    const handleImageChange = async ev => {
        if (!mounted.current) return;

        if (ev.target.files && ev.target.files[0]) {
            const file = ev.target.files[0];
            const name = file.name; // ファイル名
            const type = file.type; // MIMEタイプ
            const size = file.size; // ファイル容量（byte）
            const limit = 10485760; // 10MB
            // MIMEタイプの判定
            if (["image/jpeg", "image/png", "image/gif"].indexOf(type) === -1) {
                alert("画像はjpg/png/gif形式のファイルを選択してください");
                handleChangePhoto(
                    {
                        target: {
                            name: "image",
                            value: null
                        }
                    },
                    date,
                    index,
                    no
                );
                return;
            }
            // サイズの判定
            if (limit < size) {
                alert("10MBを超える画像はアップロードできません。");
                handleChangePhoto(
                    {
                        target: {
                            name: "image",
                            value: null
                        }
                    },
                    date,
                    index,
                    no
                );
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
                        handleUploadPhoto(e, date, index, no, response.data);
                        handleChangePhoto(
                            {
                                target: {
                                    name: "image",
                                    value: e.target.result // サムネイル画像セット
                                }
                            },
                            date,
                            index,
                            no
                        );
                    }
                };
                reader.readAsDataURL(file);
            }
        }
    };

    // 選択画像削除
    const handleDelete = e => {
        handleChangePhoto(
            {
                target: {
                    name: "image",
                    value: null
                }
            },
            date,
            index,
            no
        );
        handleClearPhoto(e, date, index, no);
    };

    return (
        <li>
            <ul className="pickupSpot">
                <li>
                    <span className="inputLabel">写真</span>
                    <input
                        type="file"
                        id={`${date}_${index}_photos_${no}`}
                        accept="image/*"
                        onChange={handleImageChange}
                        disabled={isUploading}
                    />
                    {/* <input
                        type="hidden"
                        name={`${inputName}[photos][${no}][id]`}
                        value={input?.photos?.[no]?.id ?? ""}
                    /> */}
                    {/** 保存済み画像パス */}
                    {/* <input
                        type="hidden"
                        name={`${inputName}[photos][${no}][file_name]`}
                        value={input?.photos?.[no]?.file_name ?? ""}
                    /> */}
                    {/** アップロード画像パス */}
                    {/* <input
                        type="hidden"
                        name={`${inputName}[photos][${no}][upload_file_name]`}
                        value={input?.photos?.[no]?.upload_file_name ?? ""}
                    />
                    <input
                        type="hidden"
                        name={`${inputName}[photos][${no}][file_size]`}
                        value={input?.photos?.[no]?.file_size ?? ""}
                    />
                    <input
                        type="hidden"
                        name={`${inputName}[photos][${no}][original_file_name]`}
                        value={input?.photos?.[no]?.original_file_name ?? ""}
                    />
                    <input
                        type="hidden"
                        name={`${inputName}[photos][${no}][mime_type]`}
                        value={input?.photos?.[no]?.mime_type ?? ""}
                    /> */}
                    <label htmlFor={`${date}_${index}_photos_${no}`}>
                        <span className="material-icons">add_a_photo</span>
                    </label>
                    {input?.photos?.[no]?.file_name && (
                        <div>
                            <span
                                className="material-icons"
                                onClick={handleDelete}
                            >
                                cancel
                            </span>
                            <img
                                src={`${thumbSBaseUrl}${input.photos[no].file_name}`}
                                alt=""
                            />
                        </div>
                    )}
                    {/** アップロード中 */}
                    {isUploading && (
                        <ReactLoading
                            type={"bubbles"}
                            color={"#dddddd"}
                            width={45}
                            height={45}
                        />
                    )}
                    {/** アップロード済み画像有り */}
                    {!isUploading && input?.photos?.[no]?.image && (
                        <div>
                            <span
                                className="material-icons"
                                onClick={handleDelete}
                            >
                                cancel
                            </span>
                            <img src={input.photos[no].image} alt="" />
                        </div>
                    )}
                    {/** 選択画像あり */}
                </li>
                <li>
                    <span className="inputLabel">写真の説明</span>
                    <input
                        type="text"
                        value={input?.photos?.[no]?.description ?? ""}
                        name={`${inputName}[photos][${no}][description]`}
                        onChange={e =>
                            handleChangePhotoExplanation(
                                {
                                    target: {
                                        name: "description",
                                        value: e.target.value
                                    }
                                },
                                date,
                                index,
                                no
                            )
                        }
                    />
                </li>
            </ul>
        </li>
    );
};

export default PickupSpot;
