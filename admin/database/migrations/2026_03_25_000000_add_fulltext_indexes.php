<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->fullText(['title', 'description'], 'articles_fulltext');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->fullText(['title', 'description'], 'projects_fulltext');
        });

        Schema::table('github_activity', function (Blueprint $table) {
            $table->fullText(['repo', 'title'], 'github_activity_fulltext');
        });

        Schema::table('experience', function (Blueprint $table) {
            $table->fullText(['title', 'description'], 'experience_fulltext');
        });

        Schema::table('service', function (Blueprint $table) {
            $table->fullText(['title', 'description'], 'service_fulltext');
        });

        Schema::table('testimonial', function (Blueprint $table) {
            $table->fullText(['name', 'company', 'description'], 'testimonial_fulltext');
        });

        Schema::table('skill', function (Blueprint $table) {
            $table->fullText(['title'], 'skill_fulltext');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropFullText('articles_fulltext');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropFullText('projects_fulltext');
        });

        Schema::table('github_activity', function (Blueprint $table) {
            $table->dropFullText('github_activity_fulltext');
        });

        Schema::table('experience', function (Blueprint $table) {
            $table->dropFullText('experience_fulltext');
        });

        Schema::table('service', function (Blueprint $table) {
            $table->dropFullText('service_fulltext');
        });

        Schema::table('testimonial', function (Blueprint $table) {
            $table->dropFullText('testimonial_fulltext');
        });

        Schema::table('skill', function (Blueprint $table) {
            $table->dropFullText('skill_fulltext');
        });
    }
};
