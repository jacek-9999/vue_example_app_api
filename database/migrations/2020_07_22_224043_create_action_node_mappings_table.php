<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActionNodeMappingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('action_node_mappings', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->bigInteger('goto_id')->unsigned();
            $table->foreign('goto_id')->references('id')->on('action_nodes');
            $table->bigInteger('option_id')->unsigned();
            $table->foreign('option_id')->references('id')->on('action_node_options');
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
        Schema::dropIfExists('action_node_mappings');
    }
}
