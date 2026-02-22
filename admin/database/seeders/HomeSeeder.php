<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HomeSeeder extends Seeder
{
    public function run()
    {
        DB::table('page_home')->insert([
            'title'       => '',
            'description' => '',
        ]);
    }
}
