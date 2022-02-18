// パスワード表示・非表示
const toggleShowPasswordText = () => {
    $("#showPassword").text(
        $("#password").attr("type") === "password" ? "表示" : "非表示"
    );
};
$(() => {
    toggleShowPasswordText();
    $("#showPassword").on("click", function(e) {
        e.preventDefault();
        $("#password").attr(
            "type",
            $("#password").attr("type") === "password" ? "text" : "password"
        );
        toggleShowPasswordText();
    });
});

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
