<?php
include("_person.php");

return array_merge($common, [
  'STATUS_VALID' => 1, // 有効
  'STATUS_SUSPEND' => 0, // 無効（ログイン不可）
  'STATUS_LIST' => [
    'status_valid' => 1,
    'status_suspend' => 0,
  ],
]);