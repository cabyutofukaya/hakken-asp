<?php

use Illuminate\Database\Seeder;

class UserCustomCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('user_custom_categories')->insert([
            ['name' => '個人顧客', 'code' => config('consts.user_custom_categories.CUSTOM_CATEGORY_PERSON'), 'seq' => 0, ],
            ['name' => '法人顧客', 'code' => config('consts.user_custom_categories.CUSTOM_CATEGORY_BUSINESS'), 'seq' => 5, ],
            ['name' => '予約/見積', 'code' => config('consts.user_custom_categories.CUSTOM_CATEGORY_RESERVE'), 'seq' => 10, ],
            ['name' => '相談履歴', 'code' => config('consts.user_custom_categories.CUSTOM_CATEGORY_CONSULTATION'), 'seq' => 15, ],
            ['name' => '科目マスタ', 'code' => config('consts.user_custom_categories.CUSTOM_CATEGORY_SUBJECT'), 'seq' => 20, ],
            ['name' => '仕入れ先マスタ', 'code' => config('consts.user_custom_categories.CUSTOM_CATEGORY_SUPPLIER'), 'seq' => 25, ],
            ['name' => '入出金管理', 'code' => config('consts.user_custom_categories.CUSTOM_CATEGORY_MANAGEMENT'), 'seq' => 30, ],
            ['name' => 'ユーザー管理', 'code' => config('consts.user_custom_categories.CUSTOM_CATEGORY_USER'), 'seq' => 35, ],
        ]);

    }
}
