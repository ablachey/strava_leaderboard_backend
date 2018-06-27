<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class BoardListingResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
  public function toArray($request)
  {
    $athletes = [];
    if($this->pivot->active) {
      if($this->pivot->admin) {
        $athletes = UserBoardResource::collection($this->users()->orderBy('firstname', 'asc')->orderBy('lastname', 'asc')->get());
      }
      else {
        $athletes = UserBoardResource::collection($this->users()->wherePivot('active', true)->orderBy('firstname', 'asc')->orderBy('lastname', 'asc')->get());
      }
    }
    return [
      'id' => $this->id,
      'name' => $this->name,
      'athletes' => $athletes,
      'active' => $this->pivot->active,
      'admin' => $this->pivot->admin,
    ];
  }
}
