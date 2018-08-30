<?php
/*
 * This file is part of the congraph/oauth-2 package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Congraph\OAuth2;

use LucaDegasperi\OAuth2Server\Authorizer;
use League\OAuth2\Server\AuthorizationServer as Issuer;
use League\OAuth2\Server\Exception\AccessDeniedException;
use League\OAuth2\Server\ResourceServer as Checker;
use League\OAuth2\Server\TokenType\TokenTypeInterface;
use League\OAuth2\Server\Util\RedirectUri;
use LucaDegasperi\OAuth2Server\Exceptions\NoActiveAccessTokenException;
use Symfony\Component\HttpFoundation\Request;
use Congraph\Contracts\OAuth2\UserRepositoryContract;
use Congraph\Contracts\OAuth2\ClientRepositoryContract;
use League\OAuth2\Server\Entity\RefreshTokenEntity;

/**
 * UserAuthorizer class
 *
 * Override the default Authorizer to check user permissions also
 *
 * @uses   		LucaDegasperi\OAuth2Server\Authorizer
 *
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/oauth-2
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class UserAuthorizer extends Authorizer
{

	/**
	 * User repository
	 *
	 * @var Congraph\Contracts\OAuth2\UserRepositoryContract
	 */
	protected $userRepository;

    /**
     * User repository
     *
     * @var Congraph\Contracts\OAuth2\ClientRepositoryContract
     */
    protected $clientRepository;


	/**
     * Create a new UserAuthorizer instance.
     *
     * @param \League\OAuth2\Server\AuthorizationServer $issuer
     * @param \League\OAuth2\Server\ResourceServer $checker
     * @param \Congraph\Contracts\OAuth2\UserRepositoryContract $userRepository
     * @param \Congraph\Contracts\OAuth2\ClientRepositoryContract $userRepository
     */
    public function __construct(
        Issuer $issuer, 
        Checker $checker, 
        UserRepositoryContract $userRepository, 
        ClientRepositoryContract $clientRepository
    )
    {
        $this->issuer = $issuer;
        $this->checker = $checker;
        $this->authCodeRequestParams = [];
        $this->userRepository = $userRepository;
        $this->clientRepository = $clientRepository;
    }

    /**
     * get token session owner
     *
     * @return bool
     */
    public function getOwner()
    {
        $this->validateAccessToken(false);

        $ownerId = $this->getResourceOwnerId();
        $ownerType = $this->getResourceOwnerType();

        if($ownerType == 'client')
        {
            return $this->getClient($ownerId);
        }
        elseif($ownerType == 'user')
        {
            return $this->getUser($ownerId);
        }

        return null;
    }

    /**
     * get user as owner
     * 
     * @param  $id User ID
     *
     * @return Object
     */
    protected function getUser($id)
    {
        return $this->userRepository->fetchOwner($id);
    }

    /**
     * get client as owner
     *
     * @param  $id Client ID
     * 
     * @return Object
     */
    protected function getClient($id)
    {
        return $this->clientRepository->fetchOwner($id);
    }

    /**
     * Revoke the token
     *
     * @return bool
     */
    public function revokeToken()
    {
        // $this->validateAccessToken(true);
        $token = $this->issuer->getRequest()->request->get('token', null);
        $tokenType = $this->issuer->getRequest()->request->get('token_type', null);
        if($token === null)
        {
            throw new \League\OAuth2\Server\Exception\InvalidRequestException('token');
        }
        if($tokenType !== 'access_token' && $tokenType !== 'refresh_token' && $tokenType !== null)
        {
            throw new \League\OAuth2\Server\Exception\InvalidRequestException('token_type');
        }

        if($tokenType == 'access_token')
        {
            $tokenEntity = $this->issuer->getAccessTokenStorage()->get($token);
            if(!$tokenEntity)
            {
                return;
            }
        }
        elseif($tokenType == 'refresh_token')
        {
            $tokenEntity = $this->issuer->getRefreshTokenStorage()->get($token);
            if(!$tokenEntity)
            {
                return;
            }
        }
        else
        {
            $tokenEntity = $this->issuer->getAccessTokenStorage()->get($token);
            if(!$tokenEntity)
            {
                $tokenEntity = $this->issuer->getRefreshTokenStorage()->get($token);
                if(!$tokenEntity)
                {
                    return;
                }
            }
        }

        if($tokenEntity instanceof RefreshTokenEntity)
        {
            $accessToken = $tokenEntity->getAccessToken();
            $accessToken->expire();
        }

        $tokenEntity->expire();
        return;
        
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