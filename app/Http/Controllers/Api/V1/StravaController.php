<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController;
use GuzzleHttp\Client;

class StravaController extends BaseController
{
  public function subscribe() {

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
}
