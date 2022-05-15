<?php

return [
  // アクション
  'READ' => '1',
  'CREATE' => '2',
  'UPDATE' => '3',
  'DELETE' => '4',
  // 'IMPORT' => '5',
  // 'EXPORT' => '6',
  // 対象
  'USER_MEMBER_CARDS|USER_MILEAGES|USER_VISAS|USERS' => 'user_member_cards|user_mileages|user_visas|users',
  'BUSINESS_USERS|BUSINESS_USER_MANAGERS' => 'business_users|business_user_managers',
  'PARTICIPANTS|RESERVE_CONFIRMS|RESERVE_INVOICES|RESERVE_RECEIPTS|RESERVE_ITINERARIES|RESERVES|WEB_ONLINE_SCHEDULES|WEB_RESERVE_EXTS' => 'participants|reserve_confirms|reserve_invoices|reserve_receipts|reserve_itineraries|reserves|web_online_schedules|web_reserve_exts',
  'ACCOUNT_PAYABLE_DETAILS|ACCOUNT_PAYABLE_ITEMS|ACCOUNT_PAYABLE_RESERVES|ACCOUNT_PAYABLES|AGENCY_BUNDLE_DEPOSITS|AGENCY_DEPOSITS|AGENCY_WITHDRAWAL_ITEM_HISTORIES|AGENCY_WITHDRAWALS|RESERVE_BUNDLE_INVOICES|RESERVE_BUNDLE_RECEIPTS|RESERVE_INVOICES|RESERVE_RECEIPTS|V_RESERVE_INVOICES' => 'account_payable_details|account_payable_items|account_payable_reserves|account_payables|agency_bundle_deposits|agency_deposits|agency_withdrawal_item_histories|agency_withdrawals|reserve_bundle_invoices|reserve_bundle_receipts|reserve_invoices|reserve_receipts|v_reserve_invoices',
  'AGENCY_CONSULTATIONS|WEB_MESSAGES|WEB_MESSAGE_HISTORIES' => 'agency_consultations|web_messages|web_message_histories',
  'DIRECTIONS|V_DIRECTIONS|AREAS|V_AREAS|CITIES|SUBJECT_OPTIONS|SUBJECT_AIRPLANES|SUBJECT_HOTELS|SUPPLIERS' => 'directions|v_directions|areas|v_areas|cities|subject_options|subject_airplanes|subject_hotels|suppliers',
  'AGENCY_ROLES|DOCUMENT_CATEGORIES|DOCUMENT_COMMONS|DOCUMENT_QUOTES|DOCUMENT_RECEIPTS|DOCUMENT_REQUEST_ALLS|DOCUMENT_REQUESTS|MAIL_TEMPLATES|STAFFS|USER_CUSTOM_ITEMS' => 'agency_roles|document_categories|document_commons|document_quotes|document_receipts|document_request_alls|document_requests|mail_templates|staffs|user_custom_items',
  'WEB_COMPANIES|WEB_PROFILES|WEB_MODELCOURSES' => 'web_companies|web_profiles|web_modelcourses',
  'ACTIONS_LIST' => [
    'read' => '1',
    'create' => '2',
    'update' => '3',
    'delete' => '4',
    // 'import' => '5',
    // 'export' => '6',
  ],
  'TARGETS_LIST' => [
    'user_member_cards|user_mileages|user_visas|users' => 'user_member_cards|user_mileages|user_visas|users',
    'business_users|business_user_managers' => 'business_users|business_user_managers',
    'participants|reserve_confirms|reserve_invoices|reserve_receipts|reserve_itineraries|reserves|web_online_schedules|web_reserve_exts' => 'participants|reserve_confirms|reserve_invoices|reserve_receipts|reserve_itineraries|reserves|web_online_schedules|web_reserve_exts',
    'account_payable_details|account_payable_items|account_payable_reserves|account_payables|agency_bundle_deposits|agency_deposits|agency_withdrawal_item_histories|agency_withdrawals|reserve_bundle_invoices|reserve_bundle_receipts|reserve_invoices|reserve_receipts|v_reserve_invoices' => 'account_payable_details|account_payable_items|account_payable_reserves|account_payables|agency_bundle_deposits|agency_deposits|agency_withdrawal_item_histories|agency_withdrawals|reserve_bundle_invoices|reserve_bundle_receipts|reserve_invoices|reserve_receipts|v_reserve_invoices',
    'agency_consultations|web_messages|web_message_histories' => 'agency_consultations|web_messages|web_message_histories',
    'directions|v_directions|areas|v_areas|cities|subject_options|subject_airplanes|subject_hotels|suppliers' => 'directions|v_directions|areas|v_areas|cities|subject_options|subject_airplanes|subject_hotels|suppliers',
    'agency_roles|document_categories|document_commons|document_quotes|document_receipts|document_request_alls|document_requests|mail_templates|staffs|user_custom_items' => 'agency_roles|document_categories|document_commons|document_quotes|document_receipts|document_request_alls|document_requests|mail_templates|staffs|user_custom_items',
    'web_companies|web_profiles|web_modelcourses' => 'web_companies|web_profiles|web_modelcourses',
  ]
];