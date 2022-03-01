<?php

namespace App\Traits;

use Illuminate\Support\Arr;
use Lang;

/**
 * Javascriptに渡す定数データを扱うtrait
 */
trait JsConstsTrait
{
    public function getJsVars(string $agencyAccount) : array
    {
        return [
            'agencyAccount' => $agencyAccount,
            // 受付種別
            'receptionTypes' => [
                'asp' => config('consts.const.RECEPTION_TYPE_ASP'),
                'web' => config('consts.const.RECEPTION_TYPE_WEB'),
            ],
            //　カスタム項目のカテゴリ一覧
            'userCustomCategoryCodes' => [
                'persion' => config('consts.user_custom_categories.CUSTOM_CATEGORY_PERSON'),
                'business' => config('consts.user_custom_categories.CUSTOM_CATEGORY_BUSINESS'),
                'reserve' => config('consts.user_custom_categories.CUSTOM_CATEGORY_RESERVE'),
                'consultation' => config('consts.user_custom_categories.CUSTOM_CATEGORY_CONSULTATION'),
                'subject' => config('consts.user_custom_categories.CUSTOM_CATEGORY_SUBJECT'),
                'supplier' => config('consts.user_custom_categories.CUSTOM_CATEGORY_SUPPLIER'),
                'management' => config('consts.user_custom_categories.CUSTOM_CATEGORY_MANAGEMENT'),
                'user' => config('consts.user_custom_categories.CUSTOM_CATEGORY_USER'),
            ],
            // カスタム項目タイプ
            'customFieldTypes' => [
                'text' => config('consts.user_custom_items.CUSTOM_ITEM_TYPE_TEXT'),
                'list' => config('consts.user_custom_items.CUSTOM_ITEM_TYPE_LIST'),
                'date' => config('consts.user_custom_items.CUSTOM_ITEM_TYPE_DATE'),    
            ],
            // カスタム項目入力タイプ
            'customFieldInputTypes' => [
                'oneline' => config('consts.user_custom_items.INPUT_TYPE_TEXT_01'),
                'multiple' => config('consts.user_custom_items.INPUT_TYPE_TEXT_02'),
                'calendar' => config('consts.user_custom_items.INPUT_TYPE_DATE_01'),
                'time' => config('consts.user_custom_items.INPUT_TYPE_DATE_02'),
            ],
            // カスタムフィールド位置情報
            'customFieldPositions' => [
                'estimates_base' => config('consts.user_custom_items.POSITION_APPLICATION_BASE_FIELD'),
                'estimates_custom' => config('consts.user_custom_items.POSITION_APPLICATION_CUSTOM_FIELD'),
                'user_custom' => config('consts.user_custom_items.POSITION_PERSON_CUSTOM_FIELD'),
                'user_workplace' => config('consts.user_custom_items.POSITION_PERSON_WORKSPACE_SCHOOL'),
                'user_emergency' => config('consts.user_custom_items.POSITION_PERSON_EMERGENCY_CONTACT'),
                'user_mileage_modal' => config('consts.user_custom_items.POSITION_PERSON_MILEAGE_MODAL'),
                'business_custom' => config('consts.user_custom_items.POSITION_BUSINESS_CUSTOM_FIELD'),
                'subject_option' => config('consts.user_custom_items.POSITION_SUBJECT_OPTION'),
                'subject_airplane' => config('consts.user_custom_items.POSITION_SUBJECT_AIRPLANE'),
                'subject_hotel' => config('consts.user_custom_items.POSITION_SUBJECT_HOTEL'),
                'consultation_custom' => config('consts.user_custom_items.POSITION_CONSULTATION_CUSTOM_FIELD'),
                'management_common' => config('consts.user_custom_items.POSITION_MANAGEMENT_COMMON_FIELD'),
                'payment_management' => config('consts.user_custom_items.POSITION_PAYMENT_MANAGEMENT'),
                'staff_base' => config('consts.user_custom_items.POSITION_STAFF_BASE_FIELD'),
            ],
            // カスタムフィールドの管理コード。必要に応じて追加していく
            'customFieldCodes' => [
                // 'code_application_travel_type' => config('consts.user_custom_items.CODE_APPLICATION_TRAVEL_TYPE'), // 旅行種別
                'code_user_customer_kbn' => config('consts.user_custom_items.CODE_USER_CUSTOMER_KBN'),
                'code_user_customer_rank' => config('consts.user_custom_items.CODE_USER_CUSTOMER_RANK'),
                'code_user_customer_receptionist' => config('consts.user_custom_items.CODE_USER_CUSTOMER_RECEPTIONIST'),
                'code_user_customer_airplane_company' => config('consts.user_custom_items.CODE_USER_CUSTOMER_AIRPLANE_COMPANY'),
            ],
            // application_stepの値
            'applicationSteps' => [
                'normal' => config('consts.reserves.APPLICATION_STEP_DRAFT'), // 見積
                'reserve' => config('consts.reserves.APPLICATION_STEP_RESERVE'), // 予約
                // 'departed' => config('consts.reserves.APPLICATION_STEP_DEPARTED'), 催行済はスコープでのみ使用する値なのでテーブルに設定されている値としては使用しない
            ],
            // 税区分
            'documentZeiKbns' => get_const_item('subject_categories', 'document_zei_kbn'),
            // 科目カテゴリ一覧
            'subjectCategories' => config('consts.subject_categories.SUBJECT_CATEGORY_LIST'),
        ];
    }
}
