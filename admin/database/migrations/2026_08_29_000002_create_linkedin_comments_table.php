<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('linkedin_comments', function (Blueprint $table) {
            $table->id();
            $table->string('urn', 191)->unique();
            $table->string('post_urn', 191)->index();
            $table->unsignedBigInteger('linkedin_post_id')->nullable()->index();
            $table->string('parent_comment_urn', 191)->nullable();
            $table->text('text');
            $table->timestamp('edited_at')->nullable();
            $table->timestamp('deleted_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('linkedin_comments');
    }
};
