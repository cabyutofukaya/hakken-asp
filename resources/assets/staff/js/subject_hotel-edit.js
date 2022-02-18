import React from "react";
import { render } from "react-dom";
import PriceArea from "./components/Subject/PriceArea";

// ホテル科目料金エリア
const HotelPriceArea = document.getElementById("hotelPriceArea");
if (HotelPriceArea) {
    const agencyAccount = HotelPriceArea.getAttribute("agencyAccount");
    const defaultZeiKbn = HotelPriceArea.getAttribute("defaultZeiKbn");
    const zeiKbns = HotelPriceArea.getAttribute("zeiKbns");
    const parsedZeiKbns = zeiKbns && JSON.parse(zeiKbns);
    const defaultValue = HotelPriceArea.getAttribute("defaultValue");
    const parsedDefaultValue = defaultValue && JSON.parse(defaultValue);

    render(
        <PriceArea
            agencyAccount={agencyAccount}
            defaultZeiKbn={defaultZeiKbn}
            zeiKbns={parsedZeiKbns}
            defaultValue={parsedDefaultValue}
        />,
        document.getElementById("hotelPriceArea")
    );
}
