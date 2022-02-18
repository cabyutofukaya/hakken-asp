<?php
include("_person.php");

return array_merge($common, [
  // 顧客番号用接頭辞
  'USER_NUMBER_PREFIX_ASP' => 'P', // ASPユーザー
  'USER_NUMBER_PREFIX_WEB' => 'WP', // WEBユーザー
  // ユーザー区分(asp/web)
  'USER_KBN_ASP' => 'asp',
  'USER_KBN_WEB' => 'web',
  // 顧客詳細ページのタブ
  'DEFAULT_TAB' => 'customer',
  'TAB_CUSTOMER_INFO' => 'customer',
  'TAB_USAGE_HISTORY' => 'history',
  'TAB_CONSULTATION' => 'consultation',
  'TAB_LIST' => [
    'tab_customer_info' => 'customer',
    'tab_usage_history' => 'history',
    'tab_consultation' => 'consultation',
  ],
  'DEFAULT_PASSPORT_ISSUE_COUNTRY' => 'JP', // 旅券発行国デフォルト値
  'DEFAULT_CITIZENSHIP' => 'JP', // 国籍デフォルト値
  // DM
  'DEFAULT_DM' => '', // DMデフォルト値
  'DM_OK' => 1,
  'DM_NG' => 2,
  'DM_LIST' => [
    'dm_ok' => 1,
    'dm_ng' => 2,
  ],
  'STATUS_VALID' => 1, // 有効
  'STATUS_SUSPEND' => 0, // 無効
  'STATUS_LIST' => [
    'status_valid' => 1,
    'status_suspend' => 0,
  ],
]);