<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('page_setting', function (Blueprint $table) {
            $table->text('ai_project_prompt')->nullable()->after('ai_content_reasoning');
            $table->string('ai_project_reasoning', 20)->nullable()->after('ai_project_prompt');
        });
    }

    public function down(): void
    {
        Schema::table('page_setting', function (Blueprint $table) {
            $table->dropColumn(['ai_project_prompt', 'ai_project_reasoning']);
        });
    }
};
