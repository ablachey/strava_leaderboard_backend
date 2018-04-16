<?php

Route::post('auth/authenticate', 'Api\V1\AuthController@authenticate');

Route::group(['middleware' => 'jwt.auth'], function() {
  Route::get('auth/me', 'Api\V1\AuthController@authenticatedUser');
  Route::post('auth/logout', 'Api\V1\AuthController@unauthenticate');

  Route::post('activities/sync', 'Api\V1\ActivityController@syncData');

  Route::get('boards/{id}', 'Api\V1\BoardController@show');
  Route::post('boards', 'Api\V1\BoardController@store');
  Route::get('boards/{id}/cards', 'Api\V1\BoardController@getCard');
});
