(window["webpackJsonp"] = window["webpackJsonp"] || []).push([["/staff/js/document-setting_row"],{

/***/ "./resources/assets/staff/js/document-setting_row.js":
/*!***********************************************************!*\
  !*** ./resources/assets/staff/js/document-setting_row.js ***!
  \***********************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _slicedToArray(arr, i) {
  return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest();
}

function _nonIterableRest() {
  throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.");
}

function _unsupportedIterableToArray(o, minLen) {
  if (!o) return;
  if (typeof o === "string") return _arrayLikeToArray(o, minLen);
  var n = Object.prototype.toString.call(o).slice(8, -1);
  if (n === "Object" && o.constructor) n = o.constructor.name;
  if (n === "Map" || n === "Set") return Array.from(o);
  if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen);
}

function _arrayLikeToArray(arr, len) {
  if (len == null || len > arr.length) len = arr.length;

  for (var i = 0, arr2 = new Array(len); i < len; i++) {
    arr2[i] = arr[i];
  }

  return arr2;
}

function _iterableToArrayLimit(arr, i) {
  if (typeof Symbol === "undefined" || !(Symbol.iterator in Object(arr))) return;
  var _arr = [];
  var _n = true;
  var _d = false;
  var _e = undefined;

  try {
    for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) {
      _arr.push(_s.value);

      if (i && _arr.length === i) break;
    }
  } catch (err) {
    _d = true;
    _e = err;
  } finally {
    try {
      if (!_n && _i["return"] != null) _i["return"]();
    } finally {
      if (_d) throw _e;
    }
  }

  return _arr;
}

function _arrayWithHoles(arr) {
  if (Array.isArray(arr)) return arr;
}

$(function () {
  $('input[name^="setting"]').on("change", function () {
    var val = $(this).val();

    var _val$split = val.split("_"),
        _val$split2 = _slicedToArray(_val$split, 2),
        parent = _val$split2[0],
        child = _val$split2[1];

    if ($(this).prop("checked")) {
      if (child) {
        // 子項目の場合は親項目のチェックもON
        $("[value=\"".concat(parent, "\"]")).prop("checked", true);
      }
    } else {
      if (!child) {
        // 親項目の場合は子項目のチェックもOFF
        $("[value^=\"".concat(parent, "_\"]")).prop("checked", false);
      }
    }
  });
});

/***/ }),

/***/ 30:
/*!*****************************************************************!*\
  !*** multi ./resources/assets/staff/js/document-setting_row.js ***!
  \*****************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! /Users/takaryon/Documents/vm/hakken-system/data/asp/resources/assets/staff/js/document-setting_row.js */"./resources/assets/staff/js/document-setting_row.js");


/***/ })

},[[30,"/js/manifest"]]]);