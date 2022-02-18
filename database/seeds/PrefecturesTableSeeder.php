<?php

use Illuminate\Database\Seeder;

class PrefecturesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('prefectures')->insert([
            ['code' => '01', 'name' => '北海道', 'block_name' => '北海道'],
            ['code' => '02', 'name' => '青森県', 'block_name' => '東北'],
            ['code' => '03', 'name' => '岩手県', 'block_name' => '東北'],
            ['code' => '04', 'name' => '宮城県', 'block_name' => '東北'],
            ['code' => '05', 'name' => '秋田県', 'block_name' => '東北'],
            ['code' => '06', 'name' => '山形県', 'block_name' => '東北'],
            ['code' => '07', 'name' => '福島県', 'block_name' => '東北'],
            ['code' => '08', 'name' => '茨城県', 'block_name' => '関東'],
            ['code' => '09', 'name' => '栃木県', 'block_name' => '関東'],
            ['code' => '10', 'name' => '群馬県', 'block_name' => '関東'],
            ['code' => '11', 'name' => '埼玉県', 'block_name' => '関東'],
            ['code' => '12', 'name' => '千葉県', 'block_name' => '関東'],
            ['code' => '13', 'name' => '東京都', 'block_name' => '関東'],
            ['code' => '14', 'name' => '神奈川県', 'block_name' => '関東'],
            ['code' => '15', 'name' => '新潟県', 'block_name' => '中部'],
            ['code' => '16', 'name' => '富山県', 'block_name' => '中部'],
            ['code' => '17', 'name' => '石川県', 'block_name' => '中部'],
            ['code' => '18', 'name' => '福井県', 'block_name' => '中部'],
            ['code' => '19', 'name' => '山梨県', 'block_name' => '中部'],
            ['code' => '20', 'name' => '長野県', 'block_name' => '中部'],
            ['code' => '21', 'name' => '岐阜県', 'block_name' => '中部'],
            ['code' => '22', 'name' => '静岡県', 'block_name' => '中部'],
            ['code' => '23', 'name' => '愛知県', 'block_name' => '中部'],
            ['code' => '24', 'name' => '三重県', 'block_name' => '近畿'],
            ['code' => '25', 'name' => '滋賀県', 'block_name' => '近畿'],
            ['code' => '26', 'name' => '京都府', 'block_name' => '近畿'],
            ['code' => '27', 'name' => '大阪府', 'block_name' => '近畿'],
            ['code' => '28', 'name' => '兵庫県', 'block_name' => '近畿'],
            ['code' => '29', 'name' => '奈良県', 'block_name' => '近畿'],
            ['code' => '30', 'name' => '和歌山県', 'block_name' => '近畿'],
            ['code' => '31', 'name' => '鳥取県', 'block_name' => '中国'],
            ['code' => '32', 'name' => '島根県', 'block_name' => '中国'],
            ['code' => '33', 'name' => '岡山県', 'block_name' => '中国'],
            ['code' => '34', 'name' => '広島県', 'block_name' => '中国'],
            ['code' => '35', 'name' => '山口県', 'block_name' => '中国'],
            ['code' => '36', 'name' => '徳島県', 'block_name' => '四国'],
            ['code' => '37', 'name' => '香川県', 'block_name' => '四国'],
            ['code' => '38', 'name' => '愛媛県', 'block_name' => '四国'],
            ['code' => '39', 'name' => '高知県', 'block_name' => '四国'],
            ['code' => '40', 'name' => '福岡県', 'block_name' => '九州'],
            ['code' => '41', 'name' => '佐賀県', 'block_name' => '九州'],
            ['code' => '42', 'name' => '長崎県', 'block_name' => '九州'],
            ['code' => '43', 'name' => '熊本県', 'block_name' => '九州'],
            ['code' => '44', 'name' => '大分県', 'block_name' => '九州'],
            ['code' => '45', 'name' => '宮崎県', 'block_name' => '九州'],
            ['code' => '46', 'name' => '鹿児島県', 'block_name' => '九州'],
            ['code' => '47', 'name' => '沖縄県', 'block_name' => '九州'],
        ]);

    }
}
