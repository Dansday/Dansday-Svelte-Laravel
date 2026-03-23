<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('page_setting', function (Blueprint $table) {
            $table->string('ai_terminal_reasoning', 50)->nullable()->after('ai_terminal_prompt');
            $table->string('ai_content_reasoning', 50)->nullable()->after('ai_content_prompt');
        });
    }

    public function down(): void
    {
        Schema::table('page_setting', function (Blueprint $table) {
            $table->dropColumn(['ai_terminal_reasoning', 'ai_content_reasoning']);
        });
    }
};
