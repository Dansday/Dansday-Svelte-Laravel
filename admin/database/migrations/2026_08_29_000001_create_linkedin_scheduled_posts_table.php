<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('linkedin_scheduled_posts', function (Blueprint $table) {
            $table->id();
            $table->text('commentary');
            $table->unsignedBigInteger('article_id')->nullable()->index();
            $table->string('status', 16)->default('pending')->index();
            $table->timestamp('publish_at')->index();
            $table->json('payload');
            $table->unsignedInteger('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->unsignedBigInteger('linkedin_post_id')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('linkedin_scheduled_posts');
    }
};
