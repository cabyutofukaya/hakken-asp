import React from "react";
import { render } from "react-dom";
import CustomItemList from "./components/CustomItemList";

/**
 * テキスト項目追加
 *
 * @param {*} param0
 * @returns
 */
const ListDiv = ({ defaultList, protectList }) => {
    return (
        <CustomItemList defaultList={defaultList} protectList={protectList} />
    );
};

const Element = document.getElementById("listDiv");
if (Element) {
    const defaultList = Element.getAttribute("defaultList");
    const parsedDefaultList = defaultList && JSON.parse(defaultList);
    const protectList = Element.getAttribute("protectList");
    const parsedProtectList = protectList && JSON.parse(protectList);

    render(
        <ListDiv
            defaultList={parsedDefaultList}
            protectList={parsedProtectList}
        />,
        document.getElementById("listDiv")
    );
}
