<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestimonialTable extends Migration
{
    public function up()
    {
        Schema::create('testimonial', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('company');
            $table->text('text');
            $table->integer('order');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('testimonial');
    }
}
