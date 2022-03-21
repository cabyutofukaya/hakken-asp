<?php

return [
  'reserves' => [
    'participant_type' => [
      'participant_type_person' => '個人',
      'participant_type_business' => '法人',
    ],
  ],
  'web_reserve_exts' => [
    'estimate_status' => [
        'estimate_status_consult' => '相談依頼',
        'estimate_status_estimate' => '見積',
        'estimate_status_reserve_request' => '予約依頼',
        'estimate_status_reserve_cancel' => '取消',
        'estimate_status_reserve_rejection' => '辞退',
    ],
  ],
  'web_consults' => [
    'budget_kbn' => [
      'budget_kbn_total' => '総額',
      'budget_kbn_person' => '一人当たり',
    ],  
  ],
  'reserve_itineraries' => [
    'transportation' => [
      'transportation_airplane' => '飛行機',
      'transportation_express' => '新幹線',
      'transportation_train' => '電車',
      'transportation_taxi' => 'タクシー',
      'transportation_bus' => 'バス',
      'transportation_walk' => '徒歩',
      'transportation_others' => 'その他',  
    ]
  ],
  'users' => [
    'tab' => [
      'tab_customer_info' => '顧客情報',
      'tab_usage_history' => '利用履歴',
      'tab_consultation' => '相談一覧',  
    ],
    'sex' => [
      'sex_male' => '男性',
      'sex_female' => '女性',
    ],
    'dm' => [
      'dm_ok' => '可',
      'dm_ng' => '不可',
    ],
    'age_kbn' => [
      'age_kbn_ad' => 'AD',
      'age_kbn_ch' => 'CH',
      'age_kbn_inf' => 'INF',
    ],
    'status' => [
      'status_valid' => '有効',
      'status_suspend' => '無効',
    ],
  ],
  'business_users' => [
    'pay_altogether' => [
      'pay_altogether_yes' => 'あり',
      'pay_altogether_no' => 'なし',  
    ],
    'status' => [
      'status_valid' => '有効',
      'status_suspend' => '無効',
    ],
  ],
  'business_user_managers' => [
    'dm' => [
      'dm_ok' => '可',
      'dm_ng' => '不可',
    ],
  ],
  'agencies' => [
    // ステータス
    'status' => [
      'status_suspend' => '無効',
      'status_main_registration' => '有効',
    ],
    // 業務範囲
    'business_scope' => [
      'business_scope_domestic' => '国内',
      'business_scope_overseas' => '海外',
    ],
    // 登録種別
    'registration_type' => [
      'registration_type_1' => '第一種',
      'registration_type_2' => '第二種',
      'registration_type_3' => '第三種',
      'registration_type_4' => 'その他',
    ],
    // 旅行業協会
    'travel_agency_association' => [
      'travel_agency_association_none' => 'なし',
      'travel_agency_association_nta' => '日旅',
      'travel_agency_association_zenryo' => '全旅',
    ],

  ],
  'staffs' => [
    'status' => [
      'status_valid' => '有効',
      'status_invalid' => '無効',
    ],
  ],
  'agency_roles' => [
    'actions' => [
      'read' => '参照',
      'create' => '登録',
      'update' => '更新',
      'delete' => '削除',
      // 'import' => 'インポート',
      // 'export' => 'エクスポート',
    ],
    // 2つのテーブルに権限を設定する場合は、それぞれのテーブルを「|」でつなぐ
    'targets' => [
      'user_member_cards|user_mileages|user_visas|users' => '顧客管理(個人)',
      'business_users|business_user_managers' => '顧客管理(法人)',
      'participants|reserve_confirms|reserve_invoices|reserve_receipts|reserve_itineraries|reserves|web_online_schedules|web_reserve_exts' => '予約/見積',
      'account_payable_details|account_payables|agency_bundle_deposits|agency_deposits|agency_withdrawals|reserve_bundle_invoices|reserve_bundle_receipts|reserve_invoices|reserve_receipts|v_reserve_invoices' => '経理業務', 
      'agency_consultations|web_messages|web_message_histories' => '相談履歴',
      'directions|v_directions|areas|v_areas|cities|subject_options|subject_airplanes|subject_hotels|suppliers' => 'マスタ管理', 
      'agency_roles|document_categories|document_commons|document_quotes|document_receipts|document_request_alls|document_requests|mail_templates|staffs|user_custom_items' => 'システム設定',
      'web_companies|web_profiles|web_modelcourses' => 'WEBページ管理',
    ]
  ],
  'user_custom_items' => [
    'categories' => [
      'category_text01' => 'テキスト項目1',
      'category_list01' => 'リスト項目1',
    ],
    // カスタムフィールド表示位置
    'position' => [
      'position_person_custom_field' => 'カスタムフィールド',
      'position_person_workspace_school' => '勤務先・学校',
      'position_person_emergency_contact' => '緊急連絡先',
      'position_business_custom_field' => 'カスタムフィールド',
      'position_subject_option' => 'オプション科目',
      'position_subject_airplane' => '航空券科目',
      'position_subject_hotel' => 'ホテル科目',
      'position_staff_base_field' => '基本情報', // この表記でよいかちょっと考える
      'position_application_base_field' => '基本情報',
      'position_application_custom_field' => 'カスタムフィールド',
      'position_management_common_field' => 'カスタムフィールド',
      'position_consultation_custom_field' => 'カスタムフィールド',
      // 'position_payment_management' => '支払管理(固定)',
      // 'position_invoice_management' => '請求管理(固定)',
    ],
    // テキスト項目入力タイプ
    'input_type_text' => [
      'input_type_text_01' => '1行入力',
      'input_type_text_02' => '複数行入力',
    ],
    // 日時項目入力タイプ
    'input_type_date' => [
      'input_type_date_01' => 'カレンダー',
      'input_type_date_02' => '時間',
    ]
  ],
  // 'consultations' => [
  //   'type' => [
  //     'type_order_made' => 'オーダーメイド',
  //     'type_tour' => 'ツアー',
  //   ],
  //   'to_transportation' => [
  //     'to_transportation_shinkansen' => '新幹線',
  //     'to_transportation_airplane' => '飛行機',
  //     'to_transportation_other' => 'その他',
  //   ],
  //   'at_transportation' => [
  //     'at_transportation_car_rental' => 'レンタカー',
  //     'at_transportation_chartered_taxi' => '貸切タクシー',
  //     'at_transportation_sightseeing_bus' => '定期観光バス',
  //     'at_transportation_other' => 'その他',
  //   ],
  //   'proposal_method' => [
  //     'proposal_method_online' => 'オンライン',
  //     'proposal_method_offline' => 'オフライン',
  //   ],
  //   'contact_app' => [
  //     'contact_app_line' => 'LINE',
  //     'contact_app_zoom' => 'Zoom',
  //     'contact_app_skype' => 'Skype',
  //     'contact_app_google_meet' => 'Google Meet',
  //   ],
  //   'meal' => [
  //     'meal_no' => 'なし',
  //     'meal_yes' => 'あり',
  //   ],
  //   'meal_detail' => [
  //     'meal_detail_breakfast_only' => '朝食のみ',
  //   ],
  // ],
  'agency_consultations' => [
    'status' => [
      'status_reception' => '受付',
      'status_responding' => '対応中',
      'status_completion' => '完了',
    ],
    'kind' => [
      'kind_estimate' => '見積',
      'kind_reserve' => '予約',
      'kind_question' => '質問',
      'kind_request' => '要望',
      'kind_message' => 'メッセージ',
      'kind_others' => 'その他',
    ],
  ],
  'suppliers' => [
    'reference_date' => [
      'reference_date_application_date' => '申込日',
      'reference_date_departure_date' => '出発日',
      'reference_date_return_date' => '帰着日',
      'reference_date_guidance_deadline' => '案内期限',
      'reference_date_fnl_date' => 'FNL日',
      'reference_date_ticketlimit' => 'ticketlimit',
    ],
    'payment_month' => [
      'payment_month_add_zero' => "当月",
      'payment_month_add_one' => "翌月",
      'payment_month_add_two' => "翌々月",
      'payment_month_add_three' => "3ヵ月後",  
    ]
  ],
  'supplier_account_payables' => [
    'account_type' => [
      'account_type_saving' => "普通",
      'account_type_checking' => "当座",
    ]
  ],
  'subject_categories' => [
    'zei_kbn' => [
      'zei_kbn_tax8' => '課税(8％)',
      'zei_kbn_tax10' => '課税(10％)',
      'zei_kbn_tax_free' => '非課税',
      'zei_kbn_non_tax' => '不課税',
    ],
    // 書類用
    'document_zei_kbn' => [
      'zei_kbn_tax8' => '8％',
      'zei_kbn_tax10' => '10％',
      'zei_kbn_tax_free' => '非課税',
      'zei_kbn_non_tax' => '不課税',
    ],
    'subject_category' => [
      'subject_category_option' => 'オプション科目',
      'subject_category_airplane' => '航空券科目',
      'subject_category_hotel' => 'ホテル科目',
    ]
  ],
  'document_commons' => [
    'address_person' => '宛名(個人顧客)',
    'address_business' => '宛名(法人顧客)',
    'company_info' => '自社情報',
  ],
  'documents' => [
    'honorific' => [
      'honorific_sama' => "様",
      'honorific_onchu' => "御中",  
    ],
  ],
  'management_invoices' => [
    'status' => [
      'status_not_deposited' => '未入金のみ',
    ]
  ],
  'reserve_confirms' => [
    'status' => [
      'status_issued' => '発行済み',
      'status_unissued' => '未発行',
    ],
  ],
  'reserve_invoices' => [
    'status' => [
      'status_billed' => '請求済み',
      'status_unclaimed' => '未請求',
    ]
  ],
  'reserve_bundle_invoices' => [
    'status' => [
      'status_billed' => '請求済み',
      'status_unclaimed' => '未請求',
    ]
  ],
  'v_reserve_invoices' => [
    'status' => [
      'status_billed' => '請求済み',
      'status_unclaimed' => '未請求',
    ]
  ],
  'reserve_receipts' => [
    'status' => [
      'status_issued' => '発行済み',
      'status_not_issued' => '未発行',
    ]
  ],
  'reserve_bundle_receipts' => [
    'status' => [
      'status_issued' => '発行済み',
      'status_not_issued' => '未発行',
    ]
  ],  
  'document_quotes' => [
    'display_block' => '表示ブロック',
    'reservation_info' => '予約情報',
    'air_ticket_info' => '航空券情報',
    'breakdown_price' => '代金内訳',
  ],
  'document_requests' => [
    'display_block' => '表示ブロック',
    'reservation_info' => '予約情報',
    'air_ticket_info' => '航空券情報',
    'breakdown_price' => '代金内訳',
  ],
  'document_request_alls' => [
    'display_block' => '表示ブロック',
    'reservation_info' => '予約情報',
    'breakdown_price' => '代金内訳',
  ],
  'account_payable_details' => [
    'status' => [
      'status_none' => '', // 支払いナシ
      'status_unpaid' => '未払',
      'status_paid' => '支払済み',
      'status_overpaid' => '過払',
    ],
  ],
  'web_users' => [
    'age_kbn' => [
      'age_kbn_ad' => 'AD',
      'age_kbn_ch' => 'CH',
      'age_kbn_inf' => 'INF',
    ],
    'status' => [
      'status_valid' => '有効',
      'status_suspend' => '無効',
    ],
  ],
  'web_modelcourses' => [
    'stay' => [
      'stay0' => '日帰り', 
      'stay1' => '1泊2日', 
      'stay2' => '2泊3日', 
      'stay3' => '3泊4日', 
      'stay4' => '4泊5日', 
      'stay5' => '5泊6日', 
      'stay6' => '6泊7日', 
      'stay7' => '7泊8日', 
    ],
  ],
];
