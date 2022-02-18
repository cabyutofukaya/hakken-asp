$(() => {
    // 有効・無効を切り替え
    $("[name='flg[]']").on("click", function() {
        $(this).prop("disabled", true); // 処理が終わるまで選択不可に

        const agencyAccount = $(this).data("agency_account");

        axios
            .post(`/api/${agencyAccount}/toggleFlg`, {
                id: $(this).val(),
                flg: $(this).is(":checked"),
                _method: "put"
            })
            .then(res => {
                //
            })
            .catch(error => {
                alert("有効・無効フラグの更新に失敗しました。");
            })
            .finally(
                function() {
                    $(this).prop("disabled", false);
                }.bind(this)
            );
    });
});
