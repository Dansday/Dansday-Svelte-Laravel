<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SkillSeeder extends Seeder
{
    public function run()
    {
        DB::table('skill')->insert([
            'type'          => 'design',
            'title'         => 'Photoshop',
            'order'         => '1',
        ]);
        DB::table('skill')->insert([
            'type'          => 'design',
            'title'         => 'Illustrator',
            'order'         => '2',
        ]);
        DB::table('skill')->insert([
            'type'          => 'design',
            'title'         => 'Indesign',
            'order'         => '3',
        ]);
        DB::table('skill')->insert([
            'type'          => 'design',
            'title'         => 'Freehand',
            'order'         => '4',
        ]);
        DB::table('skill')->insert([
            'type'          => 'development',
            'title'         => 'PHP',
            'order'         => '1',
        ]);
        DB::table('skill')->insert([
            'type'          => 'development',
            'title'         => 'HTML',
            'order'         => '4',
        ]);
        DB::table('skill')->insert([
            'type'          => 'development',
            'title'         => 'CSS',
            'order'         => '5',
        ]);
    }
}
