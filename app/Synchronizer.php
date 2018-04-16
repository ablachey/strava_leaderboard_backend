<?php

namespace App;

use GuzzleHttp\Client;
use App\User;
use App\Activity;
use App\Effort;
use \Carbon\Carbon;

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
    $acts = collect();
    $page = 1;

    $client = new Client();
    $data = [];

    do {
      $params = http_build_query(['before' => $this->before, 'after' => $this->after, 'page' => $page]);
      $url = env('STRAVA_OWNER_URL') . '/activities?' . $params;

      $result = $client->get($url, [
        'headers' => $this->getAuthHeader()
      ]);
      
      $data = json_decode($result->getBody());
      foreach($data as $d) {
        $acts->push($d);
      }
      
      $page = $page + 1;
    } while(count($data) > 0);
    
    foreach($acts as $item) {
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
    $data['start_date_local'] = new Carbon($data['start_date_local']);
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
