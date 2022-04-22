import React from "react";
import AreaInput from "../AreaInput";
import CustomField from "../CustomField";
import CustomerSelect from "../Reserve/CustomerSelect";
// flatpickr
import "flatpickr/dist/themes/airbnb.css";
import { Japanese } from "flatpickr/dist/l10n/ja.js";
import Flatpickr from "react-flatpickr";

const BasicInfoInputArea = ({
    input,
    setInput,
    participantTypes,
    customerKbns,
    countries,
    sexes,
    ageKbns,
    birthdayYears,
    birthdayMonths,
    birthdayDays,
    prefectures,
    defaultAreas,
    customFields,
    customFieldPositions,
    customFieldCodes,
    customCategoryCode,
    handleChange,
    handleAreaChange,
    clearApplicantUserNumber,
    userAddModalDefaultValue
}) => {
    return (
        <div id="inputArea">
            <CustomerSelect
                customerTypes={participantTypes}
                customerKbns={customerKbns}
                customerType={input?.participant_type}
                countries={countries}
                sexes={sexes}
                ageKbns={ageKbns}
                birthdayYears={birthdayYears}
                birthdayMonths={birthdayMonths}
                birthdayDays={birthdayDays}
                prefectures={prefectures}
                userNumber={input?.applicant_user_number}
                searchUserNumber={input?.search_user_number}
                searchUserName={input?.search_user_name}
                getDeleted={input?.applicant_search_get_deleted}
                handleChange={handleChange}
                handleCustomerTypeChange={e => {
                    setInput({
                        ...input,
                        applicant_user_number: "", // use_numberをクリアする
                        [e.target.name]: e.target.value
                    });
                }}
                clearUserNumber={clearApplicantUserNumber}
                userAddModalDefaultValue={userAddModalDefaultValue}
            />

            <hr className="sepBorder" />
            <ul className="sideList">
                <li className="wd70">
                    <span className="inputLabel">旅行名</span>
                    <input
                        type="text"
                        name="name"
                        value={input.name ?? ""}
                        onChange={handleChange}
                    />
                </li>
                {_.has(customFields, customFieldPositions.base) &&
                    // code=travel_typeのobjectを検索して配列に格納
                    _.filter(customFields[customFieldPositions.base], {
                        code: customFieldCodes.travel_type
                    }).map((row, index) => (
                        <CustomField
                            key={index}
                            customCategoryCode={customCategoryCode}
                            type={row?.type}
                            inputType={row?.input_type}
                            label={row?.name}
                            name={row?.key}
                            list={row?.list}
                            value={input?.[row?.key]}
                            handleChange={handleChange}
                            uneditItem={row?.unedit_item}
                            liClass={"wd30 mr00"}
                        />
                    ))}
            </ul>
            <ul className="sideList half">
                <li>
                    <span className="inputLabel req">出発日</span>
                    <div className="calendar">
                        <Flatpickr
                            theme="airbnb"
                            value={input?.departure_date ?? ""}
                            onChange={date => {
                                handleChange({
                                    target: {
                                        name: "departure_date",
                                        value: date
                                    }
                                });
                            }}
                            options={{
                                dateFormat: "Y/m/d",
                                locale: {
                                    ...Japanese
                                }
                            }}
                            render={(
                                { defaultValue, value, ...props },
                                ref
                            ) => {
                                return (
                                    <input
                                        name="departure_date"
                                        defaultValue={value ?? ""}
                                        ref={ref}
                                    />
                                );
                            }}
                        />
                    </div>
                </li>
                <li>
                    <span className="inputLabel req">帰着日</span>
                    <div className="calendar">
                        <Flatpickr
                            theme="airbnb"
                            value={input?.return_date ?? ""}
                            onChange={date => {
                                handleChange({
                                    target: {
                                        name: "return_date",
                                        value: date
                                    }
                                });
                            }}
                            options={{
                                dateFormat: "Y/m/d",
                                locale: {
                                    ...Japanese
                                }
                            }}
                            render={(
                                { defaultValue, value, ...props },
                                ref
                            ) => {
                                return (
                                    <input
                                        name="return_date"
                                        defaultValue={value ?? ""}
                                        ref={ref}
                                    />
                                );
                            }}
                        />
                    </div>
                </li>
                <li>
                    <span className="inputLabel">出発地</span>
                    <ul className="sideList">
                        <li className="wd40">
                            <AreaInput
                                name="departure_id"
                                defaultValue={input?.departure}
                                defaultOptions={defaultAreas}
                                handleAreaChange={handleAreaChange}
                            />
                        </li>
                        <li className="wd60">
                            <input
                                type="text"
                                name="departure_place"
                                value={input.departure_place ?? ""}
                                onChange={handleChange}
                                placeholder="住所・名称"
                            />
                        </li>
                    </ul>
                </li>
                <li>
                    <span className="inputLabel">目的地</span>
                    <ul className="sideList">
                        <li className="wd40">
                            <AreaInput
                                name="destination_id"
                                defaultValue={input?.destination}
                                defaultOptions={defaultAreas}
                                handleAreaChange={handleAreaChange}
                            />
                        </li>
                        <li className="wd60">
                            <input
                                type="text"
                                name="destination_place"
                                value={input.destination_place ?? ""}
                                onChange={handleChange}
                                placeholder="住所・名称"
                            />
                        </li>
                    </ul>
                </li>
                {_.has(customFields, customFieldPositions.base) &&
                    // code=travel_type以外のカスタム項目(基本情報)を取得
                    _.reject(customFields[customFieldPositions.base], {
                        code: customFieldCodes.travel_type
                    }).map((row, index) => (
                        <CustomField
                            key={index}
                            customCategoryCode={customCategoryCode}
                            type={row?.type}
                            inputType={row?.input_type}
                            label={row?.name}
                            name={row?.key}
                            list={row?.list}
                            value={input?.[row?.key]}
                            handleChange={handleChange}
                            uneditItem={row?.unedit_item}
                        />
                    ))}
                <li className="wd100 mr00">
                    <span className="inputLabel"> 備考</span>
                    <textarea
                        rows="3"
                        name="note"
                        onChange={handleChange}
                        value={input.note ?? ""}
                    ></textarea>
                </li>
            </ul>
            <input
                type="hidden"
                name="updated_at"
                value={input.updated_at ?? ""}
            />
        </div>
    );
};

export default BasicInfoInputArea;
