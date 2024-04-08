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
        Schema::create('planted_trees', function (Blueprint $table){
            $table -> id();
            $table->foreignId('exercise_id')->constrained('planting_exercises')->onDelete('cascade');
            $table->string('planter');
            $table->foreign('planter')->references('tsc_no')->on('users');
            $table->string('tree name');
            //$table->time('time');
            $table->string('photo');
            $table->integer('no_of_trees')->default(0);
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
        Schema::dropIfExists('planted trees');
    }
};
