<?php
/*
 * This file is part of the congraph/oauth-2 package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Congraph\OAuth2\Handlers;

use Illuminate\Support\ServiceProvider;


use Congraph\OAuth2\Handlers\Commands\Clients\ClientCreateHandler;
use Congraph\OAuth2\Handlers\Commands\Clients\ClientUpdateHandler;
use Congraph\OAuth2\Handlers\Commands\Clients\ClientDeleteHandler;
use Congraph\OAuth2\Handlers\Commands\Clients\ClientFetchHandler;
use Congraph\OAuth2\Handlers\Commands\Clients\ClientGetHandler;

use Congraph\OAuth2\Handlers\Commands\Roles\RoleCreateHandler;
use Congraph\OAuth2\Handlers\Commands\Roles\RoleUpdateHandler;
use Congraph\OAuth2\Handlers\Commands\Roles\RoleDeleteHandler;
use Congraph\OAuth2\Handlers\Commands\Roles\RoleFetchHandler;
use Congraph\OAuth2\Handlers\Commands\Roles\RoleGetHandler;

use Congraph\OAuth2\Handlers\Commands\Users\UserCreateHandler;
use Congraph\OAuth2\Handlers\Commands\Users\UserUpdateHandler;
use Congraph\OAuth2\Handlers\Commands\Users\UserDeleteHandler;
use Congraph\OAuth2\Handlers\Commands\Users\UserFetchHandler;
use Congraph\OAuth2\Handlers\Commands\Users\UserGetHandler;
use Congraph\OAuth2\Handlers\Commands\Users\UserChangePasswordHandler;

/**
 * HandlersServiceProvider service provider for handlers
 * 
 * It will register all handlers to app container
 * 
 * @uses   		Illuminate\Support\ServiceProvider
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/oauth-2
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
			'Congraph\OAuth2\Commands\Clients\ClientCreateCommand' => 
				'Congraph\OAuth2\Handlers\Commands\Clients\ClientCreateHandler@handle',
			'Congraph\OAuth2\Commands\Clients\ClientUpdateCommand' => 
				'Congraph\OAuth2\Handlers\Commands\Clients\ClientUpdateHandler@handle',
			'Congraph\OAuth2\Commands\Clients\ClientDeleteCommand' => 
				'Congraph\OAuth2\Handlers\Commands\Clients\ClientDeleteHandler@handle',
			'Congraph\OAuth2\Commands\Clients\ClientFetchCommand' => 
				'Congraph\OAuth2\Handlers\Commands\Clients\ClientFetchHandler@handle',
			'Congraph\OAuth2\Commands\Clients\ClientGetCommand' => 
				'Congraph\OAuth2\Handlers\Commands\Clients\ClientGetHandler@handle',

			// Roles
			'Congraph\OAuth2\Commands\Roles\RoleCreateCommand' => 
				'Congraph\OAuth2\Handlers\Commands\Roles\RoleCreateHandler@handle',
			'Congraph\OAuth2\Commands\Roles\RoleUpdateCommand' => 
				'Congraph\OAuth2\Handlers\Commands\Roles\RoleUpdateHandler@handle',
			'Congraph\OAuth2\Commands\Roles\RoleDeleteCommand' => 
				'Congraph\OAuth2\Handlers\Commands\Roles\RoleDeleteHandler@handle',
			'Congraph\OAuth2\Commands\Roles\RoleFetchCommand' => 
				'Congraph\OAuth2\Handlers\Commands\Roles\RoleFetchHandler@handle',
			'Congraph\OAuth2\Commands\Roles\RoleGetCommand' => 
				'Congraph\OAuth2\Handlers\Commands\Roles\RoleGetHandler@handle',

			// Users
			'Congraph\OAuth2\Commands\Users\UserCreateCommand' => 
				'Congraph\OAuth2\Handlers\Commands\Users\UserCreateHandler@handle',
			'Congraph\OAuth2\Commands\Users\UserUpdateCommand' => 
				'Congraph\OAuth2\Handlers\Commands\Users\UserUpdateHandler@handle',
			'Congraph\OAuth2\Commands\Users\UserDeleteCommand' => 
				'Congraph\OAuth2\Handlers\Commands\Users\UserDeleteHandler@handle',
			'Congraph\OAuth2\Commands\Users\UserFetchCommand' => 
				'Congraph\OAuth2\Handlers\Commands\Users\UserFetchHandler@handle',
			'Congraph\OAuth2\Commands\Users\UserGetCommand' => 
				'Congraph\OAuth2\Handlers\Commands\Users\UserGetHandler@handle',
			'Congraph\OAuth2\Commands\Users\UserChangePasswordCommand' => 
				'Congraph\OAuth2\Handlers\Commands\Users\UserChangePasswordHandler@handle',
			
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
		
		$this->app->bind('Congraph\OAuth2\Handlers\Commands\Clients\ClientCreateHandler', function($app){
			return new ClientCreateHandler($app->make('Congraph\Contracts\OAuth2\ClientRepositoryContract'));
		});

		$this->app->bind('Congraph\OAuth2\Handlers\Commands\Clients\ClientUpdateHandler', function($app){
			return new ClientUpdateHandler($app->make('Congraph\Contracts\OAuth2\ClientRepositoryContract'));
		});

		$this->app->bind('Congraph\OAuth2\Handlers\Commands\Clients\ClientDeleteHandler', function($app){
			return new ClientDeleteHandler($app->make('Congraph\Contracts\OAuth2\ClientRepositoryContract'));
		});

		$this->app->bind('Congraph\OAuth2\Handlers\Commands\Clients\ClientFetchHandler', function($app){
			return new ClientFetchHandler($app->make('Congraph\Contracts\OAuth2\ClientRepositoryContract'));
		});

		$this->app->bind('Congraph\OAuth2\Handlers\Commands\Clients\ClientGetHandler', function($app){
			return new ClientGetHandler($app->make('Congraph\Contracts\OAuth2\ClientRepositoryContract'));
		});


		// Roles
		
		$this->app->bind('Congraph\OAuth2\Handlers\Commands\Roles\RoleCreateHandler', function($app){
			return new RoleCreateHandler($app->make('Congraph\Contracts\OAuth2\RoleRepositoryContract'));
		});

		$this->app->bind('Congraph\OAuth2\Handlers\Commands\Roles\RoleUpdateHandler', function($app){
			return new RoleUpdateHandler($app->make('Congraph\Contracts\OAuth2\RoleRepositoryContract'));
		});

		$this->app->bind('Congraph\OAuth2\Handlers\Commands\Roles\RoleDeleteHandler', function($app){
			return new RoleDeleteHandler($app->make('Congraph\Contracts\OAuth2\RoleRepositoryContract'));
		});

		$this->app->bind('Congraph\OAuth2\Handlers\Commands\Roles\RoleFetchHandler', function($app){
			return new RoleFetchHandler($app->make('Congraph\Contracts\OAuth2\RoleRepositoryContract'));
		});

		$this->app->bind('Congraph\OAuth2\Handlers\Commands\Roles\RoleGetHandler', function($app){
			return new RoleGetHandler($app->make('Congraph\Contracts\OAuth2\RoleRepositoryContract'));
		});


		// Users
		
		$this->app->bind('Congraph\OAuth2\Handlers\Commands\Users\UserCreateHandler', function($app){
			return new UserCreateHandler($app->make('Congraph\Contracts\OAuth2\UserRepositoryContract'));
		});

		$this->app->bind('Congraph\OAuth2\Handlers\Commands\Users\UserUpdateHandler', function($app){
			return new UserUpdateHandler($app->make('Congraph\Contracts\OAuth2\UserRepositoryContract'));
		});

		$this->app->bind('Congraph\OAuth2\Handlers\Commands\Users\UserDeleteHandler', function($app){
			return new UserDeleteHandler(
				$app->make('Congraph\Contracts\OAuth2\UserRepositoryContract')
				// $app->make('Congraph\Contracts\Users\ConsumerRepositoryContract')
			);
		});

		$this->app->bind('Congraph\OAuth2\Handlers\Commands\Users\UserFetchHandler', function($app){
			return new UserFetchHandler($app->make('Congraph\Contracts\OAuth2\UserRepositoryContract'));
		});

		$this->app->bind('Congraph\OAuth2\Handlers\Commands\Users\UserGetHandler', function($app){
			return new UserGetHandler($app->make('Congraph\Contracts\OAuth2\UserRepositoryContract'));
		});

		$this->app->bind('Congraph\OAuth2\Handlers\Commands\Users\UserChangePasswordHandler', function($app){
			return new UserChangePasswordHandler($app->make('Congraph\Contracts\OAuth2\UserRepositoryContract'));
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
			'Congraph\OAuth2\Handlers\Commands\Clients\ClientCreateHandler',
			'Congraph\OAuth2\Handlers\Commands\Clients\ClientUpdateHandler',
			'Congraph\OAuth2\Handlers\Commands\Clients\ClientDeleteHandler',
			'Congraph\OAuth2\Handlers\Commands\Clients\ClientFetchHandler',
			'Congraph\OAuth2\Handlers\Commands\Clients\ClientGetHandler',


			// Roles
			'Congraph\OAuth2\Handlers\Commands\Roles\RoleCreateHandler',
			'Congraph\OAuth2\Handlers\Commands\Roles\RoleUpdateHandler',
			'Congraph\OAuth2\Handlers\Commands\Roles\RoleDeleteHandler',
			'Congraph\OAuth2\Handlers\Commands\Roles\RoleFetchHandler',
			'Congraph\OAuth2\Handlers\Commands\Roles\RoleGetHandler',


			// Users
			'Congraph\OAuth2\Handlers\Commands\Users\UserCreateHandler',
			'Congraph\OAuth2\Handlers\Commands\Users\UserUpdateHandler',
			'Congraph\OAuth2\Handlers\Commands\Users\UserDeleteHandler',
			'Congraph\OAuth2\Handlers\Commands\Users\UserFetchHandler',
			'Congraph\OAuth2\Handlers\Commands\Users\UserGetHandler',
			'Congraph\OAuth2\Handlers\Commands\Users\UserChangePasswordHandler',
		];
	}
}