(window["webpackJsonp"] = window["webpackJsonp"] || []).push([["/staff/js/user_custom_item-index"],{

/***/ "./resources/assets/staff/js/user_custom_item-index.js":
/*!*************************************************************!*\
  !*** ./resources/assets/staff/js/user_custom_item-index.js ***!
  \*************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

$(function () {
  // 有効・無効を切り替え
  $("[name='flg[]']").on("click", function () {
    $(this).prop("disabled", true); // 処理が終わるまで選択不可に

    var agencyAccount = $(this).data("agency_account");
    axios.post("/api/".concat(agencyAccount, "/toggleFlg"), {
      id: $(this).val(),
      flg: $(this).is(":checked"),
      _method: "put"
    }).then(function (res) {//
    })["catch"](function (error) {
      alert("有効・無効フラグの更新に失敗しました。");
    })["finally"](function () {
      $(this).prop("disabled", false);
    }.bind(this));
  });
});

/***/ }),

/***/ 25:
/*!*******************************************************************!*\
  !*** multi ./resources/assets/staff/js/user_custom_item-index.js ***!
  \*******************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! /Users/takaryon/Documents/vm/hakken-system/data/asp/resources/assets/staff/js/user_custom_item-index.js */"./resources/assets/staff/js/user_custom_item-index.js");


/***/ })

},[[25,"/js/manifest"]]]);