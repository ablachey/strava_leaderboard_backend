<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use GuzzleHttp\Client;
use App\User;
use App\Activity;
use App\Effort;
use \Carbon\Carbon;
use App\Jobs\GetStravaActivity;

class GetStravaActivities implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  protected $user;
  protected $before;
  protected $after;

  /**
   * Create a new job instance.
   *
   * @return void
   */
  public function __construct(User $user, $after, $before)
  {
    $this->user = $user;
    $this->before = $before;
    $this->after = $after;
  }

  /**
   * Execute the job.
   *
   * @return void
   */
  public function handle()
  {
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
  
    $paramItems['per_page'] = 100;

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
      sleep(1);
    }
  }
}
