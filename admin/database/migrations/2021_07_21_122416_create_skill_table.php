<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSkillTable extends Migration
{
    public function up()
    {
        Schema::create('skill', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('title');
            $table->integer('order');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('skill');
    }
}
