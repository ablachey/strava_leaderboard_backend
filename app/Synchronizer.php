<?php

namespace App;

use GuzzleHttp\Client;
use App\User;
use App\Activity;
use App\Effort;

class Synchronizer
{
  protected $user;
  protected $before;
  protected $after;

  public function __construct($before, $after, User $user) {
    $this->user = $user;
    $this->before = $before;
    $this->after = $after;
  }

  public function sync() {
    $client = new Client();
    $params = http_build_query(['before' => $this->before, 'after' => $this->after]);
    $url = env('STRAVA_OWNER_URL') . '/activities?' . $params;

    $result = $client->get($url, [
      'headers' => $this->getAuthHeader()
    ]);

    foreach(json_decode($result->getBody()) as $item) {
      $activity = Activity::where('strava_id', $item->id)->first();

      if(!$activity) {
        $this->getActivity($item->id);
      }
    }

    return true;
  }

  public function getActivity($id) {
    $client = new Client();
    $url = env('STRAVA_ACTIVITY_URL') . '/' . $id;

    $result = $client->get($url, [
      'headers' => $this->getAuthHeader()
    ]);
    
    $data = json_decode($result->getBody(), true);
    $data['strava_id'] = $data['id'];
    unset($data['id']);

    $activity = new Activity($data);
    $this->user->activities()->save($activity);
    
    foreach($data['best_efforts'] as $bestEffort) {
      $bestEffort['strava_id'] = $bestEffort['id'];
      unset($bestEffort['id']);
      $effort = new Effort($bestEffort);
      $activity->efforts()->save($effort);
    }

    return $activity;
  }

  private function getAuthHeader() {
    return [
      'Authorization' => 'Bearer ' . $this->user->token
    ];
  }
}
