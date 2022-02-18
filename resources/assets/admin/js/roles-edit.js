//更新
$(() => {
    $("#updateButton").on("click", function(e) {
        e.preventDefault();
        $("#updateForm").trigger("submit");
    });
});

// 削除
$(() => {
    $("#deleteButton").on("click", function(e) {
        e.preventDefault();
        $("#deleteForm").trigger("submit");
    });
});
