<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLapsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('laps', function (Blueprint $table) {
      $table->increments('id');
      $table->integer('activity_id')->unsigned();
      $table->bigInteger('strava_id')->unsigned()->unique();
      $table->string('name');
      $table->integer('elapsed_time')->unsigned();
      $table->integer('moving_time')->unsigned();
      $table->timestamp('start_date_local');
      $table->double('distance')->unsigned();
      $table->integer('start_index')->unsigned();
      $table->integer('end_index')->unsigned();
      $table->double('average_speed')->unsigned();
      $table->double('max_speed');
      $table->double('average_cadence')->unsigned()->nullable();
      $table->double('average_heartrate')->unsigned()->nullable();
      $table->double('max_heartrate')->unsigned()->nullable();
      $table->integer('lap_index')->unsigned();
      $table->integer('split')->unsigned();
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
    Schema::table('laps', function(Blueprint $table) {
      $table->dropForeign('laps_activity_id_foreign');
    });
    Schema::dropIfExists('laps');
  }
}
