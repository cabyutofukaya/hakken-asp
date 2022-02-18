<?php

return [
  'DISPLAY_BLOCK' => "display_block", 
  'RESERVATION_INFO' => "reservation_info",
  'AIR_TICKET_INFO' => "air_ticket_info",
  'BREAKDOWN_PRICE' => "breakdown_price",

  'DISPLAY_BLOCK_LIST' => [
    '宛名' => [],
    '自社情報' => [],
    '予約情報(件名・期間・参加者)' => [],
    '航空券情報' => [],
    'ホテル情報' => [],
    'ホテル連絡先' => [],
    '代金内訳' => [],
    '案内文' => [],
    '備考' => [],
    '検印欄' => [], // 帳票設定ページでは未使用
  ],
  'RESERVATION_INFO_LIST' => [
    '件名' => [],
    '期間' => [],
    '代表者' => ['代表者(敬称)',], // 親子関係
    // '代表者(ローマ字)' => ['Mr/Ms',], // 親子関係
    '参加者' => ['参加者(敬称)',], // 親子関係
    // '参加者(ローマ字)' => ['Mr/Ms',], // 親子関係
  ],
  'AIR_TICKET_INFO_LIST' => [
    '座席・クラス' => [],
    '航空会社' => [],
    'REF番号' => [],
  ],
  'BREAKDOWN_PRICE_LIST' => [
    '単価・金額' => [],
    '消費税' => ['非課税/不課税',],
  ],
];