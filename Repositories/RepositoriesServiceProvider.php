<?php
/*
 * This file is part of the congraph/oauth-2 package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Congraph\OAuth2\Repositories;

use Illuminate\Support\ServiceProvider;
use Congraph\Contracts\OAuth2\ScopeRepositoryContract;
use League\OAuth2\Server\Storage\ScopeInterface;

/**
 * RepositoriesServiceProvider service provider for repositories
 * 
 * It will register all repositories to app container
 * 
 * @uses   		Illuminate\Support\ServiceProvider
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/oauth-2
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class RepositoriesServiceProvider extends ServiceProvider {

	/**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
	protected $defer = true;

	/**
	 * Boot
	 * @return void
	 */
	public function boot()
	{
		$this->mapObjectResolvers();
	}
	
	/**
	 * Register
	 * 
	 * @return void
	 */
	public function register()
	{
		$this->registerRepositories();
		$this->registerAuthDriver();
	}

	/**
	 * Register repositories to Container
	 *
	 * @return void
	 */
	protected function registerRepositories()
	{

		$this->app->singleton('Congraph\OAuth2\Repositories\ClientRepository', function($app) {
			return new ClientRepository(
				$app['db']->connection()
			);
		});

		$this->app->alias(
			'Congraph\OAuth2\Repositories\ClientRepository', 'Congraph\Contracts\OAuth2\ClientRepositoryContract'
		);

		$this->app->singleton('Congraph\OAuth2\Repositories\RoleRepository', function($app) {
			return new RoleRepository(
				$app['db']->connection()
			);
		});

		$this->app->alias(
			'Congraph\OAuth2\Repositories\RoleRepository', 'Congraph\Contracts\OAuth2\RoleRepositoryContract'
		);


		$this->app->singleton('Congraph\OAuth2\Repositories\UserRepository', function($app) {
			return new UserRepository(
				$app['db']->connection()
			);
		});

		$this->app->alias(
			'Congraph\OAuth2\Repositories\UserRepository', 'Congraph\Contracts\OAuth2\UserRepositoryContract'
		);

		$this->app->alias(
			'Congraph\OAuth2\Repositories\UserRepository', 'Illuminate\Contracts\Auth\UserProvider'
		);

		$this->app->singleton('Congraph\OAuth2\Repositories\ScopeRepository', function($app) {
			return new ScopeRepository(
				$app['db']->connection()
			);
		});

		$this->app->bind(ScopeRepositoryContract::class, ScopeRepository::class);
        $this->app->bind(ScopeInterface::class, ScopeRepository::class);

	}

	/**
	 * Map repositories to object resolver
	 *
	 * @return void
	 */
	protected function mapObjectResolvers()
	{
		$mappings = [
			'client' => 'Congraph\OAuth2\Repositories\ClientRepository',
			'role' => 'Congraph\OAuth2\Repositories\RoleRepository',
			'user' => 'Congraph\Users\Repositories\UserRepository',
			'scope' => 'Congraph\Users\Repositories\ScopeRepository',
		];

		$this->app->make('Congraph\Contracts\Core\ObjectResolverContract')->maps($mappings);
	}

	/**
	 * Register UserRepository as Laravel Auth Driver
	 *
	 * @return void
	 */
	protected function registerAuthDriver()
	{
		$app = $this->app;
		$auth = $this->app['auth'];
		$auth->extend('repository', function() use( $app ){
			return $app['Congraph\Contracts\OAuth2\UserRepositoryContract'];
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return [
			'Congraph\OAuth2\Repositories\ClientRepository',
			'Congraph\Contracts\OAuth2\ClientRepositoryContract',
			'Congraph\OAuth2\Repositories\RoleRepository',
			'Congraph\Contracts\OAuth2\RoleRepositoryContract',
			'Congraph\Users\Repositories\UserRepository',
			'Congraph\Contracts\Users\UserRepositoryContract',
		];
	}


}