<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\BoardRequest;
use App\Board;

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

    return $this->respond($board);
  }

  public function store(BoardRequest $request) {
    $board = Board::create($request->all());
    $board->users()->save($this->getUser(), ['active' => true, 'admin' => true]);

    return $this->respond($board);
  }
}
