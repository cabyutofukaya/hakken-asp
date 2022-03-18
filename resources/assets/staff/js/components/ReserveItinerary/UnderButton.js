import React, { useContext } from "react";
import { ConstContext } from "../ConstApp";
import { ReserveItineraryConstContext } from "../ReserveItineraryConstApp";
import classNames from "classnames";

/**
 *
 * @param {bool} canSave 保存ボタンを表示して良い場合はtrue
 * @returns
 */
const UnderButton = ({
    editMode,
    canSave,
    isSubmitting,
    handleSubmit,
    backUrl
}) => {
    const { applicationStep, isCanceled, isEnabled } = useContext(
        ReserveItineraryConstContext
    );
    const { applicationSteps } = useContext(ConstContext);

    const handleBack = () => {
        location.href = backUrl;
    };

    if (isCanceled) {
        return (
            <ul id="formControl">
                <li className="wd50">
                    <button className="grayBtn" onClick={handleBack}>
                        <span className="material-icons">arrow_back_ios</span>
                        戻る
                    </button>
                </li>
            </ul>
        );
    } else {
        return (
            <ul id="formControl">
                <li className="w50">
                    <button className="grayBtn" onClick={handleBack}>
                        <span className="material-icons">arrow_back_ios</span>
                        {editMode == "edit" ? "編集" : "登録"}せずに戻る
                    </button>
                </li>
                {canSave && (
                    <li className="wd50">
                        <button
                            className={classNames("blueBtn", {
                                loading: isSubmitting
                            })}
                            onClick={handleSubmit}
                            disabled={isSubmitting}
                        >
                            <span className="material-icons">save</span>{" "}
                            この内容で{editMode == "edit" ? "更新" : "登録"}する
                        </button>
                    </li>
                )}
            </ul>
        );
    }

    // if (applicationStep == applicationSteps.normal) {
    //     //見積もり
    //     return (
    //         <ul id="formControl">
    //             <li className="w50">
    //                 <button className="grayBtn" onClick={handleBack}>
    //                     <span className="material-icons">arrow_back_ios</span>
    //                     {editMode == "edit" ? "編集" : "登録"}せずに戻る
    //                 </button>
    //             </li>
    //             {canSave && (
    //                 <li className="wd50">
    //                     <button
    //                         className="blueBtn"
    //                         onClick={handleSubmit}
    //                         disabled={isSubmitting}
    //                     >
    //                         <span className="material-icons">save</span>{" "}
    //                         この内容で{editMode == "edit" ? "更新" : "登録"}する
    //                     </button>
    //                 </li>
    //             )}
    //         </ul>
    //     );
    // } else if (applicationStep == applicationSteps.reserve) {
    //     if (isCanceled) {
    //         return (
    //             <ul id="formControl">
    //                 <li className="wd50">
    //                     <button className="grayBtn" onClick={handleBack}>
    //                         <span className="material-icons">
    //                             arrow_back_ios
    //                         </span>
    //                         戻る
    //                     </button>
    //                 </li>
    //             </ul>
    //         );
    //     } else {
    //         return (
    //             <ul id="formControl">
    //                 <li className="wd50">
    //                     <button className="grayBtn" onClick={handleBack}>
    //                         <span className="material-icons">
    //                             arrow_back_ios
    //                         </span>
    //                         {editMode == "edit" ? "編集" : "登録"}せずに戻る
    //                     </button>
    //                 </li>
    //                 {canSave && (
    //                     <li className="wd50">
    //                         <button
    //                             className="blueBtn"
    //                             onClick={handleSubmit}
    //                             disabled={isSubmitting}
    //                         >
    //                             <span className="material-icons">save</span>{" "}
    //                             この内容で{editMode == "edit" ? "更新" : "登録"}
    //                             する
    //                         </button>
    //                     </li>
    //                 )}
    //             </ul>
    //         );
    //     }
    // }
    return null;
};

export default UnderButton;
