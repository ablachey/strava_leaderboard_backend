<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use App\Board;
use Auth;
use App\Http\Resources\Api\V1\UserResource;

class ConnectionController extends BaseController
{
  public function getConnections() {
    $user = Auth::user();
    $boards = $user->boards()->get();
    $connections = collect();

    foreach($boards as $board) {
      $users = $board->users()->get();

      foreach($users as $u) {
        $exists = $connections->contains(function($v, $k) use ($u) {
          return $u->id === $v->id;
        });

        if(!$exists && $u->id !== $user->id) {
          $connections->push($u);
        }
      }
    }

    $sortedConn = $connections->sortBy(['firstname', 'lastname']);

    return $this->respond(UserResource::collection($sortedConn));
  }
}
