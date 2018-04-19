<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use App\Http\Requests\Api\V1\BoardRequest;
use App\Http\Requests\Api\V1\BoardSearchRequest;
use App\Http\Resources\Api\V1\BoardResource;
use App\Http\Resources\Api\V1\BoardMemberResource;
use App\Http\Resources\Api\V1\BoardAdminResource;
use App\Http\Resources\Api\V1\BoardListingResource;
use App\Board;

class BoardController extends BaseController
{
  public function index() {
    $user = $this->getUser();

    return $this->respond(BoardListingResource::collection($user->boards()->get()));
  }

  public function show($id) {
    $board = Board::find($id);
    $currentUser = $this->getUser();

    if(!$board) {
      return $this->respondWithNotFound();
    }

    if(!$board->hasAccess($currentUser)) {
      return $this->respond(BoardResource::make($board));
    }

    if($board->isAdmin($currentUser)) {
      return $this->respond(BoardAdminResource::make($board));
    }
    return $this->respond(BoardMemberResource::make($board));
  }

  public function search(BoardSearchRequest $request) {
    $boards = Board::where('name', 'LIKE', "%$request->keyword%")->orderBy('name', 'asc')->get();
    return $this->respond(BoardResource::collection($boards));
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

    if(!$board->isAdmin($this->getUser())) {
      return $this->respondWithError(null, 403, 'forbidden');
    }

    if($boardUser->pivot->active) {
      return $this->respondWithError(null, 422, ['user' => ['Already approved']]);
    }
    
    return $this->respond(($board->users()->updateExistingPivot($boardUser->id, ['active' => true])) ? true : false);
  }
}
