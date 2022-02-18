import React from "react";

export const ConstContext = React.createContext();

// 配下のコンポーネントに良く使う定数を渡すコンポーネント
const ConstApp = props => {
    return (
        <ConstContext.Provider value={{ ...props.jsVars }}>
            {props.children}
        </ConstContext.Provider>
    );
};

export default ConstApp;
