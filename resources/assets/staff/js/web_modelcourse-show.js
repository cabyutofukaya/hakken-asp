import React, { useState, useContext } from "react";
import { ConstContext } from "./components/ConstApp";
import ConstApp from "./components/ConstApp";
import { render } from "react-dom";
import SmallDangerModal from "./components/SmallDangerModal";
import { useMountedRef } from "../../hooks/useMountedRef";

const ControlArea = ({ id, previewUrl }) => {
    const { agencyAccount } = useContext(ConstContext);

    const mounted = useMountedRef(); // マウント・アンマウント制御

    const [isDeleting, setIsDeleting] = useState(false); // 削除処理中か否か

    // プレビューリンクをクリックした時の挙動
    const handleClickPreview = e => {
        e.preventDefault();
        window.open(previewUrl, "_blank");
    };

    // 削除ボタンを押した時の挙動
    const handleDelete = async e => {
        if (!mounted.current) return;
        if (isDeleting) return;

        setIsDeleting(true);

        const response = await axios
            .delete(`/api/${agencyAccount}/web/modelcourse/${id}`, {
                data: {
                    set_message: true // API処理完了後、flashメッセージセットを要求
                }
            })
            .finally(() => {
                $(".js-modal-close").trigger("click"); // 削除モーダルclose
                setTimeout(function() {
                    if (mounted.current) {
                        setIsDeleting(false);
                    }
                }, 3000);
            });

        if (mounted.current && response?.status == 200) {
            location.href = `/${agencyAccount}/front/modelcourse/index`;
        }
    };

    return (
        <>
            {/**プレビューはひとまずナシ */}
            {/* <li>
                <button className="blueBtn" onClick={handleClickPreview}>
                    プレビュー
                </button>
            </li> */}
            <button
                className="redBtn js-modal-open"
                data-target="mdDeleteCourse"
                disabled={isDeleting}
            >
                削除
            </button>
            {/** 削除モーダル*/}
            <SmallDangerModal
                id="mdDeleteCourse"
                title="このモデルコースを削除しますか？"
                handleAction={handleDelete}
                isActioning={isDeleting}
            />
        </>
    );
};

const Element = document.getElementById("controlArea");
if (Element) {
    const jsVars = Element.getAttribute("jsVars");
    const parsedJsVars = jsVars && JSON.parse(jsVars);
    const id = Element.getAttribute("modelcourseId");
    const previewUrl = Element.getAttribute("previewUrl");

    render(
        <ConstApp jsVars={parsedJsVars}>
            <ControlArea id={id} previewUrl={previewUrl} />
        </ConstApp>,
        document.getElementById("controlArea")
    );
}
