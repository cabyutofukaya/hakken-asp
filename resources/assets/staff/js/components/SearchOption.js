import React from "react";
import SearchOptionItem from "./SearchOptionItem";

const SearchOption = ({
    agencyAccount,
    customCategoryId,
    userCustomFields,
    customItemTypes,
    input,
    handleChangeInput
}) => {
    return (
        <>
            {userCustomFields.length > 0 && (
                <>
                    <div className="toggleOption">
                        <p>検索オプション</p>
                    </div>
                    <div id="searchOption">
                        <ul className="sideList customSearch">
                            {userCustomFields.map((row, index) => (
                                <SearchOptionItem
                                    key={index}
                                    agencyAccount={agencyAccount}
                                    customCategoryId={customCategoryId}
                                    item={row}
                                    input={input}
                                    customItemTypes={customItemTypes}
                                    handleChange={handleChangeInput}
                                />
                            ))}
                        </ul>
                    </div>
                </>
            )}
        </>
    );
};

export default SearchOption;
