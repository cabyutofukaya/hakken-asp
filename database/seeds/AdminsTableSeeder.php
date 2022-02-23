<?php

use Illuminate\Database\Seeder;

class AdminsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('admins')->insert([
            'name'              => 'admin1',
            'email'             => 'admin1@hakken-tour.com',
            'password'          => Hash::make('zywDpU*A'),
            'remember_token'    => Str::random(10),
            'created_at'        => date('Y-m-d H:i:s')
        ]);
    }
}
