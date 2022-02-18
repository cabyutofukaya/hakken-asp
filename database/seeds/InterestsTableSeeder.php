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
                ['name' => '行列の出来る飲食店', 'seq' => 0],
                ['name' => 'テレビや雑誌で紹介されるショップ', 'seq' => 5],
                ['name' => '人気のお土産や名産品', 'seq' => 10],
                ['name' => '流行りのレストランやカフェ', 'seq' => 15],
                ['name' => '地元の人との交流', 'seq' => 20],
                ['name' => '人気の秘湯・温泉場や旅館', 'seq' => 25],
                ['name' => 'こどもの喜ぶレジャー施設', 'seq' => 30],
                ['name' => '人気のリゾート地', 'seq' => 35],
                ['name' => '農業・漁業体験', 'seq' => 40],
                ['name' => 'スポーツ', 'seq' => 45],
                ['name' => '釣り・登山・キャンプ', 'seq' => 50],
                ['name' => '新名所・景勝地', 'seq' => 55],
                ['name' => '海産物・農産物', 'seq' => 60],
                ['name' => '城や歴史・寺社仏閣', 'seq' => 65],
                ['name' => '郷土料理', 'seq' => 70],
                ['name' => '酒・ワイン・ビール', 'seq' => 75],
                ['name' => '郷土芸能', 'seq' => 80],
                ['name' => '祭りや行事', 'seq' => 85],
                ['name' => '美術館・博物館', 'seq' => 90],
                ['name' => '伝統工芸・日本文化', 'seq' => 95],
                ['name' => 'フルーツ狩り', 'seq' => 100],
                ['name' => '動物園・植物園', 'seq' => 105],
                ['name' => 'パワースポット', 'seq' => 110],
                ['name' => '世界遺産', 'seq' => 115],
                ['name' => '重要文化財', 'seq' => 120],
                ['name' => '国宝', 'seq' => 125],
            ]);

    }
}
