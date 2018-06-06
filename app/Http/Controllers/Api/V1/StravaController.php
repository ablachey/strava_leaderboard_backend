<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController;
use GuzzleHttp\Client;

class StravaController extends BaseController
{
  public function subscribe() {
    $client = new Client();
    $result = $client->get(env('STRAVA_WEBHOOK_URL'),
      ['form_params' =>
        [
          'client_id' => env('STRAVA_CLIENT_ID'),
          'client_secret' => env('STRAVA_CLIENT_SECRET'),
          'callback_url' => env('STRAVA_WEBHOOK_CALLBACK_URL'),
          'verify_token' => env('STRAVA_WEBHOOK_VERIFY_TOKEN')
        ]
      ]
    );
  }

  public function view() {
    $client = new Client();
    $result = $client->get(env('STRAVA_WEBHOOK_URL'),
      ['form_params' =>
        [
          'client_id' => env('STRAVA_CLIENT_ID'),
          'client_secret' => env('STRAVA_CLIENT_SECRET')
        ]
      ]
    );

    $data = json_decode($result->getBody());

    return $this->respond($data);
  }

  public function getHook(Request $request) {
    if(!$request->get('hub.verify_token')) {
      return $this->respondWithError(null, 401, 'unauthorized');
    }
    if($request->get('hub.verify_token') !== env('STRAVA_WEBHOOK_VERIFY_TOKEN')) {
      return $this->respondWithError(null, 401, 'unauthorized');
    }
    if($request->get('hub.verify_token')) {
      return response()->json(['hub.challenge' => $request->get('hub.verify_token')], 200);
    }

    return $this->respondWithError(null, 401, 'unauthorized');
  }

  public function postHook(Request $request) {
    
  }
}
