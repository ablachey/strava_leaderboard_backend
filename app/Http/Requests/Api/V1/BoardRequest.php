<?php

namespace App\Http\Requests\Api\V1;

use App\Http\Requests\Api\BaseRequest;

class BoardRequest extends BaseRequest
{
  /**
   * Get the validation rules that apply to the request.
   *
   * @return array
   */
  public function rules()
  {
    return [
      'name' => 'required|unique:boards',
    ];
  }
}
