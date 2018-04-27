<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSplitsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('splits', function (Blueprint $table) {
      $table->increments('id');
      $table->integer('activity_id')->unsigned();
      $table->double('distance')->unsigned();
      $table->integer('elapsed_time')->unsigned();
      $table->integer('moving_time')->unsigned();
      $table->integer('split')->unsigned();
      $table->double('average_heartrate')->unsigned()->nullable();
      $table->double('average_speed')->unsigned();
      $table->timestamps();

      $table->foreign('activity_id')->references('id')->on('activities')->onUpdate('CASCADE')->onDelete('CASCADE');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::table('splits', function(Blueprint $table) {
      $table->dropForeign('splits_activity_id_foreign');
    });
    Schema::dropIfExists('splits');
  }
}
