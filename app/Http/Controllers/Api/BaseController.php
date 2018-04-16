<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\BaseResource;
use JWTAuth;

class BaseController extends Controller
{
  public function respond($rawData = null, $code = 200, $message = 'OK') {
    $output = [
      'code'       => $code,
      'message'    => $message,
      'data'       => $rawData,
    ];

    return response()->json($output, $code);
  }

  public function respondWithError($data = null, $code, $message = null) {
    if ($message === null) {
        $message = Response::$statusTexts[$code];
    }
    return $this->respond($data, $code, $message);
  }

  public function respondWithNotFound() {
    return $this->respond(null, 404, Response::$statusTexts[404]);
  }

  public function getUser() {
    if(!$user = JWTAuth::parseToken()->authenticate()) {
      return $this->respondWithNotFound();
    }

    return $user;
  }
}
