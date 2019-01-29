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
    'start_date_local',
    'start_index',
    'end_index',
    'pr_rank',
  ];

  const TYPES = [
    'four-hundred' => '400m',
    'half-mile' => '1/2 mile',
    'kilometer' => '1k',
    'mile' => '1 mile',
    'two-mile' => '2 mile',
    'five-kilometer' => '5k',
    'ten-kilometer' => '10k',
    'fifteen-kilometer' => '15k',
    'ten-mile' => '10 mile',
    'twenty-kilometer' => '20k',
    'half-marathon' => 'Half-Marathon',
  ];

  public function activity() {
    return $this->belongsTo(Activity::class);
  }

  public static function getType($reqType) {
    return (array_key_exists($reqType, self::TYPES)) ? self::TYPES[$reqType] : null;
  }
}
