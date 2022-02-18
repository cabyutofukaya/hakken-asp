(window["webpackJsonp"] = window["webpackJsonp"] || []).push([["/staff/js/header"],{

/***/ "./resources/assets/staff/js/header.js":
/*!*********************************************!*\
  !*** ./resources/assets/staff/js/header.js ***!
  \*********************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _objectDestructuringEmpty(obj) {
  if (obj == null) throw new TypeError("Cannot destructure undefined");
}

var HeaderArea = function HeaderArea(_ref) {
  _objectDestructuringEmpty(_ref);

  var _useContext = useContext(ConstContext),
      agencyAccount = _useContext.agencyAccount;

  return /*#__PURE__*/React.createElement(React.Fragment, null, "news");
}; // 入力画面


var Element = document.getElementById("headerArea");

if (Element) {
  var jsVars = Element.getAttribute("jsVars");
  var parsedJsVars = jsVars && JSON.parse(jsVars);
  render( /*#__PURE__*/React.createElement(ConstApp, {
    jsVars: parsedJsVars
  }, /*#__PURE__*/React.createElement(HeaderArea, null)), document.getElementById("headerArea"));
}

/***/ }),

/***/ 19:
/*!***************************************************!*\
  !*** multi ./resources/assets/staff/js/header.js ***!
  \***************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! /Users/takaryon/Documents/vm/hakken-system/data/asp/resources/assets/staff/js/header.js */"./resources/assets/staff/js/header.js");


/***/ })

},[[19,"/js/manifest"]]]);