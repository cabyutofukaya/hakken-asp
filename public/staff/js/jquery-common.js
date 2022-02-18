(window["webpackJsonp"] = window["webpackJsonp"] || []).push([["/staff/js/jquery-common"],{

/***/ "./resources/assets/staff/js/common.js":
/*!*********************************************!*\
  !*** ./resources/assets/staff/js/common.js ***!
  \*********************************************/
/*! no static exports found */
/***/ (function(module, exports) {

axios.interceptors.response.use(function (response) {
  // 成功時の処理
  return response;
}, function (error) {
  if (error.response) {
    if (error.response.status === 422) {
      var msg = [];

      for (var key in error.response.data.errors) {
        msg.push(error.response.data.errors[key][0]);
      }

      alert(msg.join("\n"));
    } else if (error.response.status === 403) {
      alert("閲覧権限エラーです(403)");
    } else if (error.response.status === 401) {
      alert("長時間操作がなかったためセッションが切れた可能性がります。\n再度ログインお願いします。");
      location.href = "/agency1/login";
    } else {
      alert(error.response.status);
    }
  } else if (error.request) {
    alert(error.request);
  } else {
    alert("Error", error.message);
  }
});
$(".current").on("click", function () {
  if ($(this).hasClass("active")) {
    $(this).removeClass("active");
  } else {
    $(this).addClass("active");
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
$(".sort").on("click", function () {
  $(this).toggleClass("active");
});
$(document).on("click", ".closeIcon", function () {
  $(this).parent().slideUp();
});
$(function () {
  $(document).on("click", ".js-modal-open", function () {
    var target = $(this).data("target");
    var modal = document.getElementById(target);
    $(modal).fadeIn();
    return false;
  });
  $(".js-modal-close").on("click", function () {
    $(".js-modal").fadeOut();
    return false;
  });
});
$(function () {
  function mousedragscrollable(element) {
    var target; // 動かす対象

    $(element).each(function (i, e) {
      $(e).mousedown(function (event) {
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
      }); // move後のlink無効

      $(e).click(function (event) {
        if ($(e).data("move")) {
          return false;
        }
      });
    }); // list要素内/外でのevent

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
    $(".stay").removeClass("stay");
    $(this).addClass("stay");
    var index = tabs.index(this);
    $(".customList").removeClass("show").eq(index).addClass("show");
  });
});

/***/ }),

/***/ 13:
/*!***************************************************!*\
  !*** multi ./resources/assets/staff/js/common.js ***!
  \***************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! /Users/takaryon/Documents/vm/hakken-system/data/asp/resources/assets/staff/js/common.js */"./resources/assets/staff/js/common.js");


/***/ })

},[[13,"/js/manifest"]]]);