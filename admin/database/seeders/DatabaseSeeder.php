<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call(UserSeeder::class);
        $this->call(HomeSeeder::class);
        $this->call(SectionSeeder::class);
        $this->call(SettingSeeder::class);
        $this->call(CategoriesSeeder::class);
        $this->call(SkillSeeder::class);
        $this->call(ExperienceSeeder::class);
        $this->call(TestimonialSeeder::class);
        $this->call(ServiceSeeder::class);
        $this->call(ProjectCategorySeeder::class);
        $this->call(ProjectSeeder::class);
        $this->call(ProjectGallerySeeder::class);
        $this->call(ArticlesSeeder::class);
        $this->call(ArticleGallerySeeder::class);
    }
}
