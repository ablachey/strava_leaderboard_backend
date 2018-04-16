<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\BoardRequest;
use App\Board;
use App\Http\Resources\Api\V1\BoardResource;
use App\Http\Requests\Api\V1\CardRequest;
use App\Activity;
use App\Effort;
use \Carbon\Carbon;
use App\Http\Resources\Api\V1\UserResource;
use App\Http\Resources\Api\V1\EffortResource;
use App\Http\Requests\Api\V1\BoardSearchRequest;
use App\Http\Requests\Api\V1\BoardJoinRequest;

class BoardController extends BaseController
{
  public function show($id) {
    $board = Board::find($id);

    if(!$board) {
      return $this->respondWithNotFound();
    }
    $boardUsers = $board->users()->wherePivot('active', true)->get();

    if(!$boardUsers->contains('id', $this->getUser()->id)) {
      return $this->respondWithError(null, 403, 'forbidden');
    }

    return $this->respond(BoardResource::make($board));
  }

  public function search(BoardSearchRequest $request) {
    $boards = Board::where('name', 'LIKE', "%$request->keyword%")->orderBy('name', 'asc')->get();
    return $this->respond($boards);
  }

  public function store(BoardRequest $request) {
    $board = Board::create($request->all());
    $board->users()->save($this->getUser(), ['active' => true, 'admin' => true]);

    return $this->respond($board);
  }

  public function join(BoardJoinRequest $request) {
    $board = Board::find($request->board_id);

    if(!$board) {
      return $this->respondWithNotFound();
    }

    $user = $this->getUser();
    $exists = $board->users()->where('user_id', $user->id)->first();

    if($exists) {
      return $this->respondWithError(null, 422, ['user' => ['Already joined']]);
    }

    if($board->users()->save($user, ['active' => false, 'admin' => false])) {
      return $this->respond(true);
    }

    return $this->respond(false);
  }

  public function getCard(CardRequest $request) {
    $board = Board::find($request->id);

    if(!$board) {
      return $this->respondWithNotFound();
    }
  
    $fromDate = Carbon::now()->subDays($request->days);
    $users = $board->users()->wherePivot('active', true)->get();
    $effortByUsers = collect();

    foreach($users as $user) {
      $activities = $user->activities()->where('type', 'run')->where('start_date_local', '>=', $fromDate)->get();
      $efforts = collect();
      
      foreach($activities as $activity) {
        $effort = $activity->efforts()->where('name', Effort::getType($request->type))->orderBy('moving_time', 'asc')->first();
        $efforts->push($effort);
      }
      $e = $efforts->where('moving_time', $efforts->min('moving_time'))->first();

      if($e) {
        $effortByUsers->push(EffortResource::make($e));
      }
    }

    return $this->respond($effortByUsers);
  }
}
