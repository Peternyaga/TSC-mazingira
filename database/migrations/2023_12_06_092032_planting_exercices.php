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
        Schema::create('planting_exercises', function (Blueprint $table){
            $table->id();
            $table->string('exercise name')->unique();
            $table->string('initiator');
            $table->string('location');
            $table->string('initiator contact');
            $table->integer('target seedlings');
            $table->integer('planted seedlings')->default(0);
            $table->integer('deviation');
            $table->timestamp('start')->nullable();
            $table->timestamp('end')->nullable();
            $table->integer('participants')->default(0);
            $table->integer('status')->default(0);
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
        //
    }
};
