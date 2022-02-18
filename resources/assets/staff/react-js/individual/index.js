import React from "react";
import ReactDOM from "react-dom";
import IndividualList from "./IndividualList";

// ReactDOM.render(document.getElementById("individualTitle"));

const element = document.getElementById("individualList");
if (element) {
    const agencyAccount = element.getAttribute("agency-account");
    ReactDOM.render(
        <IndividualList agencyAccount={agencyAccount} />,
        document.getElementById("individualList")
    );
}
