<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Board extends Model
{
  protected $fillable = ['name'];

  public function users() {
    return $this->belongsToMany(User::class)->withPivot('active', 'admin');
  }

  public function hasAccess(User $user) {
    $usr = $this->users()->where('user_id', $user->id)->wherePivot('active', true)->first();

    return ($usr) ? true : false;
  }

  public function isAdmin(User $user) {
    $admin = $this->users()->where('user_id', $user->id)->wherePivot('active', true)->wherePivot('admin', true)->first();

    return ($admin) ? true : false;
  }
}
