import React from "react";

const MAX_TAG_NUM = 20; // 際限なく登録できても困るので、一応登録可能な上限を20個にしておく

const TagArea = ({
    name,
    tags,
    tag,
    handleChange,
    handleAdd,
    handleDeleteClick
}) => {
    return (
        <>
            <li className="wd100">
                <span className="inputLabel">タグ付け</span>
                <ul className="profTag">
                    {tags.map((tag, index) => (
                        <li key={index}>
                            {tag}
                            <span
                                className="material-icons js-modal-open"
                                data-target="mdDeleteTag"
                                onClick={e => handleDeleteClick(tag)}
                            >
                                cancel
                            </span>
                            {/**post送信用データ */}
                            <input
                                type="hidden"
                                name={`${name}[tag][]`}
                                value={tag ?? ""}
                            />
                        </li>
                    ))}
                </ul>

                <div className="buttonSet mt20">
                    <input
                        type="text"
                        className="wd30"
                        placeholder="例）温泉"
                        value={tag ?? ""}
                        onChange={handleChange}
                        maxLength={18}
                    />
                    <button
                        className="blueBtn wd10"
                        onClick={handleAdd}
                        disabled={tags.length >= MAX_TAG_NUM}
                    >
                        追加
                    </button>
                </div>
            </li>
        </>
    );
};

export default TagArea;
