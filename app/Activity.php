<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
  protected $fillable = [
    'strava_id',
    'user_id',
    'name',
    'distance',
    'moving_time',
    'elapsed_time',
    'type',
    'start_date_local',
    'has_heartrate',
    'average_heartrate',
    'max_heartrate',
    'calories',
  ];

  public function user() {
    return $this->belongsTo(User::class);
  }

  public function efforts() {
    return $this->hasMany(Effort::class);
  }
}
