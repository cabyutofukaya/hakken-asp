$(() => {
    $(".show-detail").on("click", function(e) {
        const data = $(this).data("json");

        $("#modelTxt").text(`${data.model} #${data.model_id}`);
        $("#roleTxt").text(`${data.guard} #${data.user_id}`);
        $("#operationTxt").text(data.operation_type);
        $("#messageTxt").html(
            JSON.stringify(JSON.parse(data.message), null, "\t")
        );
        $("#createdAtTxt").text(data.created_at);
    });
});
