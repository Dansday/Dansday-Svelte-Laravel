<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectGallerySeeder extends Seeder
{
    public function run()
    {
        DB::table('project_gallery')->insert([
            'image'         => 'demo/img/projects/project_image_4063.jpg',
            'project_id'    => 2,
        ]);
        DB::table('project_gallery')->insert([
            'image'         => 'demo/img/projects/project_image_5873.jpg',
            'project_id'    => 2,
        ]);
        DB::table('project_gallery')->insert([
            'image'         => 'demo/img/projects/project_image_1328.jpg',
            'project_id'    => 2,
        ]);
        DB::table('project_gallery')->insert([
            'image'         => 'demo/img/projects/project_image_8950.jpg',
            'project_id'    => 5,
        ]);
        DB::table('project_gallery')->insert([
            'image'         => 'demo/img/projects/project_image_5350.jpg',
            'project_id'    => 5,
        ]);
        DB::table('project_gallery')->insert([
            'image'         => 'demo/img/projects/project_image_7847.jpg',
            'project_id'    => 5,
        ]);
    }
}
