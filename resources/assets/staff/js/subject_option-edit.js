import React from "react";
import { render } from "react-dom";
import PriceArea from "./components/Subject/PriceArea";

// オプション科目料金エリア
const OptionPriceArea = document.getElementById("optionPriceArea");
if (OptionPriceArea) {
    const agencyAccount = OptionPriceArea.getAttribute("agencyAccount");
    const defaultZeiKbn = OptionPriceArea.getAttribute("defaultZeiKbn");
    const zeiKbns = OptionPriceArea.getAttribute("zeiKbns");
    const parsedZeiKbns = zeiKbns && JSON.parse(zeiKbns);
    const defaultValue = OptionPriceArea.getAttribute("defaultValue");
    const parsedDefaultValue = defaultValue && JSON.parse(defaultValue);

    render(
        <PriceArea
            agencyAccount={agencyAccount}
            defaultZeiKbn={defaultZeiKbn}
            zeiKbns={parsedZeiKbns}
            defaultValue={parsedDefaultValue}
        />,
        document.getElementById("optionPriceArea")
    );
}
