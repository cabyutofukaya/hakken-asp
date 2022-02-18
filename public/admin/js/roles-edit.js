(window["webpackJsonp"] = window["webpackJsonp"] || []).push([["/admin/js/roles-edit"],{

/***/ "./resources/assets/admin/js/roles-edit.js":
/*!*************************************************!*\
  !*** ./resources/assets/admin/js/roles-edit.js ***!
  \*************************************************/
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

/***/ 8:
/*!*******************************************************!*\
  !*** multi ./resources/assets/admin/js/roles-edit.js ***!
  \*******************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! /Users/takaryon/Documents/vm/hakken-system/data/asp/resources/assets/admin/js/roles-edit.js */"./resources/assets/admin/js/roles-edit.js");


/***/ })

},[[8,"/js/manifest"]]]);