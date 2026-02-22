<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriesSeeder extends Seeder
{
    public function run()
    {
        DB::table('article_category')->insert([
            'name'      => 'Photography',
            'slug'      => 'photography',
        ]);
        DB::table('article_category')->insert([
            'name'      => 'Web design',
            'slug'      => 'web-design',
        ]);
    }
}
