<?php

use Illuminate\Database\Seeder;

class InflowsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('inflows')->insert([
            'id'            => 1,
            'site_name'     => 'HAKKEN',
            'url'           => 'https://hakken-tour.com',
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);
    }
}
