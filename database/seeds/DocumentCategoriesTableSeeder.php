<?php

use Illuminate\Database\Seeder;

class DocumentCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('document_categories')->insert([
            ['name' => '共通設定', 'code' => config('consts.document_categories.DOCUMENT_CATEGORY_COMMON'), 'seq' => 0, ],
            ['name' => '見積/予約確認書', 'code' => config('consts.document_categories.DOCUMENT_CATEGORY_QUOTE'), 'seq' => 5, ],
            ['name' => '請求書', 'code' => config('consts.document_categories.DOCUMENT_CATEGORY_REQUEST'), 'seq' => 10, ],
            ['name' => '一括請求書', 'code' => config('consts.document_categories.DOCUMENT_CATEGORY_REQUEST_ALL'), 'seq' => 15, ],
            ['name' => '領収書', 'code' => config('consts.document_categories.DOCUMENT_CATEGORY_RECEIPT'), 'seq' => 20, ],
        ]);

    }
}
