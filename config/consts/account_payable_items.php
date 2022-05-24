<?php

include("_account_payable.php");

return array_merge($common, [
  // item_payable_number生成に使用するカラム
  'ITEM_PAYABLE_NUMBER_COLUMNS' => [
    "reserve_itinerary_id",
    "supplier_id",
    "subject",
    "item_id",
  ],
  // item_payable_numberを生成する際の区切り文字
  'ITEM_PAYABLE_NUMBER_DELIMITER' => '-',
]);