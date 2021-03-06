<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEffortsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('efforts', function (Blueprint $table) {
      $table->increments('id');
      $table->integer('activity_id')->unsigned();
      $table->bigInteger('strava_id')->unsigned()->unique();
      $table->string('name');
      $table->integer('elapsed_time')->unsigned();
      $table->integer('moving_time')->unsigned();
      $table->double('distance')->unsigned();
      $table->timestamp('start_date_local');
      $table->integer('start_index')->unsigned();
      $table->integer('end_index')->unsigned();
      $table->integer('pr_rank')->unsigned()->nullable();
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
    Schema::table('efforts', function(Blueprint $table) {
      $table->dropForeign('efforts_activity_id_foreign');
    });
    Schema::dropIfExists('efforts');
  }
}
