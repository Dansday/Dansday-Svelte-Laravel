<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExperienceSeeder extends Seeder
{
    public function run()
    {
        DB::table('experience')->insert([
            'type'          => 'education',
            'period'        => '2005 - 2008',
            'title'         => 'Graphic design',
            'description'   => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum consectetur purus faucibus felis placerat, non scelerisque lectus imperdiet.',
            'order'         => '1',
        ]);
        DB::table('experience')->insert([
            'type'          => 'education',
            'period'        => '2003 - 2005',
            'title'         => 'Web development',
            'description'   => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum consectetur purus faucibus felis placerat, non scelerisque lectus imperdiet.',
            'order'         => '2',
        ]);
        DB::table('experience')->insert([
            'type'          => 'education',
            'period'        => '2002 - 2003',
            'title'         => 'Web design',
            'description'   => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum consectetur purus faucibus felis placerat, non scelerisque lectus imperdiet.',
            'order'         => '3',
        ]);
        DB::table('experience')->insert([
            'type'          => 'employment',
            'period'        => '2010 - Present',
            'title'         => 'Senior web developer',
            'description'   => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum consectetur purus faucibus felis placerat, non scelerisque lectus imperdiet.',
            'order'         => '1',
        ]);
        DB::table('experience')->insert([
            'type'          => 'employment',
            'period'        => '2008 -2010',
            'title'         => 'Junior web developer',
            'description'   => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum consectetur purus faucibus felis placerat, non scelerisque lectus imperdiet.',
            'order'         => '2',
        ]);
    }
}
