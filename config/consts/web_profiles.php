<?php
include("_person.php");

return array_merge($common, [
  'PHOTO_KIND_PROFILE' => 'profile', // プロフィール
  'PHOTO_KIND_COVER' => 'cover', // カバー
  'PHOTO_KIND_LIST' => [
    'photo_kind_profile' => 'profile',
    'photo_kind_cover' => 'cover',
  ],
  'VALUE_DELIMITER' => ',', // 値を区切る文字列
]);