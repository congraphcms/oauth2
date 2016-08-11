<?php
/*
 * This file is part of the cookbook/oauth-2 package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Cookbook\OAuth2;

use Illuminate\Contracts\Auth\UserProvider;

/**
 * PasswordGrantVerifier
 * 
 * Used for user credentials verification on password grant auth
 * 
 * @author    Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright   Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package   cookbook/oauth-2
 * @since     0.1.0-alpha
 * @version   0.1.0-alpha
 */
class PasswordGrantVerifier
{

  /**
   * User provider repo
   * 
   * @var Illuminate\Contracts\Auth\UserProvider
   */ 
  protected $provider;

  /**
   * Create new PasswordGrantVerifier
   *
   * @param Illuminate\Contracts\Auth\UserProvider $provider
   *
   * @return void
   */
  public function __construct(UserProvider $provider)
  {
    $this->provider = $provider;
  }

  /**
   * Verify user
   *
   * @param string $username
   * @param string $password
   *
   * @return mixed
   */
  public function verify($username, $password)
  {
      $credentials = [
        'email'    => $username,
        'password' => $password,
      ];

      $user = $this->provider->retrieveByCredentials($credentials);

      if ($user && $this->provider->validateCredentials($user, $credentials)) {
          return $user->id;
      }

      return false;
  }
}