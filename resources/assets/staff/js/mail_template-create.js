// オプションのcheckboxが1つも選択されていない場合、同グループの独自タグは非活性に
function toggleTagButton(tagGroup) {
    $(`[data-has_child='${tagGroup}']`).prop(
        "disabled",
        $(`input:checkbox[data-tag_group="${tagGroup}"]:checked`).length === 0
    );
}

$(() => {
    // 独自タグボタン押下でtextareaに独自タグ挿入
    $("[data-tag]").on("click", function(e) {
        e.preventDefault();

        const tags = [];
        if ($(this).data("has_child")) {
            // オプションタグがある場合は（申込者に対する「漢字」など）チェック済みタグをtags配列に詰める
            let group = $(this).data("has_child");

            $(`input:checkbox[data-tag_group="${group}"]:checked`).each(
                function() {
                    tags.push($(this).val());
                }
            );
        }

        const val = $(this).data("tag");
        tags.unshift(val); // オプションタグとメインタグをtags配列に詰める

        const tag = "{%" + tags.join(",") + "%}"; // タグ配列を文字列に変換

        const textarea = document.querySelector("textarea[name='body']");
        let sentence = textarea.value;
        const len = sentence.length;
        const pos = textarea.selectionStart;
        const before = sentence.substr(0, pos);
        const after = sentence.substr(pos, len);
        sentence = before + tag + after;
        textarea.value = sentence;
    });
});

$(() => {
    // 初回アクセス時、オプションのチェックボックスのチェック状態をチェックし、同グループのタグボタンの活性化・非活性化
    $("[data-tag_group]").each(function() {
        toggleTagButton($(this).data("tag_group"));
    });

    // オプションのチェックボックスが変更されたら同グループのタグボタンの活性化・非活性化をチェック
    $("[data-tag_group]").on("change", function(e) {
        toggleTagButton($(this).data("tag_group"));
    });
});
