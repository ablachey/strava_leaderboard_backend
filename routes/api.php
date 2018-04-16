<?php

Route::post('auth/authenticate', 'Api\V1\AuthController@authenticate');

Route::group(['middleware' => 'jwt.auth'], function() {
  Route::get('auth/me', 'Api\V1\AuthController@authenticatedUser');
  Route::post('auth/logout', 'Api\V1\AuthController@unauthenticate');
  Route::post('activity/sync', 'Api\V1\ActivityController@syncData');
});
