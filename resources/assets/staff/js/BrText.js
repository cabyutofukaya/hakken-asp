import React from "react";

// 改行コードをbrタグにして出力するコンポーネント
const BrText = ({ text }) => {
    const texts = text.split(/(\n)/).map((item, index) => {
        return (
            <React.Fragment key={index}>
                {item.match(/\n/) ? <br /> : item}
            </React.Fragment>
        );
    });
    return <span>{texts}</span>;
};

export default BrText;
