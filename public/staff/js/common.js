(window["webpackJsonp"] = window["webpackJsonp"] || []).push([["/staff/js/common"],{

/***/ "./resources/assets/staff/js/common.js":
/*!*********************************************!*\
  !*** ./resources/assets/staff/js/common.js ***!
  \*********************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _libs__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./libs */ "./resources/assets/staff/js/libs.js");
 // axiosのエラーハンドリング

axios.interceptors.response.use(function (response) {
  // 成功時の処理
  return response;
}, function (error) {
  if (error !== null && error !== void 0 && error.response) {
    var _error$response, _error$response2, _error$response4, _error$response6, _error$response8, _error$response9;

    if (((_error$response = error.response) === null || _error$response === void 0 ? void 0 : _error$response.status) === 422) {
      var msg = [];

      for (var key in error.response.data.errors) {
        msg.push(error.response.data.errors[key][0]);
      }

      alert(msg.join("\n"));
    } else if ((error === null || error === void 0 ? void 0 : (_error$response2 = error.response) === null || _error$response2 === void 0 ? void 0 : _error$response2.status) === 403) {
      var _error$response3, _error$response3$data;

      alert((_error$response3 = error.response) !== null && _error$response3 !== void 0 && (_error$response3$data = _error$response3.data) !== null && _error$response3$data !== void 0 && _error$response3$data.message ? error.response.data.message : "許可されていなリクエストです(403)");
    } else if ((error === null || error === void 0 ? void 0 : (_error$response4 = error.response) === null || _error$response4 === void 0 ? void 0 : _error$response4.status) === 404) {
      var _error$response5, _error$response5$data;

      console.log(error.response);
      alert((_error$response5 = error.response) !== null && _error$response5 !== void 0 && (_error$response5$data = _error$response5.data) !== null && _error$response5$data !== void 0 && _error$response5$data.message ? error.response.data.message : "存在しないリソースです(404)");
    } else if ((error === null || error === void 0 ? void 0 : (_error$response6 = error.response) === null || _error$response6 === void 0 ? void 0 : _error$response6.status) === 409) {
      var _error$response7, _error$response7$data, _error$response$data;

      alert((_error$response7 = error.response) !== null && _error$response7 !== void 0 && (_error$response7$data = _error$response7.data) !== null && _error$response7$data !== void 0 && _error$response7$data.message ? (_error$response$data = error.response.data) === null || _error$response$data === void 0 ? void 0 : _error$response$data.message : "リソース競合エラーです(409)");
    } else if ((error === null || error === void 0 ? void 0 : (_error$response8 = error.response) === null || _error$response8 === void 0 ? void 0 : _error$response8.status) === 401) {
      alert("長時間操作がなかったためセッションが切れた可能性がります。\n再度ログインお願いします。");
      var agencyAccount = Object(_libs__WEBPACK_IMPORTED_MODULE_0__["getAgencyAccountFromUrl"])();

      if (agencyAccount) {
        // pathParts[0]は会社アカウント
        location.href = "/".concat(agencyAccount, "/login");
      }
    } else if ((error === null || error === void 0 ? void 0 : (_error$response9 = error.response) === null || _error$response9 === void 0 ? void 0 : _error$response9.status) === 500) {
      var _error$response$data2;

      alert((_error$response$data2 = error.response.data) !== null && _error$response$data2 !== void 0 && _error$response$data2.message ? error.response.data.message : "サーバーエラーです(500)。サーバー管理者にお問い合わせください");
    } else {
      var _error$response10, _error$response10$dat, _error$response$data3, _error$response11;

      alert(error !== null && error !== void 0 && (_error$response10 = error.response) !== null && _error$response10 !== void 0 && (_error$response10$dat = _error$response10.data) !== null && _error$response10$dat !== void 0 && _error$response10$dat.message ? (_error$response$data3 = error.response.data) === null || _error$response$data3 === void 0 ? void 0 : _error$response$data3.message : error === null || error === void 0 ? void 0 : (_error$response11 = error.response) === null || _error$response11 === void 0 ? void 0 : _error$response11.status);
    }
  } else if (error.request) {
    //  Request aborted など
    console.log(error);
  } else {
    console.log(error);
    alert("Error: ", error.message);
  }
}); // resetボタン

$('[type="reset"]').on("click", function (e) {
  e.preventDefault();
  $(this.form).find("textarea, :text, select, [type='email']").val("").end().find(":checked").prop("checked", false);
}); // 検索オプションを初期状態で開いておくか否かのパラメータ

$(".toggleOption").on("click", function () {
  if ($(this).hasClass("active")) {
    $("[name='search_option_open']").val("");
  } else {
    $("[name='search_option_open']").val(1);
  }
}); // submitの二重送信防止

$(".doubleBan").on("click", function () {
  $(this).css("pointer-events", "none");
});
$(".current").on("click", function () {
  if ($(this).hasClass("active")) {// $(this).removeClass("active"); 閉じるアクション不要かも、ということで一旦コメント
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
      // position: "fixed",
      top: -1 * scrollTop
    });
    var target = $(this).data("target");
    var modal = document.getElementById(target);
    $(modal).fadeIn();
    $(modal).find(".modal__content").scrollTop(0); // scroll位置をリセットしておく

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
    $(".tabstay").removeClass("tabstay");
    $(this).addClass("tabstay");
    var index = tabs.index(this);
    $(".customList,.userList").removeClass("show").eq(index).addClass("show");
  });
});
$(function () {
  var navigationOpenFlag = false;
  var navButtonFlag = true;
  var focusFlag = false; //ハンバーガーメニュー

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

/***/ "./resources/assets/staff/js/libs.js":
/*!*******************************************!*\
  !*** ./resources/assets/staff/js/libs.js ***!
  \*******************************************/
/*! exports provided: calcTaxInclud, calcNet, calcGrossProfit, calcProfitRate, getAgencyAccountFromUrl, getParam, getNameExObj, getPathFromBracketName */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "calcTaxInclud", function() { return calcTaxInclud; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "calcNet", function() { return calcNet; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "calcGrossProfit", function() { return calcGrossProfit; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "calcProfitRate", function() { return calcProfitRate; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "getAgencyAccountFromUrl", function() { return getAgencyAccountFromUrl; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "getParam", function() { return getParam; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "getNameExObj", function() { return getNameExObj; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "getPathFromBracketName", function() { return getPathFromBracketName; });
/**
 * 税込価格を計算
 * 小数点以下は四捨五入
 * @param {*} nonTaxed 税抜き価格
 * @param {*} taxRate 税率
 */
function calcTaxInclud(nonTaxed, taxRate) {
  if (/^[-]?\d*$/.test(nonTaxed) && /^[-]?\d*$/.test(taxRate)) {
    // 引数がともに数字であれば処理
    var tax = 1 + Number(taxRate) / 100;
    var num = Number(nonTaxed) * tax;
    return Math.round(num);
  }

  return nonTaxed;
}
/**
 * Net単価を計算
 * 小数点以下は四捨五入
 * @param {*} cost 仕入
 * @param {*} commissionRate 手数料率
 */

function calcNet(cost, commissionRate) {
  if (/^[-]?\d*$/.test(cost) && /^[-]?\d*$/.test(commissionRate)) {
    // 引数がともに数字であれば処理
    var rate = Number(commissionRate) / 100;
    var num = Number(cost) * rate;
    return cost - Math.round(num);
  }

  return cost;
}
/**
 * 粗利を計算
 * 小数点以下は四捨五入でOK（確認済）
 * @param {*} gross 税込単価
 * @param {*} net NET単価
 */

function calcGrossProfit(gross, net) {
  if (/^[-]?\d*$/.test(gross) && /^[-]?\d*$/.test(net)) {
    // 引数がともに数字であれば処理
    return Number(gross) - Number(net);
  }

  return gross;
}
/**
 * 利益率を計算
 *
 * @param {*} profit
 * @param {*} Sales
 */

function calcProfitRate(profit, sales) {
  if (sales === 0) return 0;
  return profit / sales * 100;
}
/**
 * URLから会社アカウントを取得
 *
 * @returns
 */

function getAgencyAccountFromUrl() {
  var path = location.pathname.replace(/^\/+|\/+$/g, ""); // pathnameの前後のスラッシュを削除

  var arr = path.split("/");
  return arr[0] ? arr[0] : null;
}
/**
 * GETパラメータを取得
 *
 * @param {*} name
 * @param {*} url
 * @returns
 */

function getParam(name, url) {
  if (!url) url = window.location.href;
  name = name.replace(/[\[\]]/g, "\\$&");
  var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
      results = regex.exec(url);
  if (!results) return null;
  if (!results[2]) return "";
  return decodeURIComponent(results[2].replace(/\+/g, " "));
} // name_exのjsonをobjectに変換

function getNameExObj(str) {
  try {
    var obj = JSON.parse(str);
    return {
      label: "".concat(obj.code, " ").concat(obj.name),
      value: obj.id
    };
  } catch (error) {}

  return {
    label: "",
    value: ""
  };
}
/**
 * ブラケットの配列表記をドットに直してパス文字列を作成
 * 前後の[]をトリムして、 ][ と [ 、 ] をドットに置換
 *
 * aaa[bbb][ccc] → aaa.bbb.cccs
 */

function getPathFromBracketName(name) {
  return name.replace(/^\[|\]$/, "").replace(/\]\[/g, ".").replace(/\[|\]/g, ".");
}

/***/ }),

/***/ 18:
/*!***************************************************!*\
  !*** multi ./resources/assets/staff/js/common.js ***!
  \***************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! /Users/takaryon/Documents/vm/hakken-system/data/asp/resources/assets/staff/js/common.js */"./resources/assets/staff/js/common.js");


/***/ })

},[[18,"/js/manifest"]]]);