<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('page_setting', function (Blueprint $table) {
            $table->string('embedding_url', 500)->nullable()->after('ai_content_reasoning');
            $table->string('embedding_key', 500)->nullable()->after('embedding_url');
            $table->string('embedding_model', 255)->nullable()->after('embedding_key');
        });
    }

    public function down(): void
    {
        Schema::table('page_setting', function (Blueprint $table) {
            $table->dropColumn(['embedding_url', 'embedding_key', 'embedding_model']);
        });
    }
};
