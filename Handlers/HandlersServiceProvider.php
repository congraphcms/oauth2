<?php
/*
 * This file is part of the cookbook/oauth-2 package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\OAuth2\Handlers;

use Illuminate\Support\ServiceProvider;


use Cookbook\OAuth2\Handlers\Commands\Clients\ClientCreateHandler;
use Cookbook\OAuth2\Handlers\Commands\Clients\ClientUpdateHandler;
use Cookbook\OAuth2\Handlers\Commands\Clients\ClientDeleteHandler;
use Cookbook\OAuth2\Handlers\Commands\Clients\ClientFetchHandler;
use Cookbook\OAuth2\Handlers\Commands\Clients\ClientGetHandler;

use Cookbook\OAuth2\Handlers\Commands\Roles\RoleCreateHandler;
use Cookbook\OAuth2\Handlers\Commands\Roles\RoleUpdateHandler;
use Cookbook\OAuth2\Handlers\Commands\Roles\RoleDeleteHandler;
use Cookbook\OAuth2\Handlers\Commands\Roles\RoleFetchHandler;
use Cookbook\OAuth2\Handlers\Commands\Roles\RoleGetHandler;

use Cookbook\OAuth2\Handlers\Commands\Users\UserCreateHandler;
use Cookbook\OAuth2\Handlers\Commands\Users\UserUpdateHandler;
use Cookbook\OAuth2\Handlers\Commands\Users\UserDeleteHandler;
use Cookbook\OAuth2\Handlers\Commands\Users\UserFetchHandler;
use Cookbook\OAuth2\Handlers\Commands\Users\UserGetHandler;
use Cookbook\OAuth2\Handlers\Commands\Users\UserChangePasswordHandler;

/**
 * HandlersServiceProvider service provider for handlers
 * 
 * It will register all handlers to app container
 * 
 * @uses   		Illuminate\Support\ServiceProvider
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/oauth-2
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class HandlersServiceProvider extends ServiceProvider {

	/**
	 * The event listener mappings for package.
	 *
	 * @var array
	 */
	protected $listen = [
	];


	/**
	 * Boot
	 * 
	 * @return void
	 */
	public function boot() {
		$this->mapCommandHandlers();
	}


	/**
	 * Register
	 * 
	 * @return void
	 */
	public function register() {
		$this->registerCommandHandlers();
	}

	/**
	 * Maps Command Handlers
	 *
	 * @return void
	 */
	public function mapCommandHandlers() {
		
		$mappings = [

			// Clients
			'Cookbook\OAuth2\Commands\Clients\ClientCreateCommand' => 
				'Cookbook\OAuth2\Handlers\Commands\Clients\ClientCreateHandler@handle',
			'Cookbook\OAuth2\Commands\Clients\ClientUpdateCommand' => 
				'Cookbook\OAuth2\Handlers\Commands\Clients\ClientUpdateHandler@handle',
			'Cookbook\OAuth2\Commands\Clients\ClientDeleteCommand' => 
				'Cookbook\OAuth2\Handlers\Commands\Clients\ClientDeleteHandler@handle',
			'Cookbook\OAuth2\Commands\Clients\ClientFetchCommand' => 
				'Cookbook\OAuth2\Handlers\Commands\Clients\ClientFetchHandler@handle',
			'Cookbook\OAuth2\Commands\Clients\ClientGetCommand' => 
				'Cookbook\OAuth2\Handlers\Commands\Clients\ClientGetHandler@handle',

			// Roles
			'Cookbook\OAuth2\Commands\Roles\RoleCreateCommand' => 
				'Cookbook\OAuth2\Handlers\Commands\Roles\RoleCreateHandler@handle',
			'Cookbook\OAuth2\Commands\Roles\RoleUpdateCommand' => 
				'Cookbook\OAuth2\Handlers\Commands\Roles\RoleUpdateHandler@handle',
			'Cookbook\OAuth2\Commands\Roles\RoleDeleteCommand' => 
				'Cookbook\OAuth2\Handlers\Commands\Roles\RoleDeleteHandler@handle',
			'Cookbook\OAuth2\Commands\Roles\RoleFetchCommand' => 
				'Cookbook\OAuth2\Handlers\Commands\Roles\RoleFetchHandler@handle',
			'Cookbook\OAuth2\Commands\Roles\RoleGetCommand' => 
				'Cookbook\OAuth2\Handlers\Commands\Roles\RoleGetHandler@handle',

			// Users
			'Cookbook\OAuth2\Commands\Users\UserCreateCommand' => 
				'Cookbook\OAuth2\Handlers\Commands\Users\UserCreateHandler@handle',
			'Cookbook\OAuth2\Commands\Users\UserUpdateCommand' => 
				'Cookbook\OAuth2\Handlers\Commands\Users\UserUpdateHandler@handle',
			'Cookbook\OAuth2\Commands\Users\UserDeleteCommand' => 
				'Cookbook\OAuth2\Handlers\Commands\Users\UserDeleteHandler@handle',
			'Cookbook\OAuth2\Commands\Users\UserFetchCommand' => 
				'Cookbook\OAuth2\Handlers\Commands\Users\UserFetchHandler@handle',
			'Cookbook\OAuth2\Commands\Users\UserGetCommand' => 
				'Cookbook\OAuth2\Handlers\Commands\Users\UserGetHandler@handle',
			'Cookbook\OAuth2\Commands\Users\UserChangePasswordCommand' => 
				'Cookbook\OAuth2\Handlers\Commands\Users\UserChangePasswordHandler@handle',
			
		];

		$this->app->make('Illuminate\Contracts\Bus\Dispatcher')->maps($mappings);
	}

	/**
	 * Registers Command Handlers
	 *
	 * @return void
	 */
	public function registerCommandHandlers() {

		// Clients
		
		$this->app->bind('Cookbook\OAuth2\Handlers\Commands\Clients\ClientCreateHandler', function($app){
			return new ClientCreateHandler($app->make('Cookbook\Contracts\OAuth2\ClientRepositoryContract'));
		});

		$this->app->bind('Cookbook\OAuth2\Handlers\Commands\Clients\ClientUpdateHandler', function($app){
			return new ClientUpdateHandler($app->make('Cookbook\Contracts\OAuth2\ClientRepositoryContract'));
		});

		$this->app->bind('Cookbook\OAuth2\Handlers\Commands\Clients\ClientDeleteHandler', function($app){
			return new ClientDeleteHandler($app->make('Cookbook\Contracts\OAuth2\ClientRepositoryContract'));
		});

		$this->app->bind('Cookbook\OAuth2\Handlers\Commands\Clients\ClientFetchHandler', function($app){
			return new ClientFetchHandler($app->make('Cookbook\Contracts\OAuth2\ClientRepositoryContract'));
		});

		$this->app->bind('Cookbook\OAuth2\Handlers\Commands\Clients\ClientGetHandler', function($app){
			return new ClientGetHandler($app->make('Cookbook\Contracts\OAuth2\ClientRepositoryContract'));
		});


		// Roles
		
		$this->app->bind('Cookbook\OAuth2\Handlers\Commands\Roles\RoleCreateHandler', function($app){
			return new RoleCreateHandler($app->make('Cookbook\Contracts\OAuth2\RoleRepositoryContract'));
		});

		$this->app->bind('Cookbook\OAuth2\Handlers\Commands\Roles\RoleUpdateHandler', function($app){
			return new RoleUpdateHandler($app->make('Cookbook\Contracts\OAuth2\RoleRepositoryContract'));
		});

		$this->app->bind('Cookbook\OAuth2\Handlers\Commands\Roles\RoleDeleteHandler', function($app){
			return new RoleDeleteHandler($app->make('Cookbook\Contracts\OAuth2\RoleRepositoryContract'));
		});

		$this->app->bind('Cookbook\OAuth2\Handlers\Commands\Roles\RoleFetchHandler', function($app){
			return new RoleFetchHandler($app->make('Cookbook\Contracts\OAuth2\RoleRepositoryContract'));
		});

		$this->app->bind('Cookbook\OAuth2\Handlers\Commands\Roles\RoleGetHandler', function($app){
			return new RoleGetHandler($app->make('Cookbook\Contracts\OAuth2\RoleRepositoryContract'));
		});


		// Users
		
		$this->app->bind('Cookbook\OAuth2\Handlers\Commands\Users\UserCreateHandler', function($app){
			return new UserCreateHandler($app->make('Cookbook\Contracts\OAuth2\UserRepositoryContract'));
		});

		$this->app->bind('Cookbook\OAuth2\Handlers\Commands\Users\UserUpdateHandler', function($app){
			return new UserUpdateHandler($app->make('Cookbook\Contracts\OAuth2\UserRepositoryContract'));
		});

		$this->app->bind('Cookbook\OAuth2\Handlers\Commands\Users\UserDeleteHandler', function($app){
			return new UserDeleteHandler(
				$app->make('Cookbook\Contracts\OAuth2\UserRepositoryContract')
				// $app->make('Cookbook\Contracts\Users\ConsumerRepositoryContract')
			);
		});

		$this->app->bind('Cookbook\OAuth2\Handlers\Commands\Users\UserFetchHandler', function($app){
			return new UserFetchHandler($app->make('Cookbook\Contracts\OAuth2\UserRepositoryContract'));
		});

		$this->app->bind('Cookbook\OAuth2\Handlers\Commands\Users\UserGetHandler', function($app){
			return new UserGetHandler($app->make('Cookbook\Contracts\OAuth2\UserRepositoryContract'));
		});

		$this->app->bind('Cookbook\OAuth2\Handlers\Commands\Users\UserChangePasswordHandler', function($app){
			return new UserChangePasswordHandler($app->make('Cookbook\Contracts\OAuth2\UserRepositoryContract'));
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
			// Clients
			'Cookbook\OAuth2\Handlers\Commands\Clients\ClientCreateHandler',
			'Cookbook\OAuth2\Handlers\Commands\Clients\ClientUpdateHandler',
			'Cookbook\OAuth2\Handlers\Commands\Clients\ClientDeleteHandler',
			'Cookbook\OAuth2\Handlers\Commands\Clients\ClientFetchHandler',
			'Cookbook\OAuth2\Handlers\Commands\Clients\ClientGetHandler',


			// Roles
			'Cookbook\OAuth2\Handlers\Commands\Roles\RoleCreateHandler',
			'Cookbook\OAuth2\Handlers\Commands\Roles\RoleUpdateHandler',
			'Cookbook\OAuth2\Handlers\Commands\Roles\RoleDeleteHandler',
			'Cookbook\OAuth2\Handlers\Commands\Roles\RoleFetchHandler',
			'Cookbook\OAuth2\Handlers\Commands\Roles\RoleGetHandler',


			// Users
			'Cookbook\OAuth2\Handlers\Commands\Users\UserCreateHandler',
			'Cookbook\OAuth2\Handlers\Commands\Users\UserUpdateHandler',
			'Cookbook\OAuth2\Handlers\Commands\Users\UserDeleteHandler',
			'Cookbook\OAuth2\Handlers\Commands\Users\UserFetchHandler',
			'Cookbook\OAuth2\Handlers\Commands\Users\UserGetHandler',
			'Cookbook\OAuth2\Handlers\Commands\Users\UserChangePasswordHandler',
		];
	}
}