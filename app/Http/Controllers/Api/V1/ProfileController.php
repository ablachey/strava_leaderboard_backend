<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\V1\ProfileMonthRequest;
use App\Http\Requests\Api\V1\ProfileEffortRequest;
use \Carbon\Carbon;
use App\Effort;
use App\User;
use App\Http\Resources\Api\V1\UserResource;

class ProfileController extends BaseController
{
  const MONTHTYPES = [
    'time' => 
      ['field' => 'elapsed_time', 'dir' => 'asc'],
    'distance' => 
      ['field' => 'distance', 'dir' => 'asc'],
  ];

  public function user(Request $request) {
    $user = User::find($request->id);

    if(!$user) {
      return $this->respondWithNotFound();
    }

    return $this->respond(UserResource::make($user));
  }

  public function accu(Request $request) {
    $user = User::find($request->id);

    if(!$user) {
      return $this->respondWithNotFound();
    }
    
    $values['count'] = 0;
    $values['time'] = 0;
    $values['distance'] = 0;
    $values['calories'] = 0;

    $values['countPrev'] = 0;
    $values['timePrev'] = 0;
    $values['distancePrev'] = 0;
    $values['caloriesPrev'] = 0;
    
    $month = new Carbon('first day of this month');
    $firstDay = new Carbon($month->format('Y-m-d'));

    $monthPrev = new Carbon('first day of last month');
    $firstDayPrev = new Carbon($monthPrev->format('Y-m-d'));

    $activities = $user->activities()->where('type', 'run')->where('start_date_local', '>=', $firstDay)->get();

    foreach($activities as $activity) {
      $values['count'] = $values['count'] + 1;
      $values['time'] = $values['time'] + $activity->elapsed_time;
      $values['distance'] = $values['distance'] + $activity->distance;
      $values['calories'] = $values['calories'] + $activity->calories;
    }

    $activitiesPrev = $user->activities()
                      ->where('type', 'run')
                      ->where('start_date_local', '>=', $firstDayPrev)
                      ->where('start_date_local', '<', $firstDay)
                      ->get();

    foreach($activitiesPrev as $ap) {
      $values['countPrev'] = $values['countPrev'] + 1;
      $values['timePrev'] = $values['timePrev'] + $ap->elapsed_time;
      $values['distancePrev'] = $values['distancePrev'] + $ap->distance;
      $values['caloriesPrev'] = $values['caloriesPrev'] + $ap->calories;
    }

    return $this->respond($values);
  }

  public function month(ProfileMonthRequest $request) {
    $user = User::find($request->id);

    if(!$user) {
      return $this->respondWithNotFound();
    }

    $lastMonthBegin = new Carbon('first day of last month');
    $lastMonthFirstDay = new Carbon($lastMonthBegin->format('Y-m-d'));
    $lastMonthLastDay = new Carbon('last day of last month');
    $thisMonthBegin = new Carbon('first day of this month');
    $thisMonthFirstDay = new Carbon($thisMonthBegin->format('Y-m-d'));
    $thisMonthLastDay = new Carbon('last day of this month');

    $type = self::MONTHTYPES[$request->type];

    
    $start = 1;
    $end = ($lastMonthLastDay->format('d') > $thisMonthLastDay->format('d')) ? $lastMonthLastDay->format('d') : $thisMonthLastDay->format('d');

    $lastMonthDays = array();
    $lastMonthValues = array();
    $lastMonthActivities = $user->activities()
                              ->where('type', 'run')
                              ->where('start_date_local', '>=', $lastMonthFirstDay)
                              ->where('start_date_local', '<', $thisMonthFirstDay)
                              ->orderBy('start_date_local', 'asc')
                              ->get();

    $lastMonthlastValue = 0;
    for($i = $start; $i <= $end; $i++) {
      array_push($lastMonthDays, $i);
      
      foreach($lastMonthActivities as $lmActivity) {
        $lma = $lmActivity->toArray();
        $dt = new Carbon($lma['start_date_local']);
        $day = $dt->format('j');
        
        if($i == $day) {
          $lastMonthlastValue = $lastMonthlastValue + $lma[$type['field']];
        }
      }

      array_push($lastMonthValues, $lastMonthlastValue);
    }


    $thisMonthDays = array();
    $thisMonthValues = array();
    $thisMonthActivities = $user->activities()
                              ->where('type', 'run')
                              ->where('start_date_local', '>=', $thisMonthFirstDay)
                              ->orderBy('start_date_local', 'asc')
                              ->get();

    $thisMonthLastValue = 0;
    for($i = $start; $i <= $end; $i++) {
      array_push($thisMonthDays, $i);

      foreach($thisMonthActivities as $tmActivity) {
        $tma = $tmActivity->toArray();
        $dt = new Carbon($tma['start_date_local']);
        $day = $dt->format('j');

        if($i == $day) {
          $thisMonthLastValue = $thisMonthLastValue + $tma[$type['field']];
        }
      }

      array_push($thisMonthValues, $thisMonthLastValue);
    }
            
    $ret = [
      'last_month' => [
        'values' => $lastMonthValues,
        'days' => $lastMonthDays
      ],
      'this_month' => [
        'values' => $thisMonthValues,
        'days' => $thisMonthDays
      ]
    ];
    
    return $this->respond($ret);
  }

  public function efforts(ProfileEffortRequest $request) {
    $user = User::find($request->id);

    if(!$user) {
      return $this->respondWithNotFound();
    }

    $monthStart = new Carbon('first day of this month');
    $monthFirstDay = new Carbon($monthStart->format('Y-m-d'));

    $days = array();
    $values = array();

    $activities = $user->activities()
                    ->where('type', 'run')
                    ->where('start_date_local', '>=', $monthFirstDay)
                    ->orderBy('start_date_local', 'asc')
                    ->get();

    foreach($activities as $activity) {
      $effort = $activity->efforts()->where('name', Effort::getType($request->type))->first();
      if($effort) {
        $d = new Carbon($effort->start_date_local);
        array_push($days, $d->format('d M'));
        array_push($values, $effort->elapsed_time);
      }
    }

    $ret = [
      'values' => $values,
      'days' => $days
    ];

    return $this->respond($ret);
  }

  public function distPaceAvg(Request $request) {
    $user = User::find($request->id);

    if(!$user) {
      return $this->respondWithNotFound();
    }

    $monthStart = new Carbon('first day of this month');
    $monthFirstDay = new Carbon($monthStart->format('Y-m-d'));

    $days = array();
    $distances = array();
    $paces = array();

    $activities = $user->activities()
                    ->where('type', 'run')
                    ->where('start_date_local', '>=', $monthFirstDay)
                    ->orderBy('start_date_local', 'asc')
                    ->get();

    foreach($activities as $activity) {
      $d = new Carbon($activity->start_date_local);
      array_push($days, $d->format('d M'));
      $pace = ($activity->elapsed_time / ($activity->distance / 1000));
      array_push($distances, $activity->distance);
      array_push($paces, $pace);
    }

    $ret = [
      'days' => $days,
      'distances' => $distances,
      'paces' => $paces,
    ];

    return $this->respond($ret);
  }
}
