<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticlesTable extends Migration
{
    public function up()
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('short_desc')->nullable();
            $table->text('text');
            $table->text('image')->nullable();
            $table->string('author');
            $table->string('slug');
            $table->string('status');
            $table->string('images_code');
            $table->integer('order');

            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')->references('id')->on('article_category');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('articles');
    }
}
