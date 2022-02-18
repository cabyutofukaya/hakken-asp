import React from "react";

export const ReserveItineraryConstContext = React.createContext();

// 配下の旅行日程関連コンポーネントに良く使う定数を渡すコンポーネント
const ReserveItineraryConstApp = props => {
    return (
        <ReserveItineraryConstContext.Provider value={{ ...props.vars }}>
            {props.children}
        </ReserveItineraryConstContext.Provider>
    );
};

export default ReserveItineraryConstApp;
