<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActivitiesTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('activities', function (Blueprint $table) {
      $table->increments('id');
      $table->bigInteger('strava_id')->unsigned()->unique();
      $table->integer('user_id')->unsigned();
      $table->string('name');
      $table->double('distance')->unsigned();
      $table->integer('moving_time')->unsigned();
      $table->integer('elapsed_time')->unsigned();
      $table->string('type');
      $table->timestamp('start_date_local');
      $table->boolean('has_heartrate');
      $table->double('average_heartrate')->nullable();
      $table->double('max_heartrate')->nullable();
      $table->double('calories');
      $table->timestamps();

      $table->foreign('user_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::table('activities', function(Blueprint $table) {
      $table->dropForeign('activities_user_id_foreign');
    });
    Schema::dropIfExists('activities');
  }
}
