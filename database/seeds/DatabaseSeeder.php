<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            CountriesTableSeeder::class,
            InflowsTableSeeder::class,
            PurposesTableSeeder::class,
            InterestsTableSeeder::class,
            PrefecturesTableSeeder::class,
            ContractPlansTableSeeder::class,
            AdminsTableSeeder::class,
            AgencySequencesTableSeeder::class, 
            UserCustomCategoriesTableSeeder::class,
            UserCustomSubcategoriesTableSeeder::class,
            DocumentCategoriesTableSeeder::class,
            SubjectCategoriesTableSeeder::class,
            WebUserSequencesTableSeeder::class,
            WebRequestSequencesTableSeeder::class,
            ZoomApiKeysTableSeeder::class,
        ]);
    }
}
