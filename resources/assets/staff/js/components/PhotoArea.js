import React from "react";
import ReactLoading from "react-loading";

/**
 *
 * @param {*} stack 画像を重ねて表示するか。プロフィールページのプロフィール画像に使用
 * @returns
 */
const PhotoArea = ({
    id,
    image,
    setImage,
    savedImage,
    setSavedImage,
    uploadImage,
    setUploadImage,
    isUploading,
    setIsUploading,
    handleChange,
    savedField,
    uploadField,
    thumbSBaseUrl,
    stack
}) => {
    const handleDelete = () => {
        setImage(null);
        setUploadImage("");
        setSavedImage("");
    };
    return (
        <>
            <input
                type="file"
                id={id}
                accept="image/*"
                disabled={isUploading}
                onChange={e =>
                    handleChange(
                        e,
                        setImage,
                        setUploadImage,
                        isUploading,
                        setIsUploading,
                        handleDelete
                    )
                }
            />
            <label htmlFor={id}>
                <span className="material-icons">add_a_photo</span>
            </label>
            {image && (
                <div>
                    <span className="material-icons" onClick={handleDelete}>
                        cancel
                    </span>
                    {/**プロフィール写真の場合は画像を2つ重ねる */}
                    {stack && (
                        <>
                            <img src={image} alt="" />
                            <img src={image} alt="" />
                        </>
                    )}
                    {!stack && <img src={image} alt="" />}
                </div>
            )}
            {/**アップロード済み画像がある場合 */}
            {!image && savedImage && (
                <div>
                    <span className="material-icons" onClick={handleDelete}>
                        cancel
                    </span>
                    {/**プロフィール写真の場合は画像を2つ重ねる */}
                    {stack && (
                        <>
                            <img
                                src={`${thumbSBaseUrl}${savedImage?.file_name}`}
                                alt=""
                            />
                            <img
                                src={`${thumbSBaseUrl}${savedImage?.file_name}`}
                                alt=""
                            />
                        </>
                    )}
                    {!stack && (
                        <img
                            src={`${thumbSBaseUrl}${savedImage?.file_name}`}
                            alt=""
                        />
                    )}
                </div>
            )}
            {isUploading && (
                <ReactLoading
                    type={"bubbles"}
                    color={"#dddddd"}
                    width={45}
                    height={45}
                />
            )}
            {/**保存済みファイル */}
            <input
                type="hidden"
                name={savedField}
                value={savedImage ? JSON.stringify(savedImage) : ""}
            />
            {/**アップロードファイル */}
            <input
                type="hidden"
                name={uploadField}
                value={uploadImage ? JSON.stringify(uploadImage) : ""}
            />
        </>
    );
};

export default PhotoArea;
