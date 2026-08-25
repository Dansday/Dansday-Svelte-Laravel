<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceTable extends Migration
{
    public function up()
    {
        Schema::create('service', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->text('info');
            $table->integer('order');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('service');
    }
}
