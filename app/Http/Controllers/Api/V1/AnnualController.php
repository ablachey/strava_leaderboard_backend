<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use Auth;
use \Carbon\Carbon;
use DB;

class AnnualController extends BaseController
{
  public function getStats() {
    $user = Auth::user();
    $firstDay = new Carbon('first day of january');
    $lastDay = new Carbon('last day of december');
    
    $activities = $user->activities()
                    ->where('type', 'run')
                    ->where('start_date_local', '>=', $firstDay)
                    ->where('start_date_local', '<=', $lastDay);
    
    $runs = $activities->count();
    $distTime = $activities->select(DB::raw('SUM(distance) as total_distance, SUM(elapsed_time) as total_time'))->first();

    $res['total_runs'] = $runs;
    $res['total_distance'] = $distTime['total_distance'];
    $res['total_time'] = $distTime['total_time'];

    return $this->respond($res);
  }
}
