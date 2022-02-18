<?php

return [
  // 管理コード

  // ユーザー
  'CODE_STAFF_SHOZOKU' => "code_staff_shozoku", // ユーザー管理 > 所属
  // 個人顧客
  'CODE_USER_CUSTOMER_KBN' => "code_user_customer_kbn", //個人顧客 > 顧客区分
  'CODE_USER_CUSTOMER_RANK' => "code_user_customer_rank", //個人顧客 > ランク
  'CODE_USER_CUSTOMER_RECEPTIONIST' => "code_user_customer_receptionist", //個人顧客 > 受付担当
  'CODE_USER_CUSTOMER_AIRPLANE_COMPANY' => "code_user_customer_airplane_company", //個人顧客 > 航空会社
  // 法人顧客
  'CODE_BUSINESS_CUSTOMER_KBN' => "code_business_customer_kbn", //法人顧客 > 顧客区分
  'CODE_BUSINESS_CUSTOMER_RANK' => "code_business_customer_rank", //法人顧客 > ランク
  // 予約・見積
  'CODE_APPLICATION_TRAVEL_TYPE' => 'code_application_travel_type', // 予約/見積 > 旅行種別
  'CODE_APPLICATION_RESERVE_STATUS' => 'code_application_reserve_status', // 予約/見積 > 予約ステータス
  'CODE_APPLICATION_ESTIMATE_STATUS' => 'code_application_estimate_status', // 予約/見積 > 見積ステータス
  'CODE_APPLICATION_KBN' => 'code_application_kbn', // 予約/見積 > 区分
  'CODE_APPLICATION_TYPE' => 'code_application_type', // 予約/見積 > 申込種別
  'CODE_APPLICATION_CLASS' => 'code_application_class', // 予約/見積 > 分類
  'CODE_APPLICATION_APPLICATION_DATE' => 'code_application_application_date', // 予約/見積 > 申込日
  'CODE_APPLICATION_GUIDANCE_DEADLINE' => 'code_application_guidance_deadline', // 予約/見積 > 案内期限
  'CODE_APPLICATION_FNL_DATE' => 'code_application_fnl_date', // 予約/見積 > FNL日
  'CODE_APPLICATION_TICKETLIMIT' => 'code_application_ticketlimit', // 予約/見積 > ticketlimit
  // オプション科目
  'CODE_SUBJECT_OPTION_KBN' => 'code_subject_option_kbn', // 科目 > オプション科目 > 区分
  // ホテル科目
  'CODE_SUBJECT_HOTEL_KBN' => 'code_subject_hotel_kbn', // 科目 > ホテル科目 > 区分
  'CODE_SUBJECT_HOTEL_ROOM_TYPE' => 'code_subject_hotel_room_type', // 科目 > ホテル科目 > 部屋タイプ
  'CODE_SUBJECT_HOTEL_MEAL_TYPE' => 'code_subject_hotel_meal_type', // 科目 > ホテル科目 > 食事タイプ
  // 航空券科目
  'CODE_SUBJECT_AIRPLANE_COMPANY' => 'code_subject_airplane_company', // 科目 > 航空券科目 > 航空会社
  // 入出金管理
  'CODE_MANAGEMENT_WITHDRAWAL_METHOD' => "code_management_withdrawal_method", // 入出金管理 > 出金方法
  'CODE_MANAGEMENT_DEPOSIT_METHOD' => "code_management_deposit_method", // 入出金管理 > 入金方法


  // +αのカスタムリスト初期値
  'CODE_USER_CUSTOMER_AIRPLANE_COMPANY_DEFAULT_LIST' => ['SQ','JL','NH','AA','MU','HA','D7'], // 個人顧客 > 航空会社
  'CODE_APPLICATION_TRAVEL_TYPE_DEFAULT_LIST' => ['国内','海外','日本発着'], // 予約見積 > 旅行種別
  'CODE_APPLICATION_RESERVE_STATUS_DEFAULT_LIST' => ['問合せ','受付','手配中','手配完了','出発','出発後','CXL','CXL待ち','ペンディング','クレーム'], // 予約/見積 > 予約ステータス
  'CODE_APPLICATION_ESTIMATE_STATUS_DEFAULT_LIST' => ['見積','連絡待ち'], // 予約/見積 > 見積ステータス
  'CODE_APPLICATION_KBN_DEFAULT_LIST' => ['業務渡航','国内出張','バスツアー','国内ツアー','海外ツアー','個人','学会・教育','団体'], // 予約/見積 > 区分
  'CODE_APPLICATION_TYPE_DEFAULT_LIST' => ['TEL','FAX','来店'], // 予約/見積 > 申込種別
  'CODE_APPLICATION_CLASS_DEFAULT_LIST' => ['募集型企画','受注型企画','手配旅行','代売'], // 予約/見積 > 分類
  'CODE_SUBJECT_OPTION_KBN_DEFAULT_LIST' => ['国内パッケージツアー','手数料','その他','JR乗車券','高速代金','貸切バス','観覧料','保険','お食事代','食事代(お弁当)','TAX・燃油','運賃','わんこそば','ゴルフ代金','現地オプション','旅行代金','諸税','レンタカー','ホテルオプション'], // 科目 > オプション科目
  'CODE_SUBJECT_HOTEL_KBN_DEFAULT_LIST' => ['ASTON','ビジネスホテル'], // 科目 > ホテル科目 > 区分
  'CODE_SUBJECT_HOTEL_ROOM_TYPE_DEFAULT_LIST' => ['シングル','ツイン','ダブル','洋室','トリプル','和室','和洋室','4ベッド','スイート'], // 科目 > ホテル科目 > 部屋タイプ
  'CODE_SUBJECT_HOTEL_MEAL_TYPE_DEFAULT_LIST' => ['夕朝食付','朝食付','夕食付','食事なし'], // 科目 > ホテル科目 > 食事タイプ
  'CODE_SUBJECT_AIRPLANE_COMPANY_DEFAULT_LIST' => ['SQ','JL','NH','AA','MU','HA','D7'], // 科目 > 航空券科目 > 航空会社
  'CODE_MANAGEMENT_WITHDRAWAL_METHOD_DEFAULT_LIST' => ['銀行振込'], // 入出金管理 > 出金方法
  'CODE_MANAGEMENT_DEPOSIT_METHOD_DEFAULT_LIST' => ['銀行振込'], // 入出金管理 > 入金方法


  // 削除不可リスト(定数名の接尾辞が「PROTECT」)
  'CODE_APPLICATION_ESTIMATE_STATUS_DEFAULT_LIST_PROTECT' => ['見積','連絡待ち'], // 見積もりステータス
  'CODE_APPLICATION_RESERVE_STATUS_DEFAULT_LIST_PROTECT' => ['問合せ','受付','手配中','手配完了','出発','出発後','CXL','CXL待ち','ペンディング','クレーム'], // 予約ステータス
  'CODE_MANAGEMENT_WITHDRAWAL_METHOD_DEFAULT_LIST_PROTECT' => ['銀行振込'], // 入出金管理 > 出金方法
  'CODE_MANAGEMENT_DEPOSIT_METHOD_DEFAULT_LIST_PROTECT' => ['銀行振込'], // 入出金管理 > 入金方法
  'CODE_APPLICATION_TRAVEL_TYPE_DEFAULT_LIST_PROTECT' => ['国内','海外','日本発着'], // 予約見積 > 旅行種別



  // カスタム項目接頭辞
  'USER_CUSTOM_ITEM_PREFIX' => 'uci-', // 全項目共通
  'USER_CUSTOM_ITEM_ONELINE_PREFIX' => 'uci-oneline-', // 一行入力（変更する場合は USER_CUSTOM_ITEM_PREFIX も変更のこと）
  'USER_CUSTOM_ITEM_MULTIPLE_PREFIX' => 'uci-multiple-', // 複数行入力（変更する場合は USER_CUSTOM_ITEM_PREFIX も変更のこと）
  'USER_CUSTOM_ITEM_CALENDAR_PREFIX' => 'uci-calendar-', // カレンダー入力（変更する場合は USER_CUSTOM_ITEM_PREFIX も変更のこと）
  'USER_CUSTOM_ITEM_TIME_PREFIX' => 'uci-time-', // 時刻入力（変更する場合は USER_CUSTOM_ITEM_PREFIX も変更のこと）
  'USER_CUSTOM_ITEM_TEXT_PREFIX' => 'uci-text-', // 一般的なテキスト入力。リストタイプで使用（変更する場合は USER_CUSTOM_ITEM_PREFIX も変更のこと）

  // 入力形式（テキスト）
  'INPUT_TYPE_TEXT_01' => "oneline",
  'INPUT_TYPE_TEXT_02' => "multiple",
  'INPUT_TYPE_TEXT_LIST' => [
    'input_type_text_01' => "oneline",
    'input_type_text_02' => "multiple",
  ],

  // 入力形式（日時）
  'INPUT_TYPE_DATE_01' => "calendar",
  'INPUT_TYPE_DATE_02' => "time",
  'INPUT_TYPE_DATE_LIST' => [
    'input_type_date_01' => "calendar",
    'input_type_date_02' => "time",
  ],

  // カテゴリ項目
  // text->list->date という順にソートする場合があるので接頭辞に数字をつける
  'CUSTOM_ITEM_TYPE_TEXT' => "00_text",
  'CUSTOM_ITEM_TYPE_LIST' => "10_list",
  'CUSTOM_ITEM_TYPE_DATE' => "20_date",
  'CUSTOM_ITEM_LIST' => [
    'custom_item_type_text' => "00_text",
    'custom_item_type_list' => "10_list",
    'custom_item_type_date' => "20_date",
  ],
  // 表示位置定数(重複しない値で設定。TODO 定数管理で良いか検討)
  // 予約/見積
  'POSITION_APPLICATION_BASE_FIELD' => 'estimates_base', // 基本情報
  'POSITION_APPLICATION_CUSTOM_FIELD' => 'estimates_custom', // カスタムフィールド
  // 個人顧客。マイレージモーダルはユーザー側では追加不可
  'POSITION_PERSON_CUSTOM_FIELD' => 'user_custom', // カスタムフィールド
  'POSITION_PERSON_WORKSPACE_SCHOOL' => 'user_workplace', // 勤務先・学校
  'POSITION_PERSON_EMERGENCY_CONTACT' => 'user_emergency', // 緊急連絡先
  'POSITION_PERSON_MILEAGE_MODAL' => 'user_mileage_modal', // マイレージモーダル
  // 法人顧客
  'POSITION_BUSINESS_CUSTOM_FIELD' => 'business_custom', // カスタムフィールド
  // 科目
  'POSITION_SUBJECT_OPTION' => 'subject_option', // オプション科目
  'POSITION_SUBJECT_AIRPLANE' => 'subject_airplane', // 航空券科目
  'POSITION_SUBJECT_HOTEL' => 'subject_hotel', // ホテル科目
  // 相談履歴
  'POSITION_CONSULTATION_CUSTOM_FIELD' => 'consultation_custom', // カスタムフィールド
  // 入出金管理
  'POSITION_MANAGEMENT_COMMON_FIELD' => 'management_common', // 入出金共通フィールド
  'POSITION_PAYMENT_MANAGEMENT' => 'payment_management', // 支払管理
  'POSITION_INVOICE_MANAGEMENT' => 'invoice_management', // 請求管理
  // ユーザー管理
  'POSITION_STAFF_BASE_FIELD' => 'staff_base', // 基本フィールド
  'POSITION_LIST' => [
    'position_person_custom_field' => 'user_custom',
    'position_person_workspace_school' => 'user_workplace',
    'position_person_emergency_contact' => 'user_emergency',
    'position_business_custom_field' => 'business_custom',
    'position_subject_option' => 'subject_option',
    'position_subject_airplane' => 'subject_airplane',
    'position_subject_hotel' => 'subject_hotel',
    'position_staff_base_field' => 'staff_base',
    'position_application_base_field' => 'estimates_base',
    'position_application_custom_field' => 'estimates_custom',
    'position_management_common_field' => 'management_common',
    'position_payment_management' => 'payment_management',
    'position_invoice_management' => 'invoice_management',
    'position_consultation_custom_field' => 'consultation_custom'
    ]
];