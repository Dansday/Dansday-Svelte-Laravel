<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectSeeder extends Seeder
{
    public function run()
    {
        DB::table('project')->insert([
            'enable'        => 1,
            'title'         => 'Lorem ipsum dolor sit amet adipiscing',
            'short_desc'    => 'Lorem ipsum dolor sit amet, consectetur adicing elit pellente enim leo ipsum.',
            'images_code'   => 'project_001',
            'description'   => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec a ligula pellentesque, malesuada orci cursus, lacinia sem. In in rutrum enim. Nam sed lectus ac nulla molestie maximus. Fusce aliquet nulla ut sapien volutpat luctus. Interdum et malesuada fames ac ante ipsum primis in faucibus. Nullam cursus odio interdum facilisis posuere.</p>
            <blockquote class="blockquote">Phasellus sit amet nulla quis odio egestas dictum. Aenean accumsan, felis a blandit eleifend, neque urna lacinia neque, vitae molestie mi est quis felis. Etiam blandit dui non ullamcorper pharetra. Donec eget efficitur leo.</blockquote>
            <p>Duis sagittis ex lectus, ac tincidunt libero viverra ullamcorper. Mauris sapien nisl, interdum non lectus eu, tempor fermentum lacus. Fusce cursus ipsum facilisis neque tempor tempus. Ut suscipit, nunc sed tristique luctus, elit tortor semper orci, non sodales turpis felis a purus. Aliquam nibh odio, sollicitudin eget vestibulum ut, euismod non turpis.</p>
            <ul>
            <li>Praesent imperdiet lorem sapien.</li>
            <li>Sed et finibus urna. Integer ante est.</li>
            <li>Placerat ac dui et, vehicula cursus nisi.</li>
            <li>Ut fermentum mauris dictum.</li>
            <li>Aenean ultricies mauris vitae.</li>
            </ul>',
            'image'         => 'demo/img/projects/project_image_8708.jpg',
            'order'         => 1,
            'category_id'   => 1,
        ]);

        DB::table('project')->insert([
            'enable'        => 1,
            'title'         => 'Lorem ipsum dolor sit amet adipiscing',
            'short_desc'    => 'Lorem ipsum dolor sit amet, consectetur adicing elit pellente enim leo ipsum.',
            'images_code'   => 'project_002',
            'description'   => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec a ligula pellentesque, malesuada orci cursus, lacinia sem. In in rutrum enim. Nam sed lectus ac nulla molestie maximus. Fusce aliquet nulla ut sapien volutpat luctus. Interdum et malesuada fames ac ante ipsum primis in faucibus. Nullam cursus odio interdum facilisis posuere.</p>
            <blockquote class="blockquote">Phasellus sit amet nulla quis odio egestas dictum. Aenean accumsan, felis a blandit eleifend, neque urna lacinia neque, vitae molestie mi est quis felis. Etiam blandit dui non ullamcorper pharetra. Donec eget efficitur leo.</blockquote>
            <p>Duis sagittis ex lectus, ac tincidunt libero viverra ullamcorper. Mauris sapien nisl, interdum non lectus eu, tempor fermentum lacus. Fusce cursus ipsum facilisis neque tempor tempus. Ut suscipit, nunc sed tristique luctus, elit tortor semper orci, non sodales turpis felis a purus. Aliquam nibh odio, sollicitudin eget vestibulum ut, euismod non turpis.</p>
            <ul>
            <li>Praesent imperdiet lorem sapien.</li>
            <li>Sed et finibus urna. Integer ante est.</li>
            <li>Placerat ac dui et, vehicula cursus nisi.</li>
            <li>Ut fermentum mauris dictum.</li>
            <li>Aenean ultricies mauris vitae.</li>
            </ul>',
            'image'         => 'demo/img/projects/project_image_3833.jpg',
            'order'         => 2,
            'category_id'   => 3,
        ]);

        DB::table('project')->insert([
            'enable'        => 1,
            'title'         => 'Lorem ipsum dolor sit amet adipiscing',
            'short_desc'    => 'Lorem ipsum dolor sit amet, consectetur adicing elit pellente enim leo ipsum.',
            'images_code'   => 'project_003',
            'description'   => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec a ligula pellentesque, malesuada orci cursus, lacinia sem. In in rutrum enim. Nam sed lectus ac nulla molestie maximus. Fusce aliquet nulla ut sapien volutpat luctus. Interdum et malesuada fames ac ante ipsum primis in faucibus. Nullam cursus odio interdum facilisis posuere.</p>
            <blockquote class="blockquote">Phasellus sit amet nulla quis odio egestas dictum. Aenean accumsan, felis a blandit eleifend, neque urna lacinia neque, vitae molestie mi est quis felis. Etiam blandit dui non ullamcorper pharetra. Donec eget efficitur leo.</blockquote>
            <p>Duis sagittis ex lectus, ac tincidunt libero viverra ullamcorper. Mauris sapien nisl, interdum non lectus eu, tempor fermentum lacus. Fusce cursus ipsum facilisis neque tempor tempus. Ut suscipit, nunc sed tristique luctus, elit tortor semper orci, non sodales turpis felis a purus. Aliquam nibh odio, sollicitudin eget vestibulum ut, euismod non turpis.</p>
            <ul>
            <li>Praesent imperdiet lorem sapien.</li>
            <li>Sed et finibus urna. Integer ante est.</li>
            <li>Placerat ac dui et, vehicula cursus nisi.</li>
            <li>Ut fermentum mauris dictum.</li>
            <li>Aenean ultricies mauris vitae.</li>
            </ul>',
            'image'         => 'demo/img/projects/project_image_5551.jpg',
            'order'         => 3,
            'category_id'   => 2,
        ]);

        DB::table('project')->insert([
            'enable'        => 1,
            'title'         => 'Lorem ipsum dolor sit amet adipiscing',
            'short_desc'    => 'Lorem ipsum dolor sit amet, consectetur adicing elit pellente enim leo ipsum.',
            'images_code'   => 'project_004',
            'description'   => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec a ligula pellentesque, malesuada orci cursus, lacinia sem. In in rutrum enim. Nam sed lectus ac nulla molestie maximus. Fusce aliquet nulla ut sapien volutpat luctus. Interdum et malesuada fames ac ante ipsum primis in faucibus. Nullam cursus odio interdum facilisis posuere.</p>
            <blockquote class="blockquote">Phasellus sit amet nulla quis odio egestas dictum. Aenean accumsan, felis a blandit eleifend, neque urna lacinia neque, vitae molestie mi est quis felis. Etiam blandit dui non ullamcorper pharetra. Donec eget efficitur leo.</blockquote>
            <p>Duis sagittis ex lectus, ac tincidunt libero viverra ullamcorper. Mauris sapien nisl, interdum non lectus eu, tempor fermentum lacus. Fusce cursus ipsum facilisis neque tempor tempus. Ut suscipit, nunc sed tristique luctus, elit tortor semper orci, non sodales turpis felis a purus. Aliquam nibh odio, sollicitudin eget vestibulum ut, euismod non turpis.</p>
            <ul>
            <li>Praesent imperdiet lorem sapien.</li>
            <li>Sed et finibus urna. Integer ante est.</li>
            <li>Placerat ac dui et, vehicula cursus nisi.</li>
            <li>Ut fermentum mauris dictum.</li>
            <li>Aenean ultricies mauris vitae.</li>
            </ul>',
            'image'         => 'demo/img/projects/project_image_168.jpg',
            'order'         => 4,
            'category_id'   => 1,
        ]);

        DB::table('project')->insert([
            'enable'        => 1,
            'title'         => 'Lorem ipsum dolor sit amet adipiscing',
            'short_desc'    => 'Lorem ipsum dolor sit amet, consectetur adicing elit pellente enim leo ipsum.',
            'images_code'   => 'project_005',
            'description'   => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec a ligula pellentesque, malesuada orci cursus, lacinia sem. In in rutrum enim. Nam sed lectus ac nulla molestie maximus. Fusce aliquet nulla ut sapien volutpat luctus. Interdum et malesuada fames ac ante ipsum primis in faucibus. Nullam cursus odio interdum facilisis posuere.</p>
            <blockquote class="blockquote">Phasellus sit amet nulla quis odio egestas dictum. Aenean accumsan, felis a blandit eleifend, neque urna lacinia neque, vitae molestie mi est quis felis. Etiam blandit dui non ullamcorper pharetra. Donec eget efficitur leo.</blockquote>
            <p>Duis sagittis ex lectus, ac tincidunt libero viverra ullamcorper. Mauris sapien nisl, interdum non lectus eu, tempor fermentum lacus. Fusce cursus ipsum facilisis neque tempor tempus. Ut suscipit, nunc sed tristique luctus, elit tortor semper orci, non sodales turpis felis a purus. Aliquam nibh odio, sollicitudin eget vestibulum ut, euismod non turpis.</p>
            <ul>
            <li>Praesent imperdiet lorem sapien.</li>
            <li>Sed et finibus urna. Integer ante est.</li>
            <li>Placerat ac dui et, vehicula cursus nisi.</li>
            <li>Ut fermentum mauris dictum.</li>
            <li>Aenean ultricies mauris vitae.</li>
            </ul>',
            'image'         => 'demo/img/projects/project_image_5784.jpg',
            'order'         => 5,
            'category_id'   => 3,
        ]);

        DB::table('project')->insert([
            'enable'        => 1,
            'title'         => 'Lorem ipsum dolor sit amet adipiscing',
            'short_desc'    => 'Lorem ipsum dolor sit amet, consectetur adicing elit pellente enim leo ipsum.',
            'images_code'   => 'project_006',
            'description'   => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec a ligula pellentesque, malesuada orci cursus, lacinia sem. In in rutrum enim. Nam sed lectus ac nulla molestie maximus. Fusce aliquet nulla ut sapien volutpat luctus. Interdum et malesuada fames ac ante ipsum primis in faucibus. Nullam cursus odio interdum facilisis posuere.</p>
            <blockquote class="blockquote">Phasellus sit amet nulla quis odio egestas dictum. Aenean accumsan, felis a blandit eleifend, neque urna lacinia neque, vitae molestie mi est quis felis. Etiam blandit dui non ullamcorper pharetra. Donec eget efficitur leo.</blockquote>
            <p>Duis sagittis ex lectus, ac tincidunt libero viverra ullamcorper. Mauris sapien nisl, interdum non lectus eu, tempor fermentum lacus. Fusce cursus ipsum facilisis neque tempor tempus. Ut suscipit, nunc sed tristique luctus, elit tortor semper orci, non sodales turpis felis a purus. Aliquam nibh odio, sollicitudin eget vestibulum ut, euismod non turpis.</p>
            <ul>
            <li>Praesent imperdiet lorem sapien.</li>
            <li>Sed et finibus urna. Integer ante est.</li>
            <li>Placerat ac dui et, vehicula cursus nisi.</li>
            <li>Ut fermentum mauris dictum.</li>
            <li>Aenean ultricies mauris vitae.</li>
            </ul>',
            'image'         => 'demo/img/projects/project_image_5330.jpg',
            'order'         => 6,
            'category_id'   => 2,
        ]);

        DB::table('project')->insert([
            'enable'        => 1,
            'title'         => 'Lorem ipsum dolor sit amet adipiscing',
            'short_desc'    => 'Lorem ipsum dolor sit amet, consectetur adicing elit pellente enim leo ipsum.',
            'images_code'   => 'project_007',
            'description'   => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec a ligula pellentesque, malesuada orci cursus, lacinia sem. In in rutrum enim. Nam sed lectus ac nulla molestie maximus. Fusce aliquet nulla ut sapien volutpat luctus. Interdum et malesuada fames ac ante ipsum primis in faucibus. Nullam cursus odio interdum facilisis posuere.</p>
            <blockquote class="blockquote">Phasellus sit amet nulla quis odio egestas dictum. Aenean accumsan, felis a blandit eleifend, neque urna lacinia neque, vitae molestie mi est quis felis. Etiam blandit dui non ullamcorper pharetra. Donec eget efficitur leo.</blockquote>
            <p>Duis sagittis ex lectus, ac tincidunt libero viverra ullamcorper. Mauris sapien nisl, interdum non lectus eu, tempor fermentum lacus. Fusce cursus ipsum facilisis neque tempor tempus. Ut suscipit, nunc sed tristique luctus, elit tortor semper orci, non sodales turpis felis a purus. Aliquam nibh odio, sollicitudin eget vestibulum ut, euismod non turpis.</p>
            <ul>
            <li>Praesent imperdiet lorem sapien.</li>
            <li>Sed et finibus urna. Integer ante est.</li>
            <li>Placerat ac dui et, vehicula cursus nisi.</li>
            <li>Ut fermentum mauris dictum.</li>
            <li>Aenean ultricies mauris vitae.</li>
            </ul>',
            'image'         => 'demo/img/projects/project_image_6487.jpg',
            'order'         => 7,
            'category_id'   => 1,
        ]);

        DB::table('project')->insert([
            'enable'        => 1,
            'title'         => 'Lorem ipsum dolor sit amet adipiscing',
            'short_desc'    => 'Lorem ipsum dolor sit amet, consectetur adicing elit pellente enim leo ipsum.',
            'images_code'   => 'project_008',
            'description'   => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec a ligula pellentesque, malesuada orci cursus, lacinia sem. In in rutrum enim. Nam sed lectus ac nulla molestie maximus. Fusce aliquet nulla ut sapien volutpat luctus. Interdum et malesuada fames ac ante ipsum primis in faucibus. Nullam cursus odio interdum facilisis posuere.</p>
            <blockquote class="blockquote">Phasellus sit amet nulla quis odio egestas dictum. Aenean accumsan, felis a blandit eleifend, neque urna lacinia neque, vitae molestie mi est quis felis. Etiam blandit dui non ullamcorper pharetra. Donec eget efficitur leo.</blockquote>
            <p>Duis sagittis ex lectus, ac tincidunt libero viverra ullamcorper. Mauris sapien nisl, interdum non lectus eu, tempor fermentum lacus. Fusce cursus ipsum facilisis neque tempor tempus. Ut suscipit, nunc sed tristique luctus, elit tortor semper orci, non sodales turpis felis a purus. Aliquam nibh odio, sollicitudin eget vestibulum ut, euismod non turpis.</p>
            <ul>
            <li>Praesent imperdiet lorem sapien.</li>
            <li>Sed et finibus urna. Integer ante est.</li>
            <li>Placerat ac dui et, vehicula cursus nisi.</li>
            <li>Ut fermentum mauris dictum.</li>
            <li>Aenean ultricies mauris vitae.</li>
            </ul>',
            'image'         => 'demo/img/projects/project_image_9837.jpg',
            'order'         => 8,
            'category_id'   => 3,
        ]);

        DB::table('project')->insert([
            'enable'        => 1,
            'title'         => 'Lorem ipsum dolor sit amet adipiscing',
            'short_desc'    => 'Lorem ipsum dolor sit amet, consectetur adicing elit pellente enim leo ipsum.',
            'images_code'   => 'project_009',
            'description'   => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec a ligula pellentesque, malesuada orci cursus, lacinia sem. In in rutrum enim. Nam sed lectus ac nulla molestie maximus. Fusce aliquet nulla ut sapien volutpat luctus. Interdum et malesuada fames ac ante ipsum primis in faucibus. Nullam cursus odio interdum facilisis posuere.</p>
            <blockquote class="blockquote">Phasellus sit amet nulla quis odio egestas dictum. Aenean accumsan, felis a blandit eleifend, neque urna lacinia neque, vitae molestie mi est quis felis. Etiam blandit dui non ullamcorper pharetra. Donec eget efficitur leo.</blockquote>
            <p>Duis sagittis ex lectus, ac tincidunt libero viverra ullamcorper. Mauris sapien nisl, interdum non lectus eu, tempor fermentum lacus. Fusce cursus ipsum facilisis neque tempor tempus. Ut suscipit, nunc sed tristique luctus, elit tortor semper orci, non sodales turpis felis a purus. Aliquam nibh odio, sollicitudin eget vestibulum ut, euismod non turpis.</p>
            <ul>
            <li>Praesent imperdiet lorem sapien.</li>
            <li>Sed et finibus urna. Integer ante est.</li>
            <li>Placerat ac dui et, vehicula cursus nisi.</li>
            <li>Ut fermentum mauris dictum.</li>
            <li>Aenean ultricies mauris vitae.</li>
            </ul>',
            'image'         => 'demo/img/projects/project_image_2983.jpg',
            'order'         => 9,
            'category_id'   => 2,
        ]);

        DB::table('project')->insert([
            'enable'        => 1,
            'title'         => 'Lorem ipsum dolor sit amet adipiscing',
            'short_desc'    => 'Lorem ipsum dolor sit amet, consectetur adicing elit pellente enim leo ipsum.',
            'images_code'   => 'project_010',
            'description'   => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec a ligula pellentesque, malesuada orci cursus, lacinia sem. In in rutrum enim. Nam sed lectus ac nulla molestie maximus. Fusce aliquet nulla ut sapien volutpat luctus. Interdum et malesuada fames ac ante ipsum primis in faucibus. Nullam cursus odio interdum facilisis posuere.</p>
            <blockquote class="blockquote">Phasellus sit amet nulla quis odio egestas dictum. Aenean accumsan, felis a blandit eleifend, neque urna lacinia neque, vitae molestie mi est quis felis. Etiam blandit dui non ullamcorper pharetra. Donec eget efficitur leo.</blockquote>
            <p>Duis sagittis ex lectus, ac tincidunt libero viverra ullamcorper. Mauris sapien nisl, interdum non lectus eu, tempor fermentum lacus. Fusce cursus ipsum facilisis neque tempor tempus. Ut suscipit, nunc sed tristique luctus, elit tortor semper orci, non sodales turpis felis a purus. Aliquam nibh odio, sollicitudin eget vestibulum ut, euismod non turpis.</p>
            <ul>
            <li>Praesent imperdiet lorem sapien.</li>
            <li>Sed et finibus urna. Integer ante est.</li>
            <li>Placerat ac dui et, vehicula cursus nisi.</li>
            <li>Ut fermentum mauris dictum.</li>
            <li>Aenean ultricies mauris vitae.</li>
            </ul>',
            'image'         => 'demo/img/projects/project_image_896.jpg',
            'order'         => 10,
            'category_id'   => 1,
        ]);

        DB::table('project')->insert([
            'enable'        => 1,
            'title'         => 'Lorem ipsum dolor sit amet adipiscing',
            'short_desc'    => 'Lorem ipsum dolor sit amet, consectetur adicing elit pellente enim leo ipsum.',
            'images_code'   => 'project_011',
            'description'   => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec a ligula pellentesque, malesuada orci cursus, lacinia sem. In in rutrum enim. Nam sed lectus ac nulla molestie maximus. Fusce aliquet nulla ut sapien volutpat luctus. Interdum et malesuada fames ac ante ipsum primis in faucibus. Nullam cursus odio interdum facilisis posuere.</p>
            <blockquote class="blockquote">Phasellus sit amet nulla quis odio egestas dictum. Aenean accumsan, felis a blandit eleifend, neque urna lacinia neque, vitae molestie mi est quis felis. Etiam blandit dui non ullamcorper pharetra. Donec eget efficitur leo.</blockquote>
            <p>Duis sagittis ex lectus, ac tincidunt libero viverra ullamcorper. Mauris sapien nisl, interdum non lectus eu, tempor fermentum lacus. Fusce cursus ipsum facilisis neque tempor tempus. Ut suscipit, nunc sed tristique luctus, elit tortor semper orci, non sodales turpis felis a purus. Aliquam nibh odio, sollicitudin eget vestibulum ut, euismod non turpis.</p>
            <ul>
            <li>Praesent imperdiet lorem sapien.</li>
            <li>Sed et finibus urna. Integer ante est.</li>
            <li>Placerat ac dui et, vehicula cursus nisi.</li>
            <li>Ut fermentum mauris dictum.</li>
            <li>Aenean ultricies mauris vitae.</li>
            </ul>',
            'image'         => 'demo/img/projects/project_image_9860.jpg',
            'order'         => 11,
            'category_id'   => 3,
        ]);
    }
}
