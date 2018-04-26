<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class EffortResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
  public function toArray($request)
  {
    return [
      'id' => $this->id,
      'strava_id' => $this->strava_id,
      'name' => $this->name,
      'elapsed_time' => $this->elapsed_time,
      'moving_time' => $this->moving_time,
      'distance' => $this->distance,
      'activity' => ($this->activity_id) ? ActivityResource::make($this->activity) : null,
    ];
  }
}
