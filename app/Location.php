<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
  protected $fillable = [
    'activity_id',
    'start_lat',
    'start_lng',
    'end_lat',
    'end_lng',
    'map_id',
    'polyline',
    'summary_polyline',
  ];

  public function activity() {
    return $this->belongsTo(Activity::class);
  }
}
