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
