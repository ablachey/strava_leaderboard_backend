<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
  use Notifiable;

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'firstname', 'lastname', 'email', 'strava_id', 'token', 'badge_type', 'profile_pic', 'profile_pic_medium',
  ];

  public function activities() {
    return $this->hasMany(Activity::class);
  }

  public function boards() {
    return $this->belongsToMany(Board::class)->withPivot('active');
  }
}
