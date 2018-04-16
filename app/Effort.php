<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Effort extends Model
{
  protected $fillable = [
    'activity_id',
    'strava_id',
    'name',
    'elapsed_time',
    'moving_time',
    'distance',
  ];

  public function activity() {
    return $this->belongsTo(Activity::class);
  }
}
