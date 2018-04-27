<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Lap extends Model
{
  protected $fillable = [
    'activity_id',
    'strava_id',
    'name',
    'elapsed_time',
    'moving_time',
    'start_date_local',
    'distance',
    'start_index',
    'end_index',
    'average_speed',
    'max_speed',
    'average_cadence',
    'average_heartrate',
    'max_heartrate',
    'lap_index',
    'split',
  ];

  public function activity() {
    return $this->belongsTo(Activity::class);
  }
}
