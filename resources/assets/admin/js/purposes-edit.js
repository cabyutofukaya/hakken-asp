//更新
$(() => {
    $("#updateButton").on("click", function(e) {
        e.preventDefault();
        $("#updateForm").trigger("submit");
    });
});
