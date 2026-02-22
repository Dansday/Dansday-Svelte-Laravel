<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePageSectionTable extends Migration
{
    public function up()
    {
        Schema::create('page_section', function (Blueprint $table) {
            $table->id();

            $table->boolean('about_enable');
            $table->unsignedSmallInteger('about_experience_order')->default(0);
            $table->unsignedSmallInteger('about_services_order')->default(0);
            $table->unsignedSmallInteger('about_skills_order')->default(0);
            $table->unsignedSmallInteger('about_testimonial_order')->default(0);
            $table->boolean('experience_enable');
            $table->boolean('skills_enable');
            $table->boolean('testimonial_enable');
            $table->boolean('services_enable');
            $table->boolean('projects_enable');
            $table->boolean('articles_enable');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('page_section');
    }
}
