<?php

return [
  // 追加・削除不可のテンプレートコード一覧
  'NO_ADD_OR_DELETE_CODE_LIST' => [
    "quote_default", // 見積デフォルト（document_categoriesの設定値）
    "reserve_confirm_default", // 予約確認書デフォルト（document_categoriesの設定値）
  ],

  // 予約確認書ステータス
  'STATUS_DEFAULT' => 2,
  'STATUS_ISSUED' => 1, // 発行済み
  'STATUS_UNISSUED' => 2, // 未発行
  'STATUS_LIST' => [
    'status_issued' => 1, // 発行済み
    'status_unissued' => 2, // 未発行
  ],
];