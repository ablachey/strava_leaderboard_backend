<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Split extends Model
{
  protected $fillable = [
    'activity_id',
    'distance',
    'elapsed_time',
    'moving_time',
    'split',
    'average_heartrate',
    'average_speed',
  ];

  public function activity() {
    return $this->belongsTo(Activity::class);
  }
}
