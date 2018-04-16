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

  protected $types = [
    'four-hundred' => '400m',
    'half-mile' => '1/2 mile',
    'kilometer' => '1k',
    'mile' => '1 mile',
    'two-mile' => '2 mile',
    'five-kilometer' => '5k',
  ];

  public function activity() {
    return $this->belongsTo(Activity::class);
  }

  public function getType($reqType) {
    return $types[$reqTypes];
  }
}
