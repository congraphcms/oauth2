<?php

/*
 * This file is part of OAuth 2.0 Laravel.
 *
 * (c) Luca Degasperi <packages@lucadegasperi.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Supported Grant Types
    |--------------------------------------------------------------------------
    |
    | Your OAuth2 Server can issue an access token based on different grant
    | types you can even provide your own grant type.
    |
    | To choose which grant type suits your scenario, see
    | http://oauth2.thephpleague.com/authorization-server/which-grant
    |
    | Please see this link to find available grant types
    | http://git.io/vJLAv
    |
    */

    'grant_types' => [
        'client_credentials' => [
            'class' => '\League\OAuth2\Server\Grant\ClientCredentialsGrant',
            'access_token_ttl' => 3600
        ],
        'password' => [
            'class' => '\League\OAuth2\Server\Grant\PasswordGrant',
            'callback' => '\Cookbook\OAuth2\PasswordGrantVerifier@verify',
            'access_token_ttl' => 3600
        ],
        'refresh_token' => [
            'class' => '\League\OAuth2\Server\Grant\RefreshTokenGrant',
            'access_token_ttl' => 3600,
            'refresh_token_ttl' => 604800,
            'rotate_refresh_tokens' => false
        ]
    ],

];
