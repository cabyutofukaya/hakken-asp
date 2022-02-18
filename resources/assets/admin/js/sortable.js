$(() => {
    $("[data-sort]").on("click", function() {
        const sort = $(this).data("sort");

        const url = new URL(location);
        const dp = url.searchParams.get("direction");

        url.searchParams.set("sort", sort);
        url.searchParams.set("direction", dp === "asc" ? "desc" : "asc");

        location.href = url.toString();
    });
});
