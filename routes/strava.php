<?php

Route::get('subscribe', 'Api\V1\StravaController@subscribe');
Route::get('view', 'Api\V1\StravaController@view');
Route::get('hook', 'Api\V1\StravaController@getHook');
Route::post('hook', 'Api\V1\StravaController@postHook');