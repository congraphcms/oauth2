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

		$this->registerMiddleware();
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
	}

	/**
	 * Register Middleware
	 * 
	 * @return void
	 */
	protected function registerMiddleware()
	{
		$this->app['router']->middleware('oauth', \LucaDegasperi\OAuth2Server\Middleware\OAuthMiddleware::class);
		$this->app['router']->middleware('oauth-user', \LucaDegasperi\OAuth2Server\Middleware\OAuthUserOwnerMiddleware::class);
		$this->app['router']->middleware('oauth-client', \LucaDegasperi\OAuth2Server\Middleware\OAuthClientOwnerMiddleware::class);
		$this->app['router']->middleware('oauth-authorization-params', \LucaDegasperi\OAuth2Server\Middleware\CheckAuthCodeRequestMiddleware::class);
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

		// // Repositories
		// // -----------------------------------------------------------------------------
		// $this->app->register('Cookbook\OAuth\Repositories\RepositoriesServiceProvider');
		
		// // Handlers
		// // -----------------------------------------------------------------------------
		// $this->app->register('Cookbook\OAuth\Handlers\HandlersServiceProvider');

		// // Validators
		// // -----------------------------------------------------------------------------
		// $this->app->register('Cookbook\OAuth\Validators\ValidatorsServiceProvider');

		// // Commands
		// // -----------------------------------------------------------------------------
		// $this->app->register('Cookbook\OAuth\Commands\CommandsServiceProvider');

		// // Servers
		// // -----------------------------------------------------------------------------
		// $this->app->register('Cookbook\OAuth\Servers\ServersServiceProvider');

		// // HTTP
		// // -----------------------------------------------------------------------------
		// $this->app->register('Cookbook\OAuth\Http\HttpServiceProvider');

	}

}