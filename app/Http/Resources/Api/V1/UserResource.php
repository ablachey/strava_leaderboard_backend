<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
      'firstname' => $this->firstname,
      'lastname' => $this->lastname,
      'email' => $this->email,
      'badge_type' => $this->badge_type,
      'image' => [
        'medium' => $this->profile_pic_medium,
        'large' => $this->profile_pic,
      ],
    ];
  }
}
