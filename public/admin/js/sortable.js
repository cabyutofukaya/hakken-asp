(window["webpackJsonp"] = window["webpackJsonp"] || []).push([["/admin/js/sortable"],{

/***/ "./resources/assets/admin/js/sortable.js":
/*!***********************************************!*\
  !*** ./resources/assets/admin/js/sortable.js ***!
  \***********************************************/
/*! no static exports found */
/***/ (function(module, exports) {

$(function () {
  $("[data-sort]").on("click", function () {
    var sort = $(this).data("sort");
    var url = new URL(location);
    var dp = url.searchParams.get("direction");
    url.searchParams.set("sort", sort);
    url.searchParams.set("direction", dp === "asc" ? "desc" : "asc");
    location.href = url.toString();
  });
});

/***/ }),

/***/ 14:
/*!*****************************************************!*\
  !*** multi ./resources/assets/admin/js/sortable.js ***!
  \*****************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! /Users/takaryon/Documents/vm/hakken-system/data/asp/resources/assets/admin/js/sortable.js */"./resources/assets/admin/js/sortable.js");


/***/ })

},[[14,"/js/manifest"]]]);