import "select2";
import "select2/dist/css/select2.css";

// 親会社選択
$(() => {
    $("#agencyId").select2({
        language: {
            errorLoading: function() {
                return "内容を取得できません";
            },
            inputTooLong: function(args) {
                var overChars = args.input.length - args.maximum;

                return "入力が " + overChars + " 文字超過しています";
            },
            inputTooShort: function(args) {
                var remainingChars = args.minimum - args.input.length;

                return (
                    "検索文字を " + remainingChars + " 字以上入力してください"
                );
            },
            loadingMore: function() {
                return "更に読み込んでいます...";
            },
            maximumSelected: function(args) {
                return "一度に選択できるのは " + args.maximum + " つまでです";
            },
            noResults: function() {
                return "一致するものがありません";
            },
            searching: function() {
                return "検索中...";
            }
        },
        minimumInputLength: 2, //検索実行ミニマム文字数
        ajax: {
            url: "/api/agency/select-search-companyname",
            dataType: "json",
            delay: 500,
            data: params => {
                return {
                    name: params.term,
                    page: params.page || 1
                };
            },
            processResults: (data, params) => {
                const results = data.data.map(item => {
                    return {
                        id: item.id,
                        text: `#${item.id} - ${item.company_name}`
                    };
                });
                return {
                    results: results,
                    pagination: {
                        more: data.next_page_url ? true : false
                    }
                };
            }
        }
    });
});
