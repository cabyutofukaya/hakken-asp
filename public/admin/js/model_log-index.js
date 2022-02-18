(window["webpackJsonp"] = window["webpackJsonp"] || []).push([["/admin/js/model_log-index"],{

/***/ "./resources/assets/admin/js/model_log-index.js":
/*!******************************************************!*\
  !*** ./resources/assets/admin/js/model_log-index.js ***!
  \******************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

$(function () {
  $(".show-detail").on("click", function (e) {
    var data = $(this).data("json");
    $("#modelTxt").text("".concat(data.model, " #").concat(data.model_id));
    $("#roleTxt").text("".concat(data.guard, " #").concat(data.user_id));
    $("#operationTxt").text(data.operation_type);
    $("#messageTxt").html(JSON.stringify(JSON.parse(data.message), null, "\t"));
    $("#createdAtTxt").text(data.created_at);
  });
});

/***/ }),

/***/ 10:
/*!************************************************************!*\
  !*** multi ./resources/assets/admin/js/model_log-index.js ***!
  \************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! /Users/takaryon/Documents/vm/hakken-system/data/asp/resources/assets/admin/js/model_log-index.js */"./resources/assets/admin/js/model_log-index.js");


/***/ })

},[[10,"/js/manifest"]]]);