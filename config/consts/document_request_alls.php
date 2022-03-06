<?php

return [
  'DISPLAY_BLOCK' => "display_block", 
  'RESERVATION_INFO' => "reservation_info",
  'BREAKDOWN_PRICE' => "breakdown_price",

  'DISPLAY_BLOCK_LIST' => [
    '宛名' => [],
    '自社情報' => [],
    '予約情報(件名・期間・担当者)' => [],
    '代金内訳' => [],
    '案内文' => [],
    '振込先' => [],
    '備考' => [],
    '検印欄' => [], // 帳票設定ページでは未使用
  ],
  'RESERVATION_INFO_LIST' => [
    '件名' => [],
    '期間' => [],
    '御社担当' => ['御社担当(敬称)'],// 親子関係
  ],
  'BREAKDOWN_PRICE_LIST' => [
    '単価・金額' => [],
    '御社担当' => ['御社担当(敬称)'],// 親子関係
    '消費税' => ['非課税/不課税',],
  ],
];