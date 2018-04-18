<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use App\Http\Requests\Api\V1\CardRequest;
use App\Http\Requests\Api\V1\HighestRequest;
use App\Http\Requests\Api\V1\OverallRequest;
use App\Http\Resources\Api\V1\EffortResource;
use App\Http\Resources\Api\V1\ActivityResource;
use App\Http\Resources\Api\V1\UserResource;
use \Carbon\Carbon;
use App\Board;
use App\Effort;

class CardController extends BaseController
{
  const HIGHESTTYPES = [
    'longest-run' => 
      ['field' => 'moving_time', 'dir' => 'desc'],
    'furthest-run' => 
      ['field' => 'distance', 'dir' => 'desc'],
    'max-calories' => 
      ['field' => 'calories', 'dir' => 'desc'],
  ];

  const OVERALLTYPES = [
    'overall-distance' => 
      ['field' => 'distance', 'dir' => 'desc', 'unit' => 'metre'],
    'overall-time' =>
      ['field' => 'moving_time', 'dir' => 'desc', 'unit' => 'second'],
  ];

  public function getCard(CardRequest $request) {
    $board = Board::find($request->id);

    if(!$board) {
      return $this->respondWithNotFound();
    }

    if(!$board->hasAccess($this->getUser())) {
      return $this->respondWithError(null, 403, 'forbidden');
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
    
    return $this->respond($sortedEfforts->values()->all());
  }

  public function getHighest(HighestRequest $request) {
    $board = Board::find($request->id);
    $acts = collect();

    if(!$board) {
      return $this->respondWithNotFound();
    }

    if(!$board->hasAccess($this->getUser())) {
      return $this->respondWithError(null, 403, 'forbidden');
    }

    $fromDate = Carbon::now()->subDays($request->days);
    $users = $board->users()->wherePivot('active', true)->get();

    $attr = self::HIGHESTTYPES[$request->type];

    foreach($users as $user) {
      $activity = $user->activities()->where('type', 'run')->where('start_date_local', '>=', $fromDate)->orderBy($attr['field'], $attr['dir'])->first();
      if($activity) {
        $acts->push(ActivityResource::make($activity));
      }
    }

    $sortedActivities = ($attr['dir'] == 'desc') ? $acts->sortByDesc($attr['field']) : $acts->sortBy($attr['field']);
    return $sortedActivities->values()->all();
  }

  public function getOverall(OverallRequest $request) {
    $board = Board::find($request->id);
    $acts = collect();

    if(!$board) {
      return $this->respondWithNotFound();
    }

    if(!$board->hasAccess($this->getUser())) {
      return $this->respondWithError(null, 403, 'forbidden');
    }

    $fromDate = Carbon::now()->subDays($request->days);
    $users = $board->users()->wherePivot('active', true)->get();

    $unit = self::OVERALLTYPES[$request->type]['unit'];
    $data = collect();

    foreach($users as $user) {
      $value = $user->activities()->where('type', 'run')->where('start_date_local', '>=', $fromDate)->sum(self::OVERALLTYPES[$request->type]['field']);

      if($value != 0) {
        $d = collect();
        $d->put('value', (int)$value);
        $d->put('unit', $unit);
        $d->put('athlete', UserResource::make($user));
        $data->push($d);
      }
    }

    $sortedData = (self::OVERALLTYPES[$request->type]['dir'] == 'desc') ? $data->sortByDesc('value') : $data->sortBy('value');
    return $sortedData->values()->all();
  }
}
