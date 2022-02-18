(window["webpackJsonp"] = window["webpackJsonp"] || []).push([["/admin/js/interests-edit"],{

/***/ "./resources/assets/admin/js/interests-edit.js":
/*!*****************************************************!*\
  !*** ./resources/assets/admin/js/interests-edit.js ***!
  \*****************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

//更新
$(function () {
  $("#updateButton").on("click", function (e) {
    e.preventDefault();
    $("#updateForm").trigger("submit");
  });
}); // 削除

$(function () {
  $("#deleteButton").on("click", function (e) {
    e.preventDefault();
    $("#deleteForm").trigger("submit");
  });
});

/***/ }),

/***/ 6:
/*!***********************************************************!*\
  !*** multi ./resources/assets/admin/js/interests-edit.js ***!
  \***********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! /Users/takaryon/Documents/vm/hakken-system/data/asp/resources/assets/admin/js/interests-edit.js */"./resources/assets/admin/js/interests-edit.js");


/***/ })

},[[6,"/js/manifest"]]]);