<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('artecles')->insert([
            'name'      => 'oleg',
            'email'     => 'oleg@gmail.com',
            'password'  => bcrypt('oleg123454321'),
            'isAdmin'   => 1,
        ]);

    }
}