<?php

use LucaDegasperi\OAuth2Server\Facades\Authorizer;

Route::post('oauth/access_token', function() {
    return Response::json(Authorizer::issueAccessToken());
});