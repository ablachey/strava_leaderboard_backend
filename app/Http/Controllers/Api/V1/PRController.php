<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use App\User;
use DB;

class PRController extends BaseController
{
  public function show(Request $request) {
    $user = User::find($request->id);

    if(!$user) {
      return $this->respondWithNotFound();
    }
    $efforts = collect();

    $activities = $user->activities()->get();

    foreach($activities as $activity) {
      $effs = $activity->efforts()->select('name', DB::raw('MIN(elapsed_time) as e_time'))->where('pr_rank', 1)->groupBy('name')->get();

      foreach($effs as $eff) {
        $eff['strava_id'] = $activity->strava_id;
        $eff['start_date_local'] = $activity->start_date_local;
        $cont = $efforts->contains(function($v, $k) use ($eff) {
          return $v->name === $eff->name;
        });

        if($cont) {
          $ceff = $efforts->where('name', $eff->name)->first();
          if($ceff->e_time > $eff->e_time) {
            foreach($efforts as $k => $e) {
              if($e->name === $eff->name) {
                $efforts->forget($k);
              }
            }
            $efforts->push($eff);
          }
        }
        else {
          $efforts->push($eff);
        }
      }
    }

    return $efforts->values();
  }
}
