<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLocationsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('locations', function (Blueprint $table) {
      $table->increments('id');
      $table->integer('activity_id')->unsigned();
      $table->double('start_lat', 9, 6);
      $table->double('start_lng', 9, 6);
      $table->double('end_lat', 9, 6);
      $table->double('end_lng', 9, 6);
      $table->string('map_id');
      $table->string('polyline', 8000);
      $table->string('summary_polyline', 2000);
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
    Schema::table('locations', function(Blueprint $table) {
      $table->dropForeign('locations_activity_id_foreign');
    });
    Schema::dropIfExists('locations');
  }
}
