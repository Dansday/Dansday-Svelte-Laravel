<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectTable extends Migration
{
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->boolean('enable');
            $table->string('title');
            $table->text('short_desc');
            $table->text('description');
            $table->text('image')->nullable();

            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')->references('id')->on('project_categories');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('projects');
    }
}
