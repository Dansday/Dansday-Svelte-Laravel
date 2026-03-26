<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('page_setting', function (Blueprint $table) {
            $table->renameColumn('ai_content_prompt', 'ai_article_prompt');
            $table->renameColumn('ai_content_reasoning', 'ai_article_reasoning');
        });
    }

    public function down(): void
    {
        Schema::table('page_setting', function (Blueprint $table) {
            $table->renameColumn('ai_article_prompt', 'ai_content_prompt');
            $table->renameColumn('ai_article_reasoning', 'ai_content_reasoning');
        });
    }
};
