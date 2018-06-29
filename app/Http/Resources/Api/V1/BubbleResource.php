<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;
use \Carbon\Carbon;

class BubbleResource extends JsonResource
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
      'x' => $sdt->format('m'),
      'y' => $sdt->format('d'),
      'r' => round($this->distance / 500),
    ];
  }
}
