import _ from "lodash";
import React, { useState, useContext, useRef } from "react";
import { render } from "react-dom";
import OptionSearchBox from "./components/Subject/SearchBox/Option";
import AirplaneSearchBox from "./components/Subject/SearchBox/Airplane";
import HotelSearchBox from "./components/Subject/SearchBox/Hotel";
import OptionIndex from "./components/Subject/IndexList/Option";
import AirplaneIndex from "./components/Subject/IndexList/Airplane";
import HotelIndex from "./components/Subject/IndexList/Hotel";
import ConstApp from "./components/ConstApp";
import { ConstContext } from "./components/ConstApp";
import { useMountedRef } from "../../hooks/useMountedRef";
import SmallDangerModal from "./components/SmallDangerModal";

const SubjectIndexArea = ({
    customCategoryCode,
    defaultTab,
    createLinks,
    formSelects,
    customItemTypes,
    subjectCategoryCodes,
    consts,
    permission,
    successMessage
}) => {
    const mounted = useMountedRef(); // マウント・アンマウント制御

    // 親から各タブのコンポーネントの削除メソッドを呼ぶのに使用
    const optionRef = useRef(); // オプション科目
    const airplaneRef = useRef(); // 航空券科目
    const hotelRef = useRef(); // ホテル科目

    const { agencyAccount, subjectCategories } = useContext(ConstContext);

    const [currentTab, setCurrentTab] = useState(defaultTab); //選択中のタブ

    const [optionInput, setOptionInput] = useState({}); // オプション科目 検索パラメータ
    const [optionRequestId, setOptionRequestId] = useState(null); // 検索を実行させるためのトリガーパラメータ

    const [airplaneInput, setAirplaneInput] = useState({}); // 航空券科目 検索パラメータ
    const [airplaneRequestId, setAirplaneRequestId] = useState(null); // 検索を実行させるためのトリガーパラメータ

    const [hotelInput, setHotelInput] = useState({}); // ホテル科目 検索パラメータ
    const [hotelRequestId, setHotelRequestId] = useState(null); // 検索を実行させるためのトリガーパラメータ

    const [isDeleting, setIsDeleting] = useState(false); // 削除処理中か否か
    const [deleteInfo, setDeleteInfo] = useState({}); // 削除対象レコード情報(id、科目名)を管理

    // 削除ボタンを押した時の挙動
    const handleDelete = e => {
        if (deleteInfo?.subject == subjectCategories.subject_category_option) {
            // オプション科目削除
            optionRef.current.handleDelete();
        } else if (
            deleteInfo?.subject == subjectCategories.subject_category_airplane
        ) {
            // 航空券科目削除
            airplaneRef.current.handleDelete();
        } else if (
            deleteInfo?.subject == subjectCategories.subject_category_hotel
        ) {
            // ホテル科目削除
            hotelRef.current.handleDelete();
        }
    };

    // タブクリック
    const handleTabChange = (e, tab) => {
        e.preventDefault();
        if (isDeleting) return; // 削除処理中の場合は、念の為タブの切り替え不可
        setCurrentTab(tab);
    };

    // 新規作成クリック → 新規登録ページへ遷移
    const handleCreateClick = e => {
        e.preventDefault();
        if (_.get(createLinks, currentTab)) {
            window.location.href = _.get(createLinks, currentTab);
        }
        return;
    };

    /**
     * 検索ボタン押下
     * @param {*} e
     * @param {*} target option or airplane or hotel
     */
    const handleSearchClick = (e, target) => {
        e.preventDefault();
        if (target === subjectCategoryCodes.subject_category_option) {
            setOptionRequestId(new Date().getTime()); // リクエストIDを変更 → 検索API実行
        } else if (target === subjectCategoryCodes.subject_category_airplane) {
            setAirplaneRequestId(new Date().getTime()); // リクエストIDを変更 → 検索API実行
        } else if (target === subjectCategoryCodes.subject_category_hotel) {
            setHotelRequestId(new Date().getTime()); // リクエストIDを変更 → 検索API実行
        }
    };

    return (
        <>
            <div id="pageHead">
                <h1>
                    <span className="material-icons">list</span>科目マスタ
                </h1>
                <div className="subjectMasterNav">
                    <ul>
                        <li
                            className={
                                currentTab ===
                                subjectCategoryCodes.subject_category_option
                                    ? "stay"
                                    : ""
                            }
                        >
                            <a
                                onClick={e =>
                                    handleTabChange(
                                        e,
                                        subjectCategoryCodes.subject_category_option
                                    )
                                }
                            >
                                オプション科目
                            </a>
                        </li>
                        <li
                            className={
                                currentTab ===
                                subjectCategoryCodes.subject_category_airplane
                                    ? "stay"
                                    : ""
                            }
                        >
                            <a
                                onClick={e =>
                                    handleTabChange(
                                        e,
                                        subjectCategoryCodes.subject_category_airplane
                                    )
                                }
                            >
                                航空券科目
                            </a>
                        </li>
                        <li
                            className={
                                currentTab ===
                                subjectCategoryCodes.subject_category_hotel
                                    ? "stay"
                                    : ""
                            }
                        >
                            <a
                                onClick={e =>
                                    handleTabChange(
                                        e,
                                        subjectCategoryCodes.subject_category_hotel
                                    )
                                }
                            >
                                ホテル科目
                            </a>
                        </li>
                    </ul>
                </div>
                {/**作成権限があれば表示 */}
                {permission?.[currentTab]?.create && (
                    <div className="rtBtn">
                        <button onClick={handleCreateClick} className="addBtn">
                            <span className="material-icons">list</span>新規追加
                        </button>
                    </div>
                )}
                <OptionSearchBox
                    show={
                        currentTab ===
                        subjectCategoryCodes.subject_category_option
                    }
                    agencyAccount={agencyAccount}
                    customCategoryCode={customCategoryCode}
                    formSelects={formSelects}
                    customItemTypes={customItemTypes}
                    handleSearch={handleSearchClick}
                    input={optionInput}
                    setInput={setOptionInput}
                    subjectCategoryCode={
                        subjectCategoryCodes.subject_category_option
                    }
                    customFieldCodes={consts?.customFieldCodes}
                />
                <AirplaneSearchBox
                    show={
                        currentTab ===
                        subjectCategoryCodes.subject_category_airplane
                    }
                    agencyAccount={agencyAccount}
                    customCategoryCode={customCategoryCode}
                    formSelects={formSelects}
                    customItemTypes={customItemTypes}
                    handleSearch={handleSearchClick}
                    input={airplaneInput}
                    setInput={setAirplaneInput}
                    subjectCategoryCode={
                        subjectCategoryCodes.subject_category_airplane
                    }
                    customFieldCodes={consts?.customFieldCodes}
                />
                <HotelSearchBox
                    show={
                        currentTab ===
                        subjectCategoryCodes.subject_category_hotel
                    }
                    agencyAccount={agencyAccount}
                    customCategoryCode={customCategoryCode}
                    formSelects={formSelects}
                    customItemTypes={customItemTypes}
                    handleSearch={handleSearchClick}
                    input={hotelInput}
                    setInput={setHotelInput}
                    subjectCategoryCode={
                        subjectCategoryCodes.subject_category_hotel
                    }
                    customFieldCodes={consts?.customFieldCodes}
                />
            </div>
            {currentTab === subjectCategoryCodes.subject_category_option && (
                <OptionIndex
                    agencyAccount={agencyAccount}
                    searchParam={optionInput}
                    requestId={optionRequestId}
                    successMsg={successMessage}
                    isDeleting={isDeleting}
                    setIsDeleting={setIsDeleting}
                    deleteInfo={deleteInfo}
                    setDeleteInfo={setDeleteInfo}
                    ref={optionRef}
                />
            )}
            {currentTab === subjectCategoryCodes.subject_category_airplane && (
                <AirplaneIndex
                    agencyAccount={agencyAccount}
                    searchParam={airplaneInput}
                    requestId={airplaneRequestId}
                    successMsg={successMessage}
                    isDeleting={isDeleting}
                    setIsDeleting={setIsDeleting}
                    deleteInfo={deleteInfo}
                    setDeleteInfo={setDeleteInfo}
                    ref={airplaneRef}
                />
            )}
            {currentTab === subjectCategoryCodes.subject_category_hotel && (
                <HotelIndex
                    agencyAccount={agencyAccount}
                    searchParam={hotelInput}
                    requestId={hotelRequestId}
                    successMsg={successMessage}
                    isDeleting={isDeleting}
                    setIsDeleting={setIsDeleting}
                    deleteInfo={deleteInfo}
                    setDeleteInfo={setDeleteInfo}
                    ref={hotelRef}
                />
            )}
            <SmallDangerModal
                id="mdDeleteSubject"
                title="この項目を削除しますか？"
                handleAction={handleDelete}
                isActioning={isDeleting}
            />
        </>
    );
};

const Element = document.getElementById("subjectIndexArea");
if (Element) {
    const jsVars = Element.getAttribute("jsVars");
    const parsedJsVars = jsVars && JSON.parse(jsVars);
    const agencyAccount = Element.getAttribute("agencyAccount");
    const customCategoryCode = Element.getAttribute("customCategoryCode");
    const defaultTab = Element.getAttribute("defaultTab");
    const createLinks = Element.getAttribute("createLinks");
    const parsedCreateLinks = createLinks && JSON.parse(createLinks);
    const formSelects = Element.getAttribute("formSelects");
    const parsedFormSelects = formSelects && JSON.parse(formSelects);
    const customItemTypes = Element.getAttribute("customItemTypes");
    const parsedCustomItemTypes =
        customItemTypes && JSON.parse(customItemTypes);
    const subjectCategoryCodes = Element.getAttribute("subjectCategoryCodes");
    const parsedSubjectCategoryCodes =
        subjectCategoryCodes && JSON.parse(subjectCategoryCodes);
    const consts = Element.getAttribute("consts");
    const parsedConsts = consts && JSON.parse(consts);
    const permission = Element.getAttribute("permission");
    const parsedPermission = permission && JSON.parse(permission);
    const successMessage = Element.getAttribute("successMessage");

    render(
        <ConstApp jsVars={parsedJsVars}>
            <SubjectIndexArea
                customCategoryCode={customCategoryCode}
                defaultTab={defaultTab}
                createLinks={parsedCreateLinks}
                formSelects={parsedFormSelects}
                customItemTypes={parsedCustomItemTypes}
                subjectCategoryCodes={parsedSubjectCategoryCodes}
                consts={parsedConsts}
                permission={parsedPermission}
                successMessage={successMessage}
            />
        </ConstApp>,
        document.getElementById("subjectIndexArea")
    );
}
