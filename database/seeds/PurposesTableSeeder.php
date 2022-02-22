<?php

use Illuminate\Database\Seeder;

class PurposesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('purposes')->insert([
            ['name' => '職場旅行', 'seq' => 5],
            ['name' => '教育旅行', 'seq' => 10],
            ['name' => 'イベントMICE', 'seq' => 15],
            ['name' => '視察研修旅行', 'seq' => 20],
            ['name' => '招待旅行', 'seq' => 25],
            ['name' => '報奨旅行', 'seq' => 30],
            ['name' => '親睦旅行', 'seq' => 35],
            ['name' => '福祉旅行', 'seq' => 40],
            ['name' => '遠足', 'seq' => 45],
            ['name' => '合宿', 'seq' => 50],
            ['name' => 'スポーツ旅行(ゴルフやスキーなど)', 'seq' => 55],
            ['name' => 'ビジネス業務旅行', 'seq' => 60],
            ['name' => 'グルメ旅行', 'seq' => 65],
            ['name' => '湯治旅行', 'seq' => 70],
            ['name' => '巡拝旅行', 'seq' => 75],
            ['name' => '趣味の旅行', 'seq' => 80],
            ['name' => '福祉旅行', 'seq' => 85],
            ['name' => '医療ツーリズム', 'seq' => 90],
            ['name' => 'エコツアー', 'seq' => 95],
            ['name' => '家族旅行', 'seq' => 100],
            ['name' => '新婚旅行', 'seq' => 105],
            ['name' => 'その他', 'seq' => 999],
        ]);

    }
}
