(window["webpackJsonp"] = window["webpackJsonp"] || []).push([["/admin/js/staffs-edit"],{

/***/ "./resources/assets/admin/js/staffs-edit.js":
/*!**************************************************!*\
  !*** ./resources/assets/admin/js/staffs-edit.js ***!
  \**************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

// パスワード表示・非表示
var toggleShowPasswordText = function toggleShowPasswordText() {
  $("#showPassword").text($("#password").attr("type") === "password" ? "表示" : "非表示");
};

$(function () {
  toggleShowPasswordText();
  $("#showPassword").on("click", function (e) {
    e.preventDefault();
    $("#password").attr("type", $("#password").attr("type") === "password" ? "text" : "password");
    toggleShowPasswordText();
  });
}); //更新

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

/***/ 7:
/*!********************************************************!*\
  !*** multi ./resources/assets/admin/js/staffs-edit.js ***!
  \********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! /Users/takaryon/Documents/vm/hakken-system/data/asp/resources/assets/admin/js/staffs-edit.js */"./resources/assets/admin/js/staffs-edit.js");


/***/ })

},[[7,"/js/manifest"]]]);