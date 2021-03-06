<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActionNodeOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('action_node_options', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->bigInteger('target_id')->unsigned()->nullable();
            $table->bigInteger('node_id')->unsigned()->nullable();
            $table->foreign('node_id')->references('id')->on('action_nodes');
            $table->integer('description_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('action_node_options');
    }
}
