<?php

namespace App\Http\Middleware;

use Closure;

class StravaMiddleware
{
  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure  $next
   * @return mixed
   */
  public function handle($request, Closure $next)
  {
    if(!$request->verify_token) {
      return response()->json(['status' => 'fail', 'message' => 'verify_token not provided'], 403);
    }
    if($request->verify_token !== env('STRAVA_WEBHOOK_VERIFY_TOKEN')) {
      return response()->json(['status' => 'fail', 'message' => 'verify_token mismatch'], 403);
    }
    return $next($request);
  }
}
