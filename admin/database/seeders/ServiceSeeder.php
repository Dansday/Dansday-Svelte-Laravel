<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceSeeder extends Seeder
{
    public function run()
    {
        DB::table('service')->insert([
            'title'         => 'Business and Brand Strategy',
            'description'   => 'Lorem ipsum dolor sit amet, consectetur adicing elit pellente enim leo ipsum.',
            'info'          => '[{"title":"Vivamus feugiat","text":""},{"title":"Habitasse platea ditums","text":""},{"title":"Donec placerat dignissim,","text":""},{"title":"Nam luctus vulputate","text":""}]',
            'order'         => '1',
        ]);
        DB::table('service')->insert([
            'title'         => 'Mobile and Desktop Applications',
            'description'   => 'Lorem ipsum dolor sit amet, consectetur adicing elit pellente enim leo ipsum.',
            'info'          => '[{"title":"Vivamus feugiat","text":""},{"title":"Habitasse platea ditums","text":""},{"title":"Donec placerat dignissim,","text":""},{"title":"Nam luctus vulputate","text":""}]',
            'order'         => '2',
        ]);
        DB::table('service')->insert([
            'title'         => 'Game Design & Development',
            'description'   => 'Lorem ipsum dolor sit amet, consectetur adicing elit pellente enim leo ipsum.',
            'info'          => '[{"title":"Vivamus feugiat","text":""},{"title":"Habitasse platea ditums","text":""},{"title":"Donec placerat dignissim,","text":""},{"title":"Nam luctus vulputate","text":""}]',
            'order'         => '3',
        ]);
        DB::table('service')->insert([
            'title'         => 'Web Design & Development',
            'description'   => 'Lorem ipsum dolor sit amet, consectetur adicing elit pellente enim leo ipsum.',
            'info'          => '[{"title":"Vivamus feugiat","text":""},{"title":"Habitasse platea ditums","text":""},{"title":"Donec placerat dignissim,","text":""},{"title":"Nam luctus vulputate","text":""}]',
            'order'         => '4',
        ]);
        DB::table('service')->insert([
            'title'         => 'Product and Social Photography',
            'description'   => 'Lorem ipsum dolor sit amet, consectetur adicing elit pellente enim leo ipsum.',
            'info'          => '[{"title":"Vivamus feugiat","text":""},{"title":"Habitasse platea ditums","text":""},{"title":"Donec placerat dignissim,","text":""},{"title":"Nam luctus vulputate","text":""}]',
            'order'         => '5',
        ]);
        DB::table('service')->insert([
            'title'         => 'Social Marketing',
            'description'   => 'Lorem ipsum dolor sit amet, consectetur adicing elit pellente enim leo ipsum.',
            'info'          => '[{"title":"Vivamus feugiat","text":""},{"title":"Habitasse platea ditums","text":""},{"title":"Donec placerat dignissim,","text":""},{"title":"Nam luctus vulputate","text":""}]',
            'order'         => '6',
        ]);
    }
}
