import React from "react";
import { render } from "react-dom";
import { getParam } from "./libs";
import PriceArea from "./components/Subject/PriceArea";
import CityArea from "./components/Subject/CityArea";
import ConstApp from "./components/ConstApp";

const changeInputArea = currentCategory => {
    $("[data-form]").hide();
    $(`[data-form='${currentCategory}']`).show();
    $(`[data-form='${currentCategory}']`)
        .find("[name='category']")
        .val(currentCategory); // hiddenフィールドに値をセット
};

// 科目カテゴリによってformエリアを切り替え
$(() => {
    const category = getParam("tab");

    $("select[name='category']").val(category);
    changeInputArea(category);

    $("select[name='category']").on("change", function() {
        const category = $(this).val();
        changeInputArea(category);
    });
});

// 選択した科目カテゴリに合わせてform送信切り替え
$(() => {
    $("#submit").on("click", function(e) {
        e.preventDefault();
        const category = $("select[name='category']").val();
        $(`#${category}Form`).trigger("submit");
    });
});

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
