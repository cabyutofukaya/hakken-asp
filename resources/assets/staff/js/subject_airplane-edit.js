import React from "react";
import { render } from "react-dom";
import PriceArea from "./components/Subject/PriceArea";
import CityArea from "./components/Subject/CityArea";
import ConstApp from "./components/ConstApp";

// 航空券科目料金エリア
const AirplanePriceArea = document.getElementById("airplanePriceArea");
if (AirplanePriceArea) {
    const agencyAccount = AirplanePriceArea.getAttribute("agencyAccount");
    const defaultZeiKbn = AirplanePriceArea.getAttribute("defaultZeiKbn");
    const zeiKbns = AirplanePriceArea.getAttribute("zeiKbns");
    const parsedZeiKbns = zeiKbns && JSON.parse(zeiKbns);
    const defaultValue = AirplanePriceArea.getAttribute("defaultValue");
    const parsedDefaultValue = defaultValue && JSON.parse(defaultValue);

    render(
        <PriceArea
            agencyAccount={agencyAccount}
            defaultZeiKbn={defaultZeiKbn}
            zeiKbns={parsedZeiKbns}
            defaultValue={parsedDefaultValue}
        />,
        document.getElementById("airplanePriceArea")
    );
}

// 出発地・目的地(航空券科目)
const PlaceArea = document.getElementById("placeArea");
if (PlaceArea) {
    const jsVars = PlaceArea.getAttribute("jsVars");
    const parsedJsVars = jsVars && JSON.parse(jsVars);
    const defaultValue = PlaceArea.getAttribute("defaultValue");
    const parsedDefaultValue = defaultValue && JSON.parse(defaultValue);

    render(
        <ConstApp jsVars={parsedJsVars}>
            <CityArea defaultValue={parsedDefaultValue} />
        </ConstApp>,
        document.getElementById("placeArea")
    );
}
