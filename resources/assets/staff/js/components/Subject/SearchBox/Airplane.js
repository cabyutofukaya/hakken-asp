import React from "react";
import SearchOption from "../../SearchOption";
import SearchOptionItem from "../../SearchOptionItem";

const Airplane = ({
    show,
    agencyAccount,
    customCategoryCode,
    formSelects,
    customItemTypes,
    input,
    setInput,
    handleSearch,
    subjectCategoryCode,
    customFieldCodes
}) => {
    // form入力
    const handleChangeInput = e => {
        setInput({ ...input, [e.target.name]: e.target.value });
    };

    // リセットボタン
    const handleReset = e => {
        e.preventDefault();
        setInput({});
    };

    return (
        <>
            <div id="searchBox" style={{ display: !show ? "none" : "" }}>
                <div id="inputList">
                    <ul className="sideList">
                        <li className="wd20">
                            <span className="inputLabel">商品コード</span>
                            <input
                                type="text"
                                name="code"
                                value={input?.code ?? ""}
                                onChange={handleChangeInput}
                            />
                        </li>
                        <li className="wd50">
                            <span className="inputLabel">商品名</span>
                            <input
                                type="text"
                                name="name"
                                value={input?.name ?? ""}
                                onChange={handleChangeInput}
                            />
                        </li>

                        {/**カスタム項目「航空会社」 */}
                        <SearchOptionItem
                            agencyAccount={agencyAccount}
                            customCategoryId={customCategoryCode}
                            item={_.find(
                                formSelects?.userCustomFields?.[
                                    subjectCategoryCode
                                ],
                                {
                                    code:
                                        customFieldCodes.subject_airplane_company
                                }
                            )}
                            input={input}
                            customItemTypes={customItemTypes}
                            handleChange={handleChangeInput}
                            className={"wd30 mr00"}
                        />
                    </ul>
                    <ul className="sideList">
                        <li className="wd30">
                            <span className="inputLabel">出発地</span>
                            <div className="selectBox">
                                <select
                                    name="departure_id"
                                    value={input?.departure_id ?? ""}
                                    onChange={handleChangeInput}
                                >
                                    {formSelects?.cities &&
                                        Object.keys(formSelects.cities)
                                            .sort((a, b) => {
                                                // 数字ソート
                                                return a - b;
                                            })
                                            .map((v, index) => (
                                                <option key={index} value={v}>
                                                    {formSelects.cities[v]}
                                                </option>
                                            ))}
                                </select>
                            </div>
                        </li>
                        <li className="wd30">
                            <span className="inputLabel">目的地</span>
                            <div className="selectBox">
                                <select
                                    name="destination_id"
                                    value={input?.destination_id ?? ""}
                                    onChange={handleChangeInput}
                                >
                                    {formSelects?.cities &&
                                        Object.keys(formSelects.cities)
                                            .sort((a, b) => {
                                                // 数字ソート
                                                return a - b;
                                            })
                                            .map((v, index) => (
                                                <option key={index} value={v}>
                                                    {formSelects.cities[v]}
                                                </option>
                                            ))}
                                </select>
                            </div>
                        </li>
                        <li className="wd40 mr00">
                            <span className="inputLabel">仕入れ先</span>
                            <div className="selectBox">
                                <select
                                    name="supplier_id"
                                    value={input?.supplier_id ?? ""}
                                    onChange={handleChangeInput}
                                >
                                    {formSelects?.suppliers &&
                                        Object.keys(formSelects.suppliers)
                                            .sort((a, b) => {
                                                // 数字ソート
                                                return a - b;
                                            })
                                            .map((v, index) => (
                                                <option key={index} value={v}>
                                                    {formSelects.suppliers[v]}
                                                </option>
                                            ))}
                                </select>
                            </div>
                        </li>
                    </ul>
                    {_.filter(
                        formSelects?.userCustomFields?.[subjectCategoryCode],
                        row => {
                            return (
                                row.code !=
                                customFieldCodes.subject_airplane_company
                            );
                        }
                    ) && (
                        <SearchOption
                            agencyAccount={agencyAccount}
                            customCategoryId={customCategoryCode}
                            userCustomFields={_.filter(
                                formSelects.userCustomFields[
                                    subjectCategoryCode
                                ],
                                row => {
                                    return (
                                        row.code !=
                                        customFieldCodes.subject_airplane_company
                                    );
                                }
                            )}
                            customItemTypes={customItemTypes}
                            input={input}
                            handleChangeInput={handleChangeInput}
                        />
                    )}
                </div>
                <div id="controlList">
                    <ul>
                        <li>
                            <button
                                className="orangeBtn icon-left"
                                onClick={e =>
                                    handleSearch(e, subjectCategoryCode)
                                }
                            >
                                <span className="material-icons">search</span>
                                検索
                            </button>
                        </li>
                        <li>
                            <button
                                className="grayBtn slimBtn"
                                onClick={handleReset}
                            >
                                条件クリア
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </>
    );
};

export default Airplane;
