<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use JWTAuth;
use \Carbon\Carbon;
use App\User;
use App\Activity;
use App\Effort;
use App\Board;
use App\Jobs\GetStravaActivities;

class ActivityController extends BaseController
{
  public function syncData(User $user) {
    $before = Carbon::now();
    $lastMonth = new Carbon('first day of last month');
    $after = new Carbon($lastMonth->format('Y-m-d'));
    
    GetStravaActivities::dispatch($user, $after->format('U'), $before->format('U'));
    
    return $this->respond(true);
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
      
      GetStravaActivities::dispatch($user, $after->format('U'), $before->format('U'));
    }

    return $this->respond(true);
  }
}
