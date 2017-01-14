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

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Response;
use Cookbook\Contracts\OAuth2\UserRepositoryContract;
use Cookbook\Contracts\OAuth2\ClientRepositoryContract;
use Illuminate\Contracts\Container\Container as Application;
use Illuminate\Foundation\Application as LaravelApplication;
use Laravel\Lumen\Application as LumenApplication;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\ResourceServer;
use League\OAuth2\Server\Storage\AccessTokenInterface;
use League\OAuth2\Server\Storage\AuthCodeInterface;
use League\OAuth2\Server\Storage\ClientInterface;
use League\OAuth2\Server\Storage\RefreshTokenInterface;
use League\OAuth2\Server\Storage\ScopeInterface;
use League\OAuth2\Server\Storage\SessionInterface;
use LucaDegasperi\OAuth2Server\Middleware\CheckAuthCodeRequestMiddleware;
use LucaDegasperi\OAuth2Server\Middleware\OAuthClientOwnerMiddleware;
use LucaDegasperi\OAuth2Server\Middleware\OAuthMiddleware;
use LucaDegasperi\OAuth2Server\Middleware\OAuthUserOwnerMiddleware;

/**
 * OAuth2ServiceProvider service provider for OAuth-2 package
 *
 * It will register all dependecies to app container
 *
 * @uses   		Illuminate\Support\ServiceProvider
 *
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/oauth-2
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class OAuth2ServiceProvider extends ServiceProvider {

	/**
	* Register
	*
	* @return void
	*/
	public function register()
	{
		// $this->mergeConfigFrom(realpath(__DIR__ . '/config/cookbook.php'), 'cookbook');
		$this->registerServiceProviders();

		$this->registerVerifier();

		$this->registerAuthorizer($this->app);
	}

	/**
	 * Boot
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->publishes([
			__DIR__.'/database/migrations' => database_path('/migrations'),
		]);

		$source = realpath(__DIR__.'/config/oauth2.php');

        $this->mergeConfigFrom($source, 'oauth2');
		$this->addMiddleware();

		// include __DIR__ . '/Http/routes.php';
	}



	/**
	 * Register Middleware
	 *
	 * @return void
	 */
	protected function addMiddleware()
	{
		$this->app['router']->middleware('oauth', \Cookbook\OAuth2\Http\Middleware\OAuthMiddleware::class);
		$this->app['router']->middleware('oauth-user', \LucaDegasperi\OAuth2Server\Middleware\OAuthUserOwnerMiddleware::class);
		$this->app['router']->middleware('oauth-client', \LucaDegasperi\OAuth2Server\Middleware\OAuthClientOwnerMiddleware::class);
		$this->app['router']->middleware('oauth-authorization-params', \LucaDegasperi\OAuth2Server\Middleware\CheckAuthCodeRequestMiddleware::class);
	}

	/**
     * Register the Authorization server with the IoC container.
     *
     * @param \Illuminate\Contracts\Container\Container $app
     *
     * @return void
     */
    public function registerAuthorizer(Application $app)
    {
        $app->singleton('oauth2-server.authorizer', function ($app) {
            $config = $app['config']->get('oauth2');
            $issuer = $app->make(AuthorizationServer::class)
                ->setClientStorage($app->make(ClientInterface::class))
                ->setSessionStorage($app->make(SessionInterface::class))
                ->setAuthCodeStorage($app->make(AuthCodeInterface::class))
                ->setAccessTokenStorage($app->make(AccessTokenInterface::class))
                ->setRefreshTokenStorage($app->make(RefreshTokenInterface::class))
                ->setScopeStorage($app->make(ScopeInterface::class))
                ->requireScopeParam($config['scope_param'])
                ->setDefaultScope($config['default_scope'])
                ->requireStateParam($config['state_param'])
                ->setScopeDelimiter($config['scope_delimiter'])
                ->setAccessTokenTTL($config['access_token_ttl']);

            // add the supported grant types to the authorization server
            foreach ($config['grant_types'] as $grantIdentifier => $grantParams) {
                $grant = $app->make($grantParams['class']);
                $grant->setAccessTokenTTL($grantParams['access_token_ttl']);

                if (array_key_exists('callback', $grantParams)) {
                    list($className, $method) = array_pad(explode('@', $grantParams['callback']), 2, 'verify');
                    $verifier = $app->make($className);
                    $grant->setVerifyCredentialsCallback([$verifier, $method]);
                }

                if (array_key_exists('auth_token_ttl', $grantParams)) {
                    $grant->setAuthTokenTTL($grantParams['auth_token_ttl']);
                }

                if (array_key_exists('refresh_token_ttl', $grantParams)) {
                    $grant->setRefreshTokenTTL($grantParams['refresh_token_ttl']);
                }

                if (array_key_exists('rotate_refresh_tokens', $grantParams)) {
                    $grant->setRefreshTokenRotation($grantParams['rotate_refresh_tokens']);
                }

                $issuer->addGrantType($grant, $grantIdentifier);
            }

            $checker = $app->make(ResourceServer::class);

            $userRepository = $app->make(UserRepositoryContract::class);
            $clientRepository = $app->make(ClientRepositoryContract::class);

            $authorizer = new UserAuthorizer($issuer, $checker, $userRepository, $clientRepository);
            $authorizer->setRequest($app['request']);
            $authorizer->setTokenType($app->make($config['token_type']));

            $app->refresh('request', $authorizer, 'setRequest');

            return $authorizer;
        });

        $app->alias('oauth2-server.authorizer', UserAuthorizer::class);
    }

	/**
	 * Register Verifier
	 *
	 * @return void
	 */
	protected function registerVerifier()
	{
		$this->app->singleton('Cookbook\OAuth2\PasswordGrantVerifier', function($app) {
			return new PasswordGrantVerifier(
				$app->make('Illuminate\Contracts\Auth\UserProvider')
			);
		});
	}

	/**
	 * Register Service Providers for this package
	 *
	 * @return void
	 */
	protected function registerServiceProviders()
	{

		// Repositories
		// -----------------------------------------------------------------------------
		$this->app->register('Cookbook\OAuth2\Repositories\RepositoriesServiceProvider');

		// Handlers
		// -----------------------------------------------------------------------------
		$this->app->register('Cookbook\OAuth2\Handlers\HandlersServiceProvider');

		// Validators
		// -----------------------------------------------------------------------------
		$this->app->register('Cookbook\OAuth2\Validators\ValidatorsServiceProvider');

		// Commands
		// -----------------------------------------------------------------------------
		$this->app->register('Cookbook\OAuth2\Commands\CommandsServiceProvider');

	}

}
