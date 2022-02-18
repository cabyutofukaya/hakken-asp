<?php

use Illuminate\Database\Seeder;

class WebRequestSequencesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('web_request_sequences')->insert([
            'current_number'    => 0,
            'updated_at'        => date('Y-m-d H:i:s'),
        ]);

    }
}
