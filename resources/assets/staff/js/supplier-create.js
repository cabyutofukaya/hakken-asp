import React, { useState } from "react";
import ConstApp from "./components/ConstApp";
import { render } from "react-dom";
import SupplierAccountPayable from "./components/Supplier/SupplierAccountPayable";

/**
 * 振込先情報 登録
 *
 * @param {*} param0
 * @returns
 */
const SupplierAccountPayableArea = ({ defaultValue, formSelects }) => {
    const [rowCount, setRowCount] = useState(
        defaultValue?.supplier_account_payables
            ? defaultValue.supplier_account_payables.length
            : 1
    ); // 行数

    return (
        <>
            {[...Array(rowCount).keys()].map(n => (
                <SupplierAccountPayable
                    key={n}
                    rowCount={n}
                    defaultValue={
                        defaultValue?.supplier_account_payables?.[rowCount - 1]
                    }
                    formSelects={formSelects}
                    bankAccountTypes={formSelects?.bankAccountTypes}
                />
            ))}
        </>
    );
};

const Element = document.getElementById("supplierAccountPayableArea");
if (Element) {
    const jsVars = Element.getAttribute("jsVars");
    const parsedJsVars = jsVars && JSON.parse(jsVars);
    const defaultValue = Element.getAttribute("defaultValue");
    const parsedDefaultValue = defaultValue && JSON.parse(defaultValue);
    const formSelects = Element.getAttribute("formSelects");
    const parsedFormSelects = formSelects && JSON.parse(formSelects);
    render(
        <ConstApp jsVars={parsedJsVars}>
            <SupplierAccountPayableArea
                defaultValue={parsedDefaultValue}
                formSelects={parsedFormSelects}
            />
        </ConstApp>,
        document.getElementById("supplierAccountPayableArea")
    );
}
