<?php

use LucaDegasperi\OAuth2Server\Facades\Authorizer;

Route::post('oauth/access_token', function() {
    return Response::json(Authorizer::issueAccessToken());
});

Route::post('oauth/revoke_token', function() {
    return Response::json(Authorizer::revokeToken());
});

Route::get('oauth/owner', function() {
	$owner = Authorizer::getOwner();
    return Response::json(['data' => $owner->toArray()]);
});