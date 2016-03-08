<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRivetablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rivetables', function (Blueprint $table) {
            $table->integer('rivet_id')->unsigned()->index();
            $table->integer('rivetable_id')->unsigned()->index();
            $table->string('rivetable_type')->index();
            $table->string('collection')->index();
            $table->integer('position')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('rivetables');
    }
}
