<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;
use \Carbon\Carbon;

class PRResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
  public function toArray($request)
  {
    $sdt = new Carbon($this->start_date_local);
    return [
      'name' => $this->name,
      'e_time' => $this->e_time,
      'strava_id' => $this->strava_id,
      'start_date_local' => $sdt->format('d M Y H:i'),
    ];
  }
}
