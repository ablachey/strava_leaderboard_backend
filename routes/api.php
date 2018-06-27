<?php

Route::post('auth/authenticate', 'Api\V1\AuthController@authenticate');
Route::post('auth/refresh', 'Api\V1\AuthController@refresh');

Route::group(['middleware' => 'jwt.auth'], function() {
  Route::get('auth/me', 'Api\V1\AuthController@authenticatedUser');
  Route::post('auth/logout', 'Api\V1\AuthController@unauthenticate');

  Route::get('boards', 'Api\V1\BoardController@index');
  Route::get('boards/{id}', 'Api\V1\BoardController@show');
  Route::post('boards/search', 'Api\V1\BoardController@search');
  Route::post('boards/{id}/join', 'Api\V1\BoardController@join');
  Route::post('boards/{id}/join/{uid}/approve', 'Api\V1\BoardController@approveJoin');
  Route::post('boards', 'Api\V1\BoardController@store');
  Route::delete('boards/{id}', 'Api\V1\BoardController@destroy');
  
  Route::get('boards/{id}/cards', 'Api\V1\CardController@getCard');
  Route::get('boards/{id}/highest', 'Api\V1\CardController@getHighest');
  Route::get('boards/{id}/overall', 'Api\V1\CardController@getOverall');

  Route::get('profile/{id}', 'Api\V1\ProfileController@user');
  Route::get('profile/{id}/accumulated', 'Api\V1\ProfileController@accu');
  Route::get('profile/{id}/month', 'Api\V1\ProfileController@month');
  Route::get('profile/{id}/efforts', 'Api\V1\ProfileController@efforts');

  Route::get('prs/{id}', 'Api\V1\PRController@show');
});
