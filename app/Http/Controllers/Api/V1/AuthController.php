<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AuthRequest;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use App\User;
use JWTAuth;

class AuthController extends Controller
{
  public function authenticate(AuthRequest $request) {
    try {
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
        'email' => $data->athlete->email,
        'badge_type' => $data->athlete->badge_type_id,
        'profile_pic' => $data->athlete->profile,
        'profile_pic_medium' => $data->athlete->profile_medium,
      ];

      $user = User::updateOrCreate($userData);
      
      if(!$user) {
        return response()->json(['code' => 401, 'error' => 'unauthorized', 'message' => '']);
      }

      $jwt = JWTAuth::fromUser($user, []);

      if($jwt) {
        return response(['token' => $jwt], 200);
      }
      
      return response()->json(['code' => 401, 'error' => 'unauthorized', 'message' => '']);
    }
    catch(GuzzleException $e) {
      return response()->json(['code' => $e->getCode(), 'error' => 'unauthorized', 'message' => $e->getMessage()], $e->getCode());
    }
  }

  public function authenticatedUser(Request $request) {
    if(!$user = JWTAuth::parseToken()->authenticate()) {
      return response()->json(['user_not_found'], 404);
    }

    unset($user->token);
    
    return response()->json(compact('user'));
  }
}
