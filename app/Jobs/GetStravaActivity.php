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
use App\Location;
use App\Split;
use App\Lap;
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
    unset($data['id']);

    if(Activity::where('strava_id', $data['strava_id'])->first()) {
      return true;
    }

    $data['start_date_local'] = new Carbon($data['start_date_local']);

    $activity = new Activity($data);
    $this->user->activities()->save($activity);

    $loc['start_lat'] = $data['start_latlng'][0];
    $loc['start_lng'] = $data['start_latlng'][1];
    $loc['end_lat'] = $data['end_latlng'][0];
    $loc['end_lng'] = $data['end_latlng'][1];
    $loc['map_id'] = $data['map']['id'];
    $loc['polyline'] = $data['map']['polyline'];
    $loc['summary_polyline'] = $data['map']['summary_polyline'];

    $activity->location()->save(new Location($loc));
    
    foreach($data['best_efforts'] as $bestEffort) {
      $bestEffort['strava_id'] = $bestEffort['id'];
      unset($bestEffort['id']);
      $be = Effort::where('strava_id', $bestEffort['strava_id'])->first();
      if(!$be) {
        $bestEffort['start_date_local'] = new Carbon($bestEffort['start_date_local']);
        $effort = new Effort($bestEffort);
        $activity->efforts()->save($effort);
      }
    }

    foreach($data['splits_metric'] as $split) {
      $sp = new Split($split);
      $activity->splits()->save($sp);
    }

    foreach($data['laps'] as $lap) {
      $lap['strava_id'] = $lap['id'];
      unset($lap['id']);
      $storedLap = Lap::where('strava_id', $lap['strava_id'])->first();
      if(!$storedLap) {
        $lap['start_date_local'] = new Carbon($lap['start_date_local']);
        $lp = new Lap($lap);
        $activity->laps()->save($lp);
      }
    }
  }
}
