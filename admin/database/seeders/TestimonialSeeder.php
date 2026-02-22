<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestimonialSeeder extends Seeder
{
    public function run()
    {
        DB::table('testimonial')->insert([
            'name'      => 'Judith Doe',
            'company'   => 'Company name',
            'text'      => 'Lorem ipsum dolor sit amet consectetur adipiscing elit nullam  vestibulum lacinia nisi in pellentesque duis sagittis arcu nec semper tristique',
            'order'     => '1',
        ]);
        DB::table('testimonial')->insert([
            'name'      => 'Claire Thompson',
            'company'   => 'Company name',
            'text'      => 'Nulla ac pulvinar odio ac pellentesque nulla ut non tortor non urna porttitor congue sed eget leo maximus posuere dui',
            'order'     => '2',
        ]);
        DB::table('testimonial')->insert([
            'name'      => 'Jennifer Stop',
            'company'   => 'Company name',
            'text'      => 'Fusce malesuada mollis tellus sed posuere sed felis ligula interdum vitae eleifend sit amet blandit id risus cras nibh lectus',
            'order'     => '3',
        ]);
    }
}
