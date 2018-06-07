<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AuthRequest;
use GuzzleHttp\Client;
use App\User;
use JWTAuth;
use App\Http\Resources\Api\V1\UserResource;
use \Carbon\Carbon;
use App\Jobs\GetStravaActivities;

class AuthController extends BaseController
{
  public function authenticate(AuthRequest $request) {
    
    $client = new Client();
    $result = $client->post(env('STRAVA_TOKEN_URL'),
      ['form_params' =>
        [
          'client_id' => env('STRAVA_CLIENT_ID'),
          'client_secret' => env('STRAVA_CLIENT_SECRET'),
          'code' => $request->code          
        ]
      ]
    );

    $data = json_decode($result->getBody());
    
    $userData = [
      'token' => $data->access_token,
      'firstname' => $data->athlete->firstname,
      'lastname' => $data->athlete->lastname,
      'strava_id' => $data->athlete->id,
      'email' => $data->athlete->email,
      'badge_type' => $data->athlete->badge_type_id,
      'profile_pic' => $data->athlete->profile,
      'profile_pic_medium' => $data->athlete->profile_medium,
    ];

    $user = User::where('email', $userData['email'])->first();
    
    if(!$user) {
      $user = User::create($userData);
    }
    else {
      $user->update($userData);
    }

    $jwt = JWTAuth::fromUser($user, []);

    if($jwt) {
      GetStravaActivities::dispatch($user, null, null);
      return $this->respond(['token' => $jwt]);
    }
    
    return $this->respondWithError(null, 401, 'unauthorized');
  }

  public function authenticatedUser(Request $request) {
    return $this->respond(UserResource::make($this->getUser()));
  }

  public function unauthenticate() {
    return $this->respond(JWTAuth::parseToken()->invalidate());
  }

  public function refresh() {
    return $this->respond(['token' => JWTAuth::parseToken()->refresh()]);
  }
}
