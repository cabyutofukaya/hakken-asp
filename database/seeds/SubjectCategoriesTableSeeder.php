<?php

use Illuminate\Database\Seeder;

class SubjectCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('subject_categories')->insert([
            ['name' => 'オプション科目', 'code' => config('consts.subject_categories.SUBJECT_CATEGORY_OPTION'), 'seq' => 0, ],
            ['name' => '航空券科目', 'code' => config('consts.subject_categories.SUBJECT_CATEGORY_AIRPLANE'), 'seq' => 5, ],
            ['name' => 'ホテル科目', 'code' => config('consts.subject_categories.SUBJECT_CATEGORY_HOTEL'), 'seq' => 10, ],
        ]);

    }
}
