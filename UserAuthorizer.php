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

use LucaDegasperi\OAuth2Server\Authorizer;
use League\OAuth2\Server\AuthorizationServer as Issuer;
use League\OAuth2\Server\Exception\AccessDeniedException;
use League\OAuth2\Server\ResourceServer as Checker;
use League\OAuth2\Server\TokenType\TokenTypeInterface;
use League\OAuth2\Server\Util\RedirectUri;
use LucaDegasperi\OAuth2Server\Exceptions\NoActiveAccessTokenException;
use Symfony\Component\HttpFoundation\Request;
use Cookbook\Contracts\OAuth2\UserRepositoryContract;

/**
 * UserAuthorizer class
 *
 * Override the default Authorizer to check user permissions also
 *
 * @uses   		LucaDegasperi\OAuth2Server\Authorizer
 *
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/oauth-2
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class UserAuthorizer extends Authorizer
{

	/**
	 * User repository
	 *
	 * @var Cookbook\Contracts\OAuth2\UserRepositoryContract
	 */
	protected $userRepository;


	/**
     * Create a new UserAuthorizer instance.
     *
     * @param \League\OAuth2\Server\AuthorizationServer $issuer
     * @param \League\OAuth2\Server\ResourceServer $checker
     * @param \Cookbook\Contracts\OAuth2\UserRepositoryContract $userRepository
     */
    public function __construct(Issuer $issuer, Checker $checker, UserRepositoryContract $userRepository)
    {
        $this->issuer = $issuer;
        $this->checker = $checker;
        $this->authCodeRequestParams = [];
        $this->userRepository = $userRepository;
    }


	/**
     * Check if the current request has all the scopes passed.
     *
     * @param string|array $scope the scope(s) to check for existence
     *
     * @return bool
     */
    public function hasScope($scope)
    {
        if (is_array($scope)) {
            foreach ($scope as $s) {
                if ($this->hasScope($s) === false) {
                    return false;
                }
            }

            return true;
        }

        $accessTokenPassed = $this->getAccessToken()->hasScope($scope);

        if(!$accessTokenPassed) {
        	return false;
        }

        $ownerType = $this->getResourceOwnerType();
        if($ownerType == 'client') {
        	return true;
        }

        $userId = $this->getResourceOwnerId();

        $user = $this->userRepository->fetch($userId, ['roles']);
        $user = $user->toArray();

        if(!empty($user['roles']))
        {
            foreach ($user['roles'] as $role)
            {
                if(!empty($role['scopes']))
                {
                    foreach ($role['scopes'] as $userScope)
                    {
                        if($userScope == $scope) {
                            return true;
                        }
                    }
                }
            	
            }
        }

        return false;
    }
}