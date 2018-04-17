<?php

namespace App\Http\Requests\Api\V1;

use App\Http\Requests\Api\BaseRequest;

class HighestRequest extends BaseRequest
{
  /**
   * Get the validation rules that apply to the request.
   *
   * @return array
   */
  public function rules()
  {
    return [
      'type' => 'required|in:longest-run,furthest-run,max-calories',
      'days' => 'required|numeric|min:1|max:30',
    ];
  }
}
