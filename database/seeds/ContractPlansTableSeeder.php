<?php

use Illuminate\Database\Seeder;

class ContractPlansTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('contract_plans')->insert([
            [
                'name' => 'プランA',
                'monthly_sum' => 30000,
                'period' => config('consts.const.AGENCY_CONTRACT_PERIOD_DEFAULT'),
                'number_staff' => 5,
                'updated_at' => date('Y-m-d'),
                'created_at' => date('Y-m-d'),
            ],
            [
                'name' => 'プランB',
                'monthly_sum' => 20000,
                'period' => config('consts.const.AGENCY_CONTRACT_PERIOD_DEFAULT'),
                'number_staff' => 2,
                'updated_at' => date('Y-m-d'),
                'created_at' => date('Y-m-d'),
            ],
        ]);

    }
}
