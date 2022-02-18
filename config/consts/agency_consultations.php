<?php

// 個人顧客相談等、相談レコードの共通値として使用
return [
  'DEFAULT_STATUS' => 1, // ステータスデフォルト値
  'DEFAULT_KIND' => 1, // 種別デフォルト値

  // 種別
  'TAXONOMY_RESERVE' => 'reserve', // 予約・見積相談
  'TAXONOMY_PERSON' => 'person', // 個人顧客相談
  'TAXONOMY_BUSINESS' => 'business', // 法人顧客相談
  'TAXONOMY_LIST' => [
    'taxonomy_reserve' => 'reserve',
    'taxonomy_person' => 'person',
    'taxonomy_business' => 'business',
  ],
  // 
  'STATUS_RECEPTION' => 1, // 受付
  'STATUS_RESPONDING' => 5, // 対応中
  'STATUS_COMPLETION' => 10, // 完了
  'STATUS_LIST' => [
    'status_reception' => 1,
    'status_responding' => 5,
    'status_completion' => 10,
  ],
  'KIND_ESTIMATE' => 1,
  'KIND_RESERVE' => 5,
  'KIND_QUESTION' => 10,
  'KIND_REQUEST' => 15,
  'KIND_MESSAGE' => 20,
  'KIND_OTHERS' => 25,
  'KIND_LIST' => [
    'kind_estimate' => 1,
    'kind_reserve' => 5,
    'kind_question' => 10,
    'kind_request' => 15,
    'kind_message' => 20,
    'kind_others' => 25,
    ]
];