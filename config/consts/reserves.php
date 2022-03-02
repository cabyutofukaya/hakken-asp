<?php

return [
  'ESTIMATE_DEFAULT_STATUS' => '見積', // 見積のデフォルト値（CODE_APPLICATION_ESTIMATE_STATUS_DEFAULT_LIST にある値から設定）
  'RESERVE_DEFAULT_STATUS' => '手配中', // 予約のデフォルト値（CODE_APPLICATION_RESERVE_STATUS_DEFAULT_LIST にある値から設定）
  'RESERVE_CANCEL_STATUS' => 'キャンセル', // キャンセル値（CODE_APPLICATION_RESERVE_STATUS_DEFAULT_LIST にある値から設定）
  // 状態(reservesのスコープに使用)
  'APPLICATION_STEP_CONSULT' => 'consult', // 相談
  'APPLICATION_STEP_DRAFT' => 'normal', // 見積
  'APPLICATION_STEP_RESERVE' => 'reserve', // 予約
  'APPLICATION_STEP_DEPARTED' => 'departed', // 催行済み(orキャンセル)
   // 詳細ページのタブ
  'DEFAULT_TAB' => 'basic', // デフォルト選択タブ
  'TAB_BASIC_INFO' => 'basic',
  'TAB_RESERVE_DETAIL' => 'detail',
  'TAB_CONSULTATION' => 'consultation',
  // 'TAB_LIST' => [
  //   'tab_basic_info' => 'basic',
  //   'tab_reserve_detail' => 'detail',
  //   'tab_consultation' => 'consultation',
  // ],
  // 顧客区分
  'PARTICIPANT_TYPE_DEFAULT' => 'person', // 法人・個人選択デフォルト値
  'PARTICIPANT_TYPE_PERSON' => 'person', // 個人顧客
  'PARTICIPANT_TYPE_BUSINESS' => 'business', // 法人顧客
  'PARTICIPANT_TYPE_LIST' => [
    'participant_type_person' => 'person',
    'participant_type_business' => 'business',
  ],
  'RECEPTION_TYPE_ASP' => 1, // ASP受付
  'RECEPTION_TYPE_WEB' => 2, // HAKKEN受付
  'RECEPTION_TYPE_LIST' => [
    'reception_type_asp' => 1,
    'reception_type_web' => 2,
  ],
];