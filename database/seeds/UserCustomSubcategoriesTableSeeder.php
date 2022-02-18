<?php

use Illuminate\Database\Seeder;

class UserCustomSubcategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('user_custom_category_items')->insert([
            // 個人顧客
            [
                'user_custom_category_id' => 1, 
                'name' => '個人顧客 テキスト項目', 
                'type' => config('consts.user_custom_items.CUSTOM_ITEM_TYPE_TEXT'), 
                'display_positions' => implode(',', [
                    config('consts.user_custom_items.POSITION_PERSON_CUSTOM_FIELD'),
                    config('consts.user_custom_items.POSITION_PERSON_WORKSPACE_SCHOOL'),
                    config('consts.user_custom_items.POSITION_PERSON_EMERGENCY_CONTACT'),
                ]),
                'seq' => 0, 
            ],
            [
                'user_custom_category_id' => 1, 
                'name' => '個人顧客 リスト項目', 
                'type' => config('consts.user_custom_items.CUSTOM_ITEM_TYPE_LIST'),  
                'display_positions' => implode(',', [
                    config('consts.user_custom_items.POSITION_PERSON_CUSTOM_FIELD'),
                    config('consts.user_custom_items.POSITION_PERSON_WORKSPACE_SCHOOL'),
                    config('consts.user_custom_items.POSITION_PERSON_EMERGENCY_CONTACT'),
                ]),
                'seq' => 5, 
            ],
            [
                'user_custom_category_id' => 1, 
                'name' => '個人顧客 日時項目', 
                'type' => config('consts.user_custom_items.CUSTOM_ITEM_TYPE_DATE'), 
                'display_positions' => implode(',', [
                    config('consts.user_custom_items.POSITION_PERSON_CUSTOM_FIELD'),
                    config('consts.user_custom_items.POSITION_PERSON_WORKSPACE_SCHOOL'),
                    config('consts.user_custom_items.POSITION_PERSON_EMERGENCY_CONTACT'),
                ]),
                'seq' => 10, ],
            
            // 法人
            [
                'user_custom_category_id' => 2, 
                'name' => '法人顧客 テキスト項目', 
                'type' => config('consts.user_custom_items.CUSTOM_ITEM_TYPE_TEXT'), 
                'display_positions' => implode(',', [
                    config('consts.user_custom_items.POSITION_BUSINESS_CUSTOM_FIELD'),
                ]),
                'seq' => 0, 
            ],
            [
                'user_custom_category_id' => 2, 
                'name' => '法人顧客 リスト項目', 
                'type' => config('consts.user_custom_items.CUSTOM_ITEM_TYPE_LIST'), 
                'display_positions' => implode(',', [
                    config('consts.user_custom_items.POSITION_BUSINESS_CUSTOM_FIELD'),
                ]),
                'seq' => 5, 
            ],
            [
                'user_custom_category_id' => 2, 
                'name' => '法人顧客 日時項目', 
                'type' => config('consts.user_custom_items.CUSTOM_ITEM_TYPE_DATE'), 
                'display_positions' => implode(',', [
                    config('consts.user_custom_items.POSITION_BUSINESS_CUSTOM_FIELD'),
                ]),
                'seq' => 10, 
            ],

            // 予約見積もり
            [
                'user_custom_category_id' => 3, 
                'name' => '予約/見積 テキスト項目', 
                'type' => config('consts.user_custom_items.CUSTOM_ITEM_TYPE_TEXT'), 
                'display_positions' => implode(',', [
                    config('consts.user_custom_items.POSITION_APPLICATION_BASE_FIELD'),
                    config('consts.user_custom_items.POSITION_APPLICATION_CUSTOM_FIELD'),
                ]),
                'seq' => 0, 
            ],
            [
                'user_custom_category_id' => 3, 
                'name' => '予約/見積 リスト項目', 
                'type' => config('consts.user_custom_items.CUSTOM_ITEM_TYPE_LIST'), 
                'display_positions' => implode(',', [
                    config('consts.user_custom_items.POSITION_APPLICATION_BASE_FIELD'),
                    config('consts.user_custom_items.POSITION_APPLICATION_CUSTOM_FIELD'),
                ]),
                'seq' => 5, 
            ],
            [
                'user_custom_category_id' => 3, 
                'name' => '予約/見積 日時項目', 
                'type' => config('consts.user_custom_items.CUSTOM_ITEM_TYPE_DATE'), 
                'display_positions' => implode(',', [
                    config('consts.user_custom_items.POSITION_APPLICATION_BASE_FIELD'),
                    config('consts.user_custom_items.POSITION_APPLICATION_CUSTOM_FIELD'),
                ]),
                'seq' => 10, 
            ],

            // 相談履歴
            [
                'user_custom_category_id' => 4, 
                'name' => '相談履歴 リスト項目', 
                'type' => config('consts.user_custom_items.CUSTOM_ITEM_TYPE_LIST'), 
                'display_positions' => implode(',', [
                    config('consts.user_custom_items.POSITION_CONSULTATION_CUSTOM_FIELD'),
                ]),
                'seq' => 5, 
            ],

            // 科目
            [
                'user_custom_category_id' => 5, 
                'name' => '科目マスタ テキスト項目', 
                'type' => config('consts.user_custom_items.CUSTOM_ITEM_TYPE_TEXT'), 
                'display_positions' => implode(',', [
                    config('consts.user_custom_items.POSITION_SUBJECT_OPTION'),
                    config('consts.user_custom_items.POSITION_SUBJECT_AIRPLANE'),
                    config('consts.user_custom_items.POSITION_SUBJECT_HOTEL'),
                ]),
                'seq' => 0, 
            ],
            [
                'user_custom_category_id' => 5, 
                'name' => '科目マスタ リスト項目', 
                'type' => config('consts.user_custom_items.CUSTOM_ITEM_TYPE_LIST'), 
                'display_positions' => implode(',', [
                    config('consts.user_custom_items.POSITION_SUBJECT_OPTION'),
                    config('consts.user_custom_items.POSITION_SUBJECT_AIRPLANE'),
                    config('consts.user_custom_items.POSITION_SUBJECT_HOTEL'),
                ]),
                'seq' => 5, 
            ],
            [
                'user_custom_category_id' => 5, 
                'name' => '科目マスタ 日時項目', 
                'type' => config('consts.user_custom_items.CUSTOM_ITEM_TYPE_DATE'), 
                'display_positions' => implode(',', [
                    config('consts.user_custom_items.POSITION_SUBJECT_OPTION'),
                    config('consts.user_custom_items.POSITION_SUBJECT_AIRPLANE'),
                    config('consts.user_custom_items.POSITION_SUBJECT_HOTEL'),
                ]),
                'seq' => 10, 
            ],

            // 仕入先
            [
                'user_custom_category_id' => 6, 
                'name' => '仕入れ先マスタ テキスト項目', 
                'type' => config('consts.user_custom_items.CUSTOM_ITEM_TYPE_TEXT'), 
                'display_positions' => null,
                'seq' => 0, 
            ],
            [
                'user_custom_category_id' => 6, 
                'name' => '仕入れ先マスタ リスト項目', 
                'type' => config('consts.user_custom_items.CUSTOM_ITEM_TYPE_LIST'), 
                'display_positions' => null,
                'seq' => 5, 
            ],
            [
                'user_custom_category_id' => 6, 
                'name' => '仕入れ先マスタ 日時項目', 
                'type' => config('consts.user_custom_items.CUSTOM_ITEM_TYPE_DATE'), 
                'display_positions' => null,
                'seq' => 10, 
            ],

            // 入出金管理
            [
                'user_custom_category_id' => 7, 
                'name' => '入出金管理 リスト項目', 
                'type' => config('consts.user_custom_items.CUSTOM_ITEM_TYPE_LIST'), 
                'display_positions' => implode(',', [
                    config('consts.user_custom_items.POSITION_MANAGEMENT_COMMON_FIELD'),
                    // config('consts.user_custom_items.POSITION_PAYMENT_MANAGEMENT'),
                ]),
                'seq' => 5, 
            ],

            // ユーザー
            [
                'user_custom_category_id' => 8, 
                'name' => 'ユーザー管理 リスト項目', 
                'type' => config('consts.user_custom_items.CUSTOM_ITEM_TYPE_LIST'), 
                'display_positions' => null,'seq' => 5, 
            ],
        ]);

    }
}
