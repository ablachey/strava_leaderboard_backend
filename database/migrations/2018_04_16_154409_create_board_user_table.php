<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBoardUserTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('board_user', function (Blueprint $table) {
      $table->increments('id');
      $table->integer('board_id')->unsigned();
      $table->integer('user_id')->unsigned();
      $table->boolean('active')->default(false);
      $table->boolean('admin')->default(false);
      $table->timestamps();

      $table->foreign('board_id')->references('id')->on('boards')->onUpdate('CASCADE')->onDelete('CASCADE');
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
    Schema::table('board_user', function(Blueprint $table) {
      $table->dropForeign('board_user_board_id_foreign');
      $table->dropForeign('board_user_user_id_foreign');
    });
    Schema::dropIfExists('board_user');
  }
}
