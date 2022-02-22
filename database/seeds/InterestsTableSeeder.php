<?php

use Illuminate\Database\Seeder;

class InterestsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('interests')->insert([
                ['name' => '行列の出来る飲食店', 'seq' => 5],
                ['name' => 'テレビや雑誌で紹介されるショップ', 'seq' => 10],
                ['name' => '人気のお土産や名産品', 'seq' => 15],
                ['name' => '流行りのレストランやカフェ', 'seq' => 20],
                ['name' => '地元の人との交流', 'seq' => 25],
                ['name' => '人気の温泉場や旅館', 'seq' => 30],
                ['name' => '新名所や景勝地', 'seq' => 35],
                ['name' => 'こどもの喜ぶレジャー施設', 'seq' => 40],
                ['name' => '人気のリゾート地', 'seq' => 45],
                ['name' => '農業や漁業体験', 'seq' => 50],
                ['name' => '海産物や農産物', 'seq' => 55],
                ['name' => 'スポーツ', 'seq' => 60],
                ['name' => '釣り・登山・キャンプ', 'seq' => 65],
                ['name' => '城や歴史', 'seq' => 70],
                ['name' => '寺社仏閣', 'seq' => 75],
                ['name' => '郷土料理', 'seq' => 80],
                ['name' => '酒(日本酒・ワイン・ビール)', 'seq' => 85],
                ['name' => '郷土芸能', 'seq' => 90],
                ['name' => '祭りや行事', 'seq' => 95],
                ['name' => '美術館や博物館', 'seq' => 100],
                ['name' => '伝統工芸や日本文化', 'seq' => 105],
                ['name' => 'フルーツ狩り', 'seq' => 110],
                ['name' => '動物園や植物園', 'seq' => 115],
                ['name' => 'パワースポットや映えスポット', 'seq' => 120],
                ['name' => '世界遺産', 'seq' => 125],
                ['name' => '重要文化財や国宝', 'seq' => 130],
                ['name' => '鉄道旅', 'seq' => 135],
                ['name' => 'クルーズ船', 'seq' => 140],
                ['name' => 'その他', 'seq' => 999],
            ]);

    }
}
