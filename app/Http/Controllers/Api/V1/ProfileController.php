<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\V1\ProfileMonthRequest;
use App\Http\Requests\Api\V1\ProfileEffortRequest;
use \Carbon\Carbon;

class ProfileController extends BaseController
{
  const MONTHTYPES = [
    'time' => 
      ['field' => 'elapsed_time', 'dir' => 'asc'],
    'distance' => 
      ['field' => 'distance', 'dir' => 'asc'],
  ];

  public function accu() {
    $user = $this->getUser();
    
    $values['count'] = 0;
    $values['time'] = 0;
    $values['distance'] = 0;
    $values['calories'] = 0;
    
    $month = new Carbon('first day of this month');
    $firstDay = new Carbon($month->format('Y-m-d'));

    $activities = $user->activities()->where('type', 'run')->where('start_date_local', '>=', $firstDay)->get();

    foreach($activities as $activity) {
      $values['count'] = $values['count'] + 1;
      $values['time'] = $values['time'] + $activity->elapsed_time;
      $values['distance'] = $values['distance'] + $activity->distance;
      $values['calories'] = $values['calories'] + $activity->calories;
    }
    return $this->respond($values);
  }

  public function month(ProfileMonthRequest $request) {
    $user  = $this->getUser();

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

  public function efforts(ProfileEffortRequest $reques) {

  }
}
