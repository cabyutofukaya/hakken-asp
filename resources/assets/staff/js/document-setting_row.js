$(() => {
    $('input[name^="setting"]').on("change", function() {
        const val = $(this).val();
        const [parent, child] = val.split("_");
        if ($(this).prop("checked")) {
            if (child) {
                // 子項目の場合は親項目のチェックもON
                $(this)
                    .closest("ul")
                    .find(`[value="${parent}"]`)
                    .prop("checked", true);
            }
        } else {
            if (!child) {
                // 親項目の場合は子項目のチェックもOFF
                $(`[value^="${parent}_"]`).prop("checked", false);
            }
        }
    });
});
