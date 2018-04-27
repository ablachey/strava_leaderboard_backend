<?php

namespace App;

use GuzzleHttp\Client;
use App\User;
use App\Activity;
use App\Effort;
use \Carbon\Carbon;
use App\Jobs\GetStravaActivity;

class Synchronizer
{
  protected $user;
  protected $before;
  protected $after;

  public function __construct(User $user, $after, $before) {
    $this->user = $user;
    $this->before = $before;
    $this->after = $after;
  }

  public function sync() {
    $acts = collect();
    $page = 1;

    $client = new Client();
    $data = [];
    $paramItems = [];
    if($this->before) {
      $paramItems['before'] = $this->before;
    }
    if($this->after) {
      $paramItems['after'] = $this->after;
    }
  
    do {
      $paramItems['page'] = $page;
      $params = http_build_query($paramItems);
      $url = env('STRAVA_OWNER_URL') . '/activities?' . $params;
      
      $result = $client->get($url, [
        'headers' => $this->user->getAuthHeader()
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
        GetStravaActivity::dispatch($this->user, $item->id);
      }
    }

    return true;
  }

  public function test($id) {
    $client = new Client();
    $url = env('STRAVA_ACTIVITY_URL') . '/' . $id;
    
    $result = $client->get($url, [
      'headers' => $this->user->getAuthHeader()
    ]);
    
    $data = json_decode($result->getBody(), true);
    
    return $data;
  }
}
