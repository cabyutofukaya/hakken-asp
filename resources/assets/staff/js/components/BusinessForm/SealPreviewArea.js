import React from "react";

/**
 * 検印欄表示エリア
 *
 * @param {*} sealWording 枠下文言
 * @returns
 */
const SealPreviewArea = ({ sealNumber, sealItems, sealWording }) => {
    return (
        <>
            <ul>
                {/** sealNumber - 0 は「文字列を数字に変換」する処理*/}
                {[...Array(sealNumber - 0).keys()].map((n, i) => (
                    <li key={i}>
                        <span>{sealItems?.[i] ?? `　`}</span>
                        {/**文字がないと枠線が表示されないので全角スペースを表示*/}
                    </li>
                ))}
            </ul>
            <p>{sealWording}</p>
        </>
    );
};

export default SealPreviewArea;
