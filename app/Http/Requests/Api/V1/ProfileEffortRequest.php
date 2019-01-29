<?php

namespace App\Http\Requests\Api\V1;

use App\Http\Requests\Api\BaseRequest;
use App\Effort;

class ProfileEffortRequest extends BaseRequest
{
  /**
   * Get the validation rules that apply to the request.
   *
   * @return array
   */
  public function rules()
  {
    return [
      'type' => 'required|in:' . implode(',', array_keys(Effort::TYPES)),
    ];
  }
}