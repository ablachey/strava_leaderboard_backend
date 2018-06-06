<?php

Route::get('subscribe', function() {
  return 'OK';
});

Route::get('view', 'Api\V1\StravaController@view');