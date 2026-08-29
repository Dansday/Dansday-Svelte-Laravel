<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('linkedin_posts', function (Blueprint $table) {
            $table->id();
            $table->string('urn', 128)->unique();
            $table->unsignedBigInteger('article_id')->nullable()->index();
            $table->string('media_type', 24)->default('text');
            $table->string('visibility', 24)->default('PUBLIC');
            $table->text('commentary');
            $table->timestamp('posted_at')->nullable();
            $table->timestamp('edited_at')->nullable();
            $table->timestamp('deleted_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('linkedin_posts');
    }
};
