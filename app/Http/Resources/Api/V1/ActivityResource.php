<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;
use \Carbon\Carbon;
class ActivityResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
  public function toArray($request)
  {
    $sdt = new Carbon($this->start_date_time);
    return [
      'id' => $this->id,
      'strava_id' => $this->strava_id,
      'name' => $this->name,
      'distance' => $this->distance,
      'moving_time' => $this->moving_time,
      'elapsed_time' => $this->elapsed_time,
      'type' => $this->type,
      'start_date_local' => $sdt->format('d M H:i'),
      'has_heartrate' => $this->has_heartrate,
      'average_heartrate' => $this->average_heartrate,
      'max_heartrate' => $this->max_heartrate,
      'calories' => $this->calories,
      'athlete' => UserResource::make($this->user()->first()),
    ];
  }
}
