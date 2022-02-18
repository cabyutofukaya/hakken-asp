<?php

return [
  // ステータス
  'STATUS_SUSPEND' => 0, // 無効
  'STATUS_MAIN_REGISTRATION' => 1, // 有効
  'STATUS_LIST' => [
    'status_suspend' => 0,
    'status_main_registration' => 1,
  ],
  // 業務範囲
  'BUSINESS_SCOPE_DOMESTIC' => 1, // 国内
  'BUSINESS_SCOPE_OVERSEAS' => 2, // 海外
  'BUSINESS_SCOPE_LIST' => [
    'business_scope_domestic' => 1,
    'business_scope_overseas' => 2,
  ],
  // 登録種別
  'REGISTRATION_TYPE_1' => 1, // 第一種
  'REGISTRATION_TYPE_2' => 2, // 第二種
  'REGISTRATION_TYPE_3' => 3, // 第三種
  'REGISTRATION_TYPE_4' => 4, // その他
  'REGISTRATION_TYPE_LIST' => [
    'registration_type_1' => 1,
    'registration_type_2' => 2,
    'registration_type_3' => 3,
    'registration_type_4' => 4,
  ],
  // 旅行業協会 
  'TRAVEL_AGENCY_ASSOCIATION_NONE' => 0,
  'TRAVEL_AGENCY_ASSOCIATION_NTA' => 1, // 日旅
  'TRAVEL_AGENCY_ASSOCIATION_ZENRYO' => 2,
  'TRAVEL_AGENCY_ASSOCIATION_LIST' => [
    'travel_agency_association_none' => 0,
    'travel_agency_association_nta' => 1,
    'travel_agency_association_zenryo' => 2,
  ],
];