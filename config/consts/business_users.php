<?php

return [
 // 顧客詳細ページのタブ
 'DEFAULT_TAB' => 'customer', // デフォルト選択タブ
 'TAB_CUSTOMER_INFO' => 'customer',
 'TAB_USAGE_HISTORY' => 'history',
 'TAB_CONSULTATION' => 'consultation',
 'TAB_LIST' => [
   'tab_customer_info' => 'customer',
   'tab_usage_history' => 'history',
   'tab_consultation' => 'consultation',
 ],
  // 一括支払契約
  'DEFAULT_PAY_ALTOGETHER' => 2, // 一括支払契約デフォルト値
  'PAY_ALTOGETHER_YES' => 1, // あり
  'PAY_ALTOGETHER_NO' => 2, // なし
  'PAY_ALTOGETHER_LIST' => [
    'pay_altogether_yes' => 1,
    'pay_altogether_no' => 2,
  ],
  'STATUS_VALID' => 1, // 有効
  'STATUS_SUSPEND' => 5, // 無効（ログイン不可）
  'STATUS_LIST' => [
    'status_valid' => 1,
    'status_suspend' => 5,
  ],
];
