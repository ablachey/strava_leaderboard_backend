<?php

Route::post('auth/authenticate', 'Api\V1\AuthController@authenticate');
Route::get('auth/me', 'Api\V1\AuthController@authenticatedUser');
