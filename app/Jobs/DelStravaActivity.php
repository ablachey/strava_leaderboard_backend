<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Activity;
use App\User;

class DelStravaActivity implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  protected $user;
  protected $activity;

  /**
   * Create a new job instance.
   *
   * @return void
   */
  public function __construct(User $user, Activity $activity)
  {
    $this->user = $user;
    $this->activity = $activity;
  }

  /**
   * Execute the job.
   *
   * @return void
   */
  public function handle()
  {
    if($this->activity->user()->first()->id == $this->user->id) {
      $this->activity->delete();
    }
  }
}
