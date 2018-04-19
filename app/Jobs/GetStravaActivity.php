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

class GetStravaActivity implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  protected $user;
  protected $activityId;
  
  /**
   * Create a new job instance.
   *
   * @return void
   */
  public function __construct(User $user, $activityId)
  {
    $this->user = $user;
    $this->activityId = $activityId;
  }

  /**
   * Execute the job.
   *
   * @return void
   */
  public function handle()
  {
    $client = new Client();
    $url = env('STRAVA_ACTIVITY_URL') . '/' . $this->activityId;

    $result = $client->get($url, [
      'headers' => $this->user->getAuthHeader()
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
  }
}
