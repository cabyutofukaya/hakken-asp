import { getAgencyAccountFromUrl } from "./libs";

// axiosのエラーハンドリング
axios.interceptors.response.use(
    function(response) {
        // 成功時の処理
        return response;
    },
    function(error) {
        if (error?.response) {
            if (error.response?.status === 422) {
                let msg = [];
                for (let key in error.response.data.errors) {
                    msg.push(error.response.data.errors[key][0]);
                }
                alert(msg.join("\n"));
            } else if (error?.response?.status === 403) {
                alert(
                    error.response?.data?.message
                        ? error.response.data.message
                        : "許可されていなリクエストです(403)"
                );
            } else if (error?.response?.status === 404) {
                console.log(error.response);
                alert(
                    error.response?.data?.message
                        ? error.response.data.message
                        : "存在しないリソースです(404)"
                );
            } else if (error?.response?.status === 409) {
                alert(
                    error.response?.data?.message
                        ? error.response.data?.message
                        : "リソース競合エラーです(409)"
                );
            } else if (error?.response?.status === 401) {
                alert(
                    "長時間操作がなかったためセッションが切れた可能性がります。\n再度ログインお願いします。"
                );
                const agencyAccount = getAgencyAccountFromUrl();
                if (agencyAccount) {
                    // pathParts[0]は会社アカウント
                    location.href = `/${agencyAccount}/login`;
                }
            } else if (error?.response?.status === 500) {
                alert(
                    error.response.data?.message
                        ? error.response.data.message
                        : "サーバーエラーです(500)。サーバー管理者にお問い合わせください"
                );
            } else {
                alert(
                    error?.response?.data?.message
                        ? error.response.data?.message
                        : error?.response?.status
                );
            }
        } else if (error.request) {
            //  Request aborted など
            console.log(error);
        } else {
            console.log(error);
            alert("Error: ", error.message);
        }
    }
);

// resetボタン
$('[type="reset"]').on("click", function(e) {
    e.preventDefault();
    $(this.form)
        .find("textarea, :text, select, [type='email']")
        .val("")
        .end()
        .find(":checked")
        .prop("checked", false);
});

// 検索オプションを初期状態で開いておくか否かのパラメータ
$(".toggleOption").on("click", function() {
    if ($(this).hasClass("active")) {
        $("[name='search_option_open']").val("");
    } else {
        $("[name='search_option_open']").val(1);
    }
});

// submitの二重送信防止
$(".doubleBan").on("click", function() {
    $(this).css("pointer-events", "none");
});

$(".current").on("click", function() {
    if ($(this).hasClass("active")) {
        // $(this).removeClass("active"); 閉じるアクション不要かも、ということで一旦コメント
    } else {
        $(this).addClass("active");
    }
});

$("#minimal").on("click", function() {
    if ($("#content").hasClass("resize")) {
        $(this).removeClass("active");
        $("#content").removeClass("resize");
    } else {
        $(this).addClass("active");
        $("#content").addClass("resize");
    }
});

$(".toggleOption").on("click", function() {
    if ($(this).hasClass("active")) {
        $(this).removeClass("active");
        $(this)
            .next()
            .slideUp();
    } else {
        $(this).addClass("active");
        $(this)
            .next()
            .slideDown();
    }
});
$(".memberToggle").on("click", function() {
    if ($(this).hasClass("active")) {
        $(this).removeClass("active");
        $(".memberList").slideUp();
    } else {
        $(this).addClass("active");
        $(".memberList").slideDown();
    }
});

$(".sort").on("click", function() {
    $(this).toggleClass("active");
});

$(document).on("click", ".closeIcon", function() {
    $(this)
        .parent()
        .slideUp();
});

$(function() {
    let scrollTop = 0;

    $(document).on("click", ".js-modal-open", function() {
        scrollTop = $(window).scrollTop();
        $("body").css({
            // position: "fixed",
            top: -1 * scrollTop
        });

        var target = $(this).data("target");
        var modal = document.getElementById(target);
        $(modal).fadeIn();
        $(modal)
            .find(".modal__content")
            .scrollTop(0); // scroll位置をリセットしておく
        return false;
    });
    $(".js-modal-close").on("click", function() {
        $("body").css({
            position: "static"
        });
        $("html, body").prop({ scrollTop: scrollTop });

        $(".js-modal").fadeOut();
        return false;
    });
});

$(function() {
    function mousedragscrollable(element) {
        let target; // 動かす対象
        $(element).each(function(i, e) {
            $(e).mousedown(function(event) {
                event.preventDefault();
                target = $(e); // 動かす対象
                $(e).data({
                    down: true,
                    move: false,
                    x: event.clientX,
                    y: event.clientY,
                    scrollleft: $(e).scrollLeft(),
                    scrolltop: $(e).scrollTop()
                });
                return false;
            });
            // move後のlink無効
            $(e).click(function(event) {
                if ($(e).data("move")) {
                    return false;
                }
            });
        });
        // list要素内/外でのevent
        $(document)
            .mousemove(function(event) {
                if ($(target).data("down")) {
                    event.preventDefault();
                    let move_x = $(target).data("x") - event.clientX;
                    let move_y = $(target).data("y") - event.clientY;
                    if (move_x !== 0 || move_y !== 0) {
                        $(target).data("move", true);
                    } else {
                        return;
                    }
                    $(target).scrollLeft($(target).data("scrollleft") + move_x);
                    $(target).scrollTop($(target).data("scrolltop") + move_y);
                    return false;
                }
            })
            .mouseup(function(event) {
                $(target).data("down", false);
                return false;
            });
    }
    mousedragscrollable(".dragTable");
});

$(function() {
    let tabs = $(".tab");

    $(".tab").on("click", function() {
        $(".tabstay").removeClass("tabstay");
        $(this).addClass("tabstay");
        const index = tabs.index(this);
        $(".customList,.userList")
            .removeClass("show")
            .eq(index)
            .addClass("show");
    });
});

$(function() {
    var navigationOpenFlag = false;
    var navButtonFlag = true;
    var focusFlag = false;

    //ハンバーガーメニュー
    $(function() {
        $(document).on("click", ".el_news", function() {
            if (navButtonFlag) {
                spNavInOut.switch();
                setTimeout(function() {
                    navButtonFlag = true;
                }, 200);
                navButtonFlag = false;
            }
        });
        $(document).on("click touchend", function(event) {
            if (
                !$(event.target).closest("#news,.el_news").length &&
                $("body").hasClass("js_newsOpen") &&
                focusFlag
            ) {
                focusFlag = false;
                spNavInOut.switch();
            }
        });
    });

    function spNavIn() {
        $("body").removeClass("js_newsClose");
        $("body").addClass("js_newsOpen");
        setTimeout(function() {
            focusFlag = true;
        }, 200);
        setTimeout(function() {
            navigationOpenFlag = true;
        }, 200);
    }

    function spNavOut() {
        $("body").removeClass("js_newsOpen");
        $("body").addClass("js_newsClose");
        setTimeout(function() {
            $(".uq_spNavi").removeClass("js_appear");
            focusFlag = false;
        }, 200);
        navigationOpenFlag = false;
    }

    var spNavInOut = {
        switch: function() {
            if ($("body.spNavFreez").length) {
                return false;
            }
            if ($("body").hasClass("js_newsOpen")) {
                spNavOut();
            } else {
                spNavIn();
            }
        }
    };
});
