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
         $this->call(SiteSettingsSeeder::class);
         $this->call(ArticleSeeder::class);
         $this->call(UserSeeder::class);
    }
}
