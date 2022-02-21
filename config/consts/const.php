<?php

return [
  'LOGOUT_RESPONSE_STATUS' => 999, // ログアウト時のResponseステータス
  'NUMBER_STAFF_ALLOWED_DEFAULT' => 5, // スタッフ登録可能数デフォルト値
  'NUMBER_STAFF_ALLOWED_MAX' => 20, // スタッフ登録可能数最大値(Web相談のリクエスト番号生成時にスタッフ数に依存した処理を書いているのでMaxは99人が上限)
  'TRIAL_PERIOD' => 30, // トライアル期間（日）
  'AGENCY_CONTRACT_PERIOD_DEFAULT' => 12, // 旅行会社契約期間デフォルト（月）
  'AGENCY_CONTRACT_EFFECTIVE_MARGIN' => 60, // 契約期間が切れたあと実際にログイン停止にするまでの余白時間（分）。契約更新プログラムの処理に時間がかる可能性があるため1時間程度を目安に設定
  'END_OF_MONTH' => 32, // 月末を表す定数

  'DEPARTED_QUERY' => 'departed', // 催行済GETパラメータ

  // Web相談の応札上限
  'WEB_CONSULT_MAX_UNDERTAKE' => 5,

  'RECEPTION_TYPE_ASP' => 'asp',
  'RECEPTION_TYPE_WEB' => 'web',

  // 画像サムネイルサイズ
  'THUMB_M' => 600,
  'THUMB_S' => 150,
  // アップロード画像ディレクトリ
  'UPLOAD_IMAGE_DIR' => 'image/',
  'UPLOAD_THUMB_M_DIR' => '_thumb_m/',
  'UPLOAD_THUMB_S_DIR' => '_thumb_s/',
  // 一般公開用PDFディレクトリ
  'UPLOAD_PDF_DIR' => 'pdf/',
  // pdfアップロードディレクトリ（dataディレクトリ内のものは基本的にはprivateアクセス）
  'UPLOAD_PRIVATE_PDF_DIR' => 'data/pdf/',

  'QUOTE_SEAL_MAXIMUM' => 4, // 検印欄表示数(旅行会社ページの帳票設定)
  'REQUEST_SEAL_MAXIMUM' => 4, // 検印欄表示数(旅行会社ページの請求書設定)
  'REQUEST_ALL_SEAL_MAXIMUM' => 4, // 検印欄表示数(旅行会社ページの一括請求書設定)

  // 管理画面用
  'BANK_CSV_UPLOAD_FIELDS' => 5, // 銀行データCSVアップロードフィールド数
  'DIRECTION_CSV_UPLOAD_FIELDS' => 5, // 方面マスタCSVアップロードフィールド数
  'AREA_CSV_UPLOAD_FIELDS' => 5, // 国・地域マスタCSVアップロードフィールド数
  
  'MASTER_AGENCY_ID' => 0, // v_directionsテーブルに設定するマスターレコード用のagency_id
];