<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use JWTAuth;
use \Carbon\Carbon;
use App\Synchronizer;
use App\User;
use App\Activity;
use App\Effort;
use App\Board;

class ActivityController extends BaseController
{
  public function syncData() {
    $before = Carbon::now();
    $lastMonth = new Carbon('first day of last month');
    $after = new Carbon($lastMonth->format('Y-m-d'));
    
    $syncObj = new Synchronizer($this->getUser(), $after->format('U'), $before->format('U'));
    
    return $this->respond($syncObj->sync());
  }

  public function syncBoardData($id) {
    $board = Board::find($id);

    if(!$board) {
      return $this->respondWithNotFound();
    }

    $before = Carbon::now();
    $users = $board->users()->get();

    foreach($users as $user) {
      $lastActivity = $user->activities()->orderBy('start_date_local', 'desc')->first();

      $lastMonth = new Carbon('first day of last month');
      $after = new Carbon($lastMonth->format('Y-m-d'));

      if($lastActivity) {
        $after = new Carbon($lastActivity->start_date_local);
      }
      
      $synchronizer = new Synchronizer($user, $after->format('U'), $before->format('U'));
      $synchronizer->sync();
    }

    return $this->respond(true);
  }
}
