<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use JWTAuth;
use \Carbon\Carbon;
use App\Synchronizer;
use App\User;
use App\Activity;
use App\Effort;

class ActivityController extends BaseController
{
  public function syncData() {
    $before = Carbon::now();
    $after = Carbon::now()->subMonth();
    $syncObj = new Synchronizer($before->format('U'), $after->format('U'), $this->getUser());

    return $this->respond($syncObj->sync());
  }
}
