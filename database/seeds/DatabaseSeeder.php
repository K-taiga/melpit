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
        $this->call(UserSeeder::class);
        $this->call(ItemConditionSeeder::class);
        $this->call(PrimaryCategory::class);
        $this->call(SecodnaryCategory::class);
    }
}
