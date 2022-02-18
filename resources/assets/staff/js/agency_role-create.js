/**
 * 権限対象チェックボックスの全選択⇆全解除
 */
$(() => {
    // 全選択
    $("[data-target_on]").on("click", function() {
        const target = $(this).data("target_on");
        $(`[data-target='${target}']`)
            .find("input[type=checkbox]:enabled")
            .not(":disabled") // 操作対象は有効要素のみ
            .prop("checked", true);
    });
    // 全解除
    $("[data-target_off]").on("click", function() {
        const target = $(this).data("target_off");
        $(`[data-target='${target}']`)
            .find("input[type=checkbox]")
            .not(":disabled") // 操作対象は有効要素のみ
            .prop("checked", false);
    });
});
