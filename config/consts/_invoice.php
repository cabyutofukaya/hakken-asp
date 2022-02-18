<?php

//　請求ステータス共通（請求・一括請求、請求管理一覧ページ等で使用）
$common = [
  'STATUS_DEFAULT' => 2,
  'STATUS_BILLED' => 1,
  'STATUS_UNCLAIMED' => 2,
  'STATUS_LIST' => [
    'status_billed' => 1,
    'status_unclaimed' => 2,
  ],
  // 入金(と一括入金)登録API実行時に返却リストの種類を指定するのに使用
  'LIST_TYPE_BREAKDOWN' => 'breakdown',
  'LIST_TYPE_INDEX' => 'index',
  'LIST_TYPE_LIST' => [
    'list_type_breakdown' => 'breakdown',
    'list_type_index' => 'index',
  ],
];