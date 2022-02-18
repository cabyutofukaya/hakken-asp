<?php

use Illuminate\Database\Seeder;

class ZoomApiKeysTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // TODO 個人のAPIキーなので本番時は差し替えること
        DB::table('zoom_api_keys')->insert([
            [
                'api_key' => 'eqXk0QtkTRmoozS2Gx86yg', 
                'api_secret' => '8g1OExNbbRaw3oRdtGS4V4Uhbc7tCvBFuf59', 
                'im_chat_history_token' => 'eyJhbGciOiJIUzI1NiJ9.eyJpc3MiOiIzOUxRY2tNdFNiQ3c2bk0zTGVVQ3NRIn0.QqG4-EODlMSbxLQxe6Cjs630TzbK3FnFnXb0A9X4tJQ', 
            ],
        ]);

    }
}
