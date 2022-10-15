<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVideoWalksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('video_walks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('city');
            $table->tinyInteger('state')->default(0);
            $table->longText('videoUrl')->nullable();
            $table->longText('streamUrl')->nullable();
            $table->string('muxId')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('video_walks');
    }
}
