(window["webpackJsonp"] = window["webpackJsonp"] || []).push([["/staff/js/mail_template-edit"],{

/***/ "./resources/assets/staff/js/mail_template-edit.js":
/*!*********************************************************!*\
  !*** ./resources/assets/staff/js/mail_template-edit.js ***!
  \*********************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

// オプションのcheckboxが1つも選択されていない場合、同グループの独自タグは非活性に
function toggleTagButton(tagGroup) {
  $("[data-has_child='".concat(tagGroup, "']")).prop("disabled", $("input:checkbox[data-tag_group=\"".concat(tagGroup, "\"]:checked")).length === 0);
}

$(function () {
  // 独自タグボタン押下でtextareaに独自タグ挿入
  $("[data-tag]").on("click", function (e) {
    e.preventDefault();
    var tags = [];

    if ($(this).data("has_child")) {
      // オプションタグがある場合は（申込者に対する「漢字」など）チェック済みタグをtags配列に詰める
      var group = $(this).data("has_child");
      $("input:checkbox[data-tag_group=\"".concat(group, "\"]:checked")).each(function () {
        tags.push($(this).val());
      });
    }

    var val = $(this).data("tag");
    tags.unshift(val); // オプションタグとメインタグをtags配列に詰める

    var tag = "{%" + tags.join(",") + "%}"; // タグ配列を文字列に変換

    var textarea = document.querySelector("textarea[name='body']");
    var sentence = textarea.value;
    var len = sentence.length;
    var pos = textarea.selectionStart;
    var before = sentence.substr(0, pos);
    var after = sentence.substr(pos, len);
    sentence = before + tag + after;
    textarea.value = sentence;
  });
});
$(function () {
  // 初回アクセス時、オプションのチェックボックスのチェック状態をチェックし、同グループのタグボタンの活性化・非活性化
  $("[data-tag_group]").each(function () {
    toggleTagButton($(this).data("tag_group"));
  }); // オプションのチェックボックスが変更されたら同グループのタグボタンの活性化・非活性化をチェック

  $("[data-tag_group]").on("change", function (e) {
    toggleTagButton($(this).data("tag_group"));
  });
});

/***/ }),

/***/ 35:
/*!***************************************************************!*\
  !*** multi ./resources/assets/staff/js/mail_template-edit.js ***!
  \***************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! /Users/takaryon/Documents/vm/hakken-system/data/asp/resources/assets/staff/js/mail_template-edit.js */"./resources/assets/staff/js/mail_template-edit.js");


/***/ })

},[[35,"/js/manifest"]]]);