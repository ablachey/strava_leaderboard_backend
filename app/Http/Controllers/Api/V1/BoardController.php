<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\BoardRequest;
use App\Http\Requests\Api\V1\CardRequest;
use App\Http\Requests\Api\V1\BoardSearchRequest;
use App\Http\Resources\Api\V1\BoardResource;
use App\Http\Resources\Api\V1\BoardAdminResource;
use App\Http\Resources\Api\V1\UserResource;
use App\Http\Resources\Api\V1\EffortResource;
use \Carbon\Carbon;
use App\Board;
use App\Activity;
use App\Effort;
use App\User;

class BoardController extends BaseController
{
  public function show($id) {
    $board = Board::find($id);
    $currentUser = $this->getUser();

    if(!$board) {
      return $this->respondWithNotFound();
    }

    $boardUsers = $board->users()->wherePivot('active', true)->get();

    if(!$boardUsers->contains('id', $currentUser->id)) {
      return $this->respondWithError(null, 403, 'forbidden');
    }

    $isAdmin = $board->users()->where('user_id', $currentUser->id)->wherePivot('admin', true)->first();

    if($isAdmin) {
      return $this->respond(BoardAdminResource::make($board));
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

  public function join(Request $request) {
    $board = Board::find($request->id);

    if(!$board) {
      return $this->respondWithNotFound();
    }

    $user = $this->getUser();
    $exists = $board->users()->where('user_id', $user->id)->first();

    if($exists) {
      if(!$exists->pivot->active) {
        return $this->respondWithError(null, 422, ['user' => ['Already joined, waiting for approval']]);
      }
      return $this->respondWithError(null, 422, ['user' => ['Already joined']]);
    }

    if($board->users()->save($user, ['active' => false, 'admin' => false])) {
      return $this->respond(true);
    }

    return $this->respond(false);
  }

  public function approveJoin(Request $request) {
    $board = Board::find($request->id);
    $boardUser = $board->users()->where('user_id', $request->uid)->first();

    if(!$board || !$boardUser) {
      return $this->respondWithNotFound();
    }

    $isAdmin = $board->users()->where('user_id', $this->getUser()->id)->wherePivot('active', true)->wherePivot('admin', true)->first();

    if(!$isAdmin) {
      return $this->respondWithError(null, 403, 'forbidden');
    }

    if($boardUser->pivot->active) {
      return $this->respondWithError(null, 422, ['user' => ['Already approved']]);
    }
    
    return $this->respond(($board->users()->updateExistingPivot($boardUser->id, ['active' => true])) ? true : false);
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

    $sortedEfforts = $effortByUsers->sortBy('moving_time');
    return $sortedEfforts->values()->all();
    return $this->respond($sortedEfforts);
  }
}
