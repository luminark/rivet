<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class CreateAttachablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attachables', function (Blueprint $table) {
            $table->integer('attachment_id')->unsigned();
            $table->integer('attachable_id')->unsigned();
            $table->string('attachable_type');
            $table->integer('position')->unsigned();
            $table->string('collection');
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('attachables');
    }
}