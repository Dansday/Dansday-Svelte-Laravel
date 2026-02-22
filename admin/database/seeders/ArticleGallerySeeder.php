<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ArticleGallerySeeder extends Seeder
{
    public function run()
    {
        DB::table('article_image')->insert([
            'image'       => 'demo/img/articles/gallery_image_9329.jpg',
            'article_id'  => 3,
        ]);
        DB::table('article_image')->insert([
            'image'       => 'demo/img/articles/gallery_image_7303.jpg',
            'article_id'  => 3,
        ]);
        DB::table('article_image')->insert([
            'image'       => 'demo/img/articles/gallery_image_4276.jpg',
            'article_id'  => 3,
        ]);
    }
}
