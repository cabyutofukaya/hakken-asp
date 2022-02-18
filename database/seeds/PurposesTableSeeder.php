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
            ['name' => '社員旅行', 'seq' => 0],
            ['name' => '親睦旅行', 'seq' => 5],
            ['name' => '招待旅行', 'seq' => 10],
            ['name' => '視察旅行', 'seq' => 15],
            ['name' => '会議・イベント', 'seq' => 20],
            ['name' => '遠足', 'seq' => 25],
            ['name' => '修学旅行', 'seq' => 30],
            ['name' => 'グルメ旅', 'seq' => 35],
            ['name' => '家族旅行', 'seq' => 40],
            ['name' => '新婚旅行', 'seq' => 45],
            ['name' => '交流・体験旅行', 'seq' => 50],
            ['name' => '趣味旅', 'seq' => 55],
            ['name' => '霊場めぐり', 'seq' => 60],
            ['name' => '湯治旅', 'seq' => 65],
            ['name' => '離島旅', 'seq' => 70],
            ['name' => 'その他', 'seq' => 99],
        ]);

    }
}
