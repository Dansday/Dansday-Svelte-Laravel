<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExperienceTable extends Migration
{
    public function up()
    {
        Schema::create('experience', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('period');
            $table->string('title');
            $table->text('description');
            $table->integer('order');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('experience');
    }
}
