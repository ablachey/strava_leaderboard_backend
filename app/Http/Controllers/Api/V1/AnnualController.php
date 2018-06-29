<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use Auth;
use \Carbon\Carbon;
use DB;
use App\Http\Resources\Api\V1\BubbleResource;

class AnnualController extends BaseController
{
  public function getStats() {
    $user = Auth::user();
    $firstDay = new Carbon('first day of january');
    $lastDay = new Carbon('last day of december');
    
    $activities = $user->activities()
                    ->where('type', 'run')
                    ->where('start_date_local', '>=', $firstDay)
                    ->where('start_date_local', '<', $lastDay);
    
    $runs = $activities->count();
    $distTime = $activities->select(DB::raw('SUM(distance) as total_distance, SUM(elapsed_time) as total_time'))->first();

    $res['runs'] = $runs;
    $res['distance'] = $distTime['total_distance'];
    $res['time'] = $distTime['total_time'];

    return $this->respond($res);
  }

  public function getDistances() {
    $user = Auth::user();
    $months = array();
    $distances = array();
    $i = 1;
    $date = new Carbon('first day of january');
    $now = Carbon::now();
    $thisMonth = $now->format('m');

    do {
      $daySt = $date->format('Y-m-d H:i:s');
      $dayEnd = new Carbon('last day of ' . $date->format('F'));
      $dayEnd->addDay();
      $dayEn = $dayEnd->format('Y-m-d H:i:s');
      $shortMonth = $date->format('M');

      $dist = $user->activities()
                      ->where('type', 'run')
                      ->where('start_date_local', '>=', $daySt)
                      ->where('start_date_local', '<', $dayEn)
                      ->select(DB::raw('SUM(distance) as total_distance'))->first();

      array_push($months, $shortMonth);
      array_push($distances, $dist['total_distance']);

      $date->addMonth();
      $i = $i + 1;
    }
    while($i <= $thisMonth);

    $res['months'] = $months;
    $res['distances'] = $distances;

    return $this->respond($res);
  }

  public function getBubble() {
    $user = Auth::user();
    $firstDay = new Carbon('first day of january');
    $lastDay = new Carbon('last day of december');
    
    $activities = $user->activities()
                    ->where('type', 'run')
                    ->where('start_date_local', '>=', $firstDay)
                    ->where('start_date_local', '<', $lastDay)
                    ->select('start_date_local', 'distance')
                    ->get();

    return $this->respond(BubbleResource::collection($activities));
  }
}
