<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('swipes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->bigInteger('author_id')->unsigned();
            $table->foreign('author_id')->on('users')->references('id')->cascadeOnDelete();

            $table->bigInteger('receiver_id')->unsigned();
            $table->foreign('receiver_id')->on('users')->references('id')->cascadeOnDelete();

            $table->enum('attitude', ['LIKE', 'DISLIKE']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('swipes');
    }
};
