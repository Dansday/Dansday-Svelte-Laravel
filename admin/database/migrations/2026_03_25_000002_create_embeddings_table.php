<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('embeddings', function (Blueprint $table) {
            $table->id();
            $table->string('table_name', 50);
            $table->unsignedBigInteger('row_id');
            $table->string('content_hash', 64);
            $table->json('vector');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['table_name', 'row_id']);
            $table->index('table_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('embeddings');
    }
};
