import React from "react";

// 科目隠しフィールド
const SubjectHiddenRow = ({ item, index, inputName }) => {
    return (
        <>
            {/**仕入科目情報を隠しフィールドにセット（participants以外） */}
            {_.without(Object.keys(item), "participants").map((k, i) => (
                <input
                    key={`reserve_purchasing_subjects_${index}_${k}`}
                    type="hidden"
                    name={`${inputName}[reserve_purchasing_subjects][${index}][${k}]`}
                    value={item[k] ?? ""}
                />
            ))}
            {/**仕入科目情報を隠しフィールドにセット（participants） */}
            {item?.participants &&
                item.participants.map((row, i) =>
                    Object.keys(row).map(k => (
                        <input
                            key={`reserve_purchasing_subjects_${index}_participants_${i}_${k}`}
                            type="hidden"
                            name={`${inputName}[reserve_purchasing_subjects][${index}][participants][${i}][${k}]`}
                            value={row[k] ?? ""}
                        />
                    ))
                )}
        </>
    );
};

export default SubjectHiddenRow;
