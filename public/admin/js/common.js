(window["webpackJsonp"] = window["webpackJsonp"] || []).push([["/admin/js/common"],{

/***/ "./resources/assets/admin/js/common.js":
/*!*********************************************!*\
  !*** ./resources/assets/admin/js/common.js ***!
  \*********************************************/
/*! no static exports found */
/***/ (function(module, exports) {

// resetボタン
$('[type="reset"]').on("click", function (e) {
  e.preventDefault();
  $(this.form).find("textarea, :text, select, [type='email']").val("").end().find(":checked").prop("checked", false);
});
$(".current").on("click", function () {
  if ($(this).hasClass("active")) {
    $(this).removeClass("active");
  } else {
    $(this).addClass("active");
  }
});
$(".addFavorite button").on("click", function () {
  if ($(this).hasClass("active")) {
    $(this).removeClass("active");
    $(".favoriteBox").removeClass("active");
  } else {
    $(this).addClass("active");
    $(".favoriteBox").addClass("active");
  }
});
$("#minimal").on("click", function () {
  if ($("#content").hasClass("resize")) {
    $(this).removeClass("active");
    $("#content").removeClass("resize");
  } else {
    $(this).addClass("active");
    $("#content").addClass("resize");
  }
});
$(".toggleOption").on("click", function () {
  if ($(this).hasClass("active")) {
    $(this).removeClass("active");
    $(this).next().slideUp();
  } else {
    $(this).addClass("active");
    $(this).next().slideDown();
  }
});
$(".memberToggle").on("click", function () {
  if ($(this).hasClass("active")) {
    $(this).removeClass("active");
    $(".memberList").slideUp();
  } else {
    $(this).addClass("active");
    $(".memberList").slideDown();
  }
});
$(".sort").on("click", function () {
  $(this).toggleClass("active");
});
$(document).on("click", ".closeIcon", function () {
  $(this).parent().slideUp();
});
$(function () {
  var scrollTop = 0;
  $(document).on("click", ".js-modal-open", function () {
    scrollTop = $(window).scrollTop();
    $("body").css({
      position: "fixed",
      top: -1 * scrollTop
    });
    var target = $(this).data("target");
    var modal = document.getElementById(target);
    $(modal).fadeIn();
    $(modal).find(".modal__content").scrollTop(0);
    return false;
  });
  $(".js-modal-close").on("click", function () {
    $("body").css({
      position: "static"
    });
    $("html, body").prop({
      scrollTop: scrollTop
    });
    $(".js-modal").fadeOut();
    return false;
  });
});
$(function () {
  function mousedragscrollable(element) {
    var target; // 蜍輔°縺吝ｯｾ雎｡

    $(element).each(function (i, e) {
      $(e).mousedown(function (event) {
        event.preventDefault();
        target = $(e); // 蜍輔°縺吝ｯｾ雎｡

        $(e).data({
          down: true,
          move: false,
          x: event.clientX,
          y: event.clientY,
          scrollleft: $(e).scrollLeft(),
          scrolltop: $(e).scrollTop()
        });
        return false;
      }); // move蠕後�link辟｡蜉ｹ

      $(e).click(function (event) {
        if ($(e).data("move")) {
          return false;
        }
      });
    }); // list隕∫ｴ�蜀�/螟悶〒縺ｮevent

    $(document).mousemove(function (event) {
      if ($(target).data("down")) {
        event.preventDefault();
        var move_x = $(target).data("x") - event.clientX;
        var move_y = $(target).data("y") - event.clientY;

        if (move_x !== 0 || move_y !== 0) {
          $(target).data("move", true);
        } else {
          return;
        }

        $(target).scrollLeft($(target).data("scrollleft") + move_x);
        $(target).scrollTop($(target).data("scrolltop") + move_y);
        return false;
      }
    }).mouseup(function (event) {
      $(target).data("down", false);
      return false;
    });
  }

  mousedragscrollable(".dragTable");
});
$(function () {
  var tabs = $(".tab");
  $(".tab").on("click", function () {
    $(".tabstay").removeClass("tabstay");
    $(this).addClass("tabstay");
    var index = tabs.index(this);
    $(".customList,.userList").removeClass("show").eq(index).addClass("show");
  });
});
$(function () {
  var navigationOpenFlag = false;
  var navButtonFlag = true;
  var focusFlag = false; //繝上Φ繝舌�繧ｬ繝ｼ繝｡繝九Η繝ｼ

  $(function () {
    $(document).on("click", ".el_news", function () {
      if (navButtonFlag) {
        spNavInOut["switch"]();
        setTimeout(function () {
          navButtonFlag = true;
        }, 200);
        navButtonFlag = false;
      }
    });
    $(document).on("click touchend", function (event) {
      if (!$(event.target).closest("#news,.el_news").length && $("body").hasClass("js_newsOpen") && focusFlag) {
        focusFlag = false;
        spNavInOut["switch"]();
      }
    });
  });

  function spNavIn() {
    $("body").removeClass("js_newsClose");
    $("body").addClass("js_newsOpen");
    setTimeout(function () {
      focusFlag = true;
    }, 200);
    setTimeout(function () {
      navigationOpenFlag = true;
    }, 200);
  }

  function spNavOut() {
    $("body").removeClass("js_newsOpen");
    $("body").addClass("js_newsClose");
    setTimeout(function () {
      $(".uq_spNavi").removeClass("js_appear");
      focusFlag = false;
    }, 200);
    navigationOpenFlag = false;
  }

  var spNavInOut = {
    "switch": function _switch() {
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

/***/ }),

/***/ 15:
/*!***************************************************!*\
  !*** multi ./resources/assets/admin/js/common.js ***!
  \***************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! /Users/takaryon/Documents/vm/hakken-system/data/asp/resources/assets/admin/js/common.js */"./resources/assets/admin/js/common.js");


/***/ })

},[[15,"/js/manifest"]]]);