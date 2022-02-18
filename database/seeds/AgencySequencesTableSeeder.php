<?php

use Illuminate\Database\Seeder;

class AgencySequencesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('agency_sequences')->insert([
            'current_number'    => 0,
            'updated_at'        => date('Y-m-d'),
        ]);

    }
}
