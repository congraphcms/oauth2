<?php
/*
 * This file is part of the cookbook/oauth-2 package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\OAuth2\Validators;

use Illuminate\Support\ServiceProvider;

use Cookbook\OAuth2\Validators\Clients\ClientCreateValidator;
use Cookbook\OAuth2\Validators\Clients\ClientUpdateValidator;
use Cookbook\OAuth2\Validators\Clients\ClientDeleteValidator;
use Cookbook\OAuth2\Validators\Clients\ClientFetchValidator;
use Cookbook\OAuth2\Validators\Clients\ClientGetValidator;

use Cookbook\OAuth2\Validators\Roles\RoleCreateValidator;
use Cookbook\OAuth2\Validators\Roles\RoleUpdateValidator;
use Cookbook\OAuth2\Validators\Roles\RoleDeleteValidator;
use Cookbook\OAuth2\Validators\Roles\RoleFetchValidator;
use Cookbook\OAuth2\Validators\Roles\RoleGetValidator;

use Cookbook\OAuth2\Validators\Users\UserCreateValidator;
use Cookbook\OAuth2\Validators\Users\UserUpdateValidator;
use Cookbook\OAuth2\Validators\Users\UserDeleteValidator;
use Cookbook\OAuth2\Validators\Users\UserFetchValidator;
use Cookbook\OAuth2\Validators\Users\UserGetValidator;
use Cookbook\OAuth2\Validators\Users\UserChangePasswordValidator;


/**
 * ValidatorsServiceProvider service provider for validators
 * 
 * It will register all validators to app container
 * 
 * @uses   		Illuminate\Support\ServiceProvider
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/oauth-2
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class ValidatorsServiceProvider extends ServiceProvider {

	/**
	 * Boot
	 * 
	 * @return void
	 */
	public function boot() {
		$this->mapValidators();
	}


	/**
	 * Register
	 * 
	 * @return void
	 */
	public function register() {
		$this->registerValidators();
	}

	/**
	 * Maps Validators
	 *
	 * @return void
	 */
	public function mapValidators() {
		
		$mappings = [

			// Clients
			'Cookbook\OAuth2\Commands\Clients\ClientCreateCommand' => 
				'Cookbook\OAuth2\Validators\Clients\ClientCreateValidator@validate',
			'Cookbook\OAuth2\Commands\Clients\ClientUpdateCommand' => 
				'Cookbook\OAuth2\Validators\Clients\ClientUpdateValidator@validate',
			'Cookbook\OAuth2\Commands\Clients\ClientDeleteCommand' => 
				'Cookbook\OAuth2\Validators\Clients\ClientDeleteValidator@validate',
			'Cookbook\OAuth2\Commands\Clients\ClientFetchCommand' => 
				'Cookbook\OAuth2\Validators\Clients\ClientFetchValidator@validate',
			'Cookbook\OAuth2\Commands\Clients\ClientGetCommand' => 
				'Cookbook\OAuth2\Validators\Clients\ClientGetValidator@validate',

			// Roles
			'Cookbook\OAuth2\Commands\Roles\RoleCreateCommand' => 
				'Cookbook\OAuth2\Validators\Roles\RoleCreateValidator@validate',
			'Cookbook\OAuth2\Commands\Roles\RoleUpdateCommand' => 
				'Cookbook\OAuth2\Validators\Roles\RoleUpdateValidator@validate',
			'Cookbook\OAuth2\Commands\Roles\RoleDeleteCommand' => 
				'Cookbook\OAuth2\Validators\Roles\RoleDeleteValidator@validate',
			'Cookbook\OAuth2\Commands\Roles\RoleFetchCommand' => 
				'Cookbook\OAuth2\Validators\Roles\RoleFetchValidator@validate',
			'Cookbook\OAuth2\Commands\Roles\RoleGetCommand' => 
				'Cookbook\OAuth2\Validators\Roles\RoleGetValidator@validate',

			// Users
			'Cookbook\OAuth2\Commands\Users\UserCreateCommand' => 
				'Cookbook\OAuth2\Validators\Users\UserCreateValidator@validate',
			'Cookbook\OAuth2\Commands\Users\UserUpdateCommand' => 
				'Cookbook\OAuth2\Validators\Users\UserUpdateValidator@validate',
			'Cookbook\OAuth2\Commands\Users\UserDeleteCommand' => 
				'Cookbook\OAuth2\Validators\Users\UserDeleteValidator@validate',
			'Cookbook\OAuth2\Commands\Users\UserFetchCommand' => 
				'Cookbook\OAuth2\Validators\Users\UserFetchValidator@validate',
			'Cookbook\OAuth2\Commands\Users\UserGetCommand' => 
				'Cookbook\OAuth2\Validators\Users\UserGetValidator@validate',
			'Cookbook\OAuth2\Commands\Users\UserChangePasswordCommand' => 
				'Cookbook\OAuth2\Validators\Users\UserChangePasswordValidator@validate',
		];

		$this->app->make('Illuminate\Contracts\Bus\Dispatcher')->mapValidators($mappings);
	}

	/**
	 * Registers Command Handlers
	 *
	 * @return void
	 */
	public function registerValidators() {

		// Clients
		$this->app->bind('Cookbook\OAuth2\Validators\Clients\ClientCreateValidator', function($app){
			return new ClientCreateValidator($app->make('Cookbook\Contracts\OAuth2\ScopeRepositoryContract'));
		});

		$this->app->bind('Cookbook\OAuth2\Validators\Clients\ClientUpdateValidator', function($app){
			return new ClientUpdateValidator($app->make('Cookbook\Contracts\OAuth2\ScopeRepositoryContract'));
		});

		$this->app->bind('Cookbook\OAuth2\Validators\Clients\ClientDeleteValidator', function($app){
			return new ClientDeleteValidator();
		});

		$this->app->bind('Cookbook\OAuth2\Validators\Clients\ClientFetchValidator', function($app){
			return new ClientFetchValidator();
		});

		$this->app->bind('Cookbook\OAuth2\Validators\Clients\ClientGetValidator', function($app){
			return new ClientGetValidator();
		});


		// Roles
		$this->app->bind('Cookbook\OAuth2\Validators\Roles\RoleCreateValidator', function($app){
			return new RoleCreateValidator($app->make('Cookbook\Contracts\OAuth2\ScopeRepositoryContract'));
		});

		$this->app->bind('Cookbook\OAuth2\Validators\Roles\RoleUpdateValidator', function($app){
			return new RoleUpdateValidator($app->make('Cookbook\Contracts\OAuth2\ScopeRepositoryContract'));
		});

		$this->app->bind('Cookbook\OAuth2\Validators\Roles\RoleDeleteValidator', function($app){
			return new RoleDeleteValidator();
		});

		$this->app->bind('Cookbook\OAuth2\Validators\Roles\RoleFetchValidator', function($app){
			return new RoleFetchValidator();
		});

		$this->app->bind('Cookbook\OAuth2\Validators\Roles\RoleGetValidator', function($app){
			return new RoleGetValidator();
		});


		// Users
		$this->app->bind('Cookbook\OAuth2\Validators\Users\UserCreateValidator', function($app){
			return new UserCreateValidator();
		});

		$this->app->bind('Cookbook\OAuth2\Validators\Users\UserUpdateValidator', function($app){
			return new UserUpdateValidator();
		});

		$this->app->bind('Cookbook\OAuth2\Validators\Users\UserDeleteValidator', function($app){
			return new UserDeleteValidator();
		});

		$this->app->bind('Cookbook\OAuth2\Validators\Users\UserFetchValidator', function($app){
			return new UserFetchValidator();
		});

		$this->app->bind('Cookbook\OAuth2\Validators\Users\UserGetValidator', function($app){
			return new UserGetValidator();
		});

		$this->app->bind('Cookbook\OAuth2\Validators\Users\UserChangePasswordValidator', function($app){
			return new UserChangePasswordValidator();
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
			'Cookbook\OAuth2\Validators\Clients\ClientCreateValidator',
			'Cookbook\OAuth2\Validators\Clients\ClientUpdateValidator',
			'Cookbook\OAuth2\Validators\Clients\ClientDeleteValidator',
			'Cookbook\OAuth2\Validators\Clients\ClientFetchValidator',
			'Cookbook\OAuth2\Validators\Clients\ClientGetValidator',


			// Roles
			'Cookbook\OAuth2\Validators\Roles\RoleCreateValidator',
			'Cookbook\OAuth2\Validators\Roles\RoleUpdateValidator',
			'Cookbook\OAuth2\Validators\Roles\RoleDeleteValidator',
			'Cookbook\OAuth2\Validators\Roles\RoleFetchValidator',
			'Cookbook\OAuth2\Validators\Roles\RoleGetValidator',


			// Users
			'Cookbook\OAuth2\Validators\Users\UserCreateValidator',
			'Cookbook\OAuth2\Validators\Users\UserUpdateValidator',
			'Cookbook\OAuth2\Validators\Users\UserDeleteValidator',
			'Cookbook\OAuth2\Validators\Users\UserFetchValidator',
			'Cookbook\OAuth2\Validators\Users\UserGetValidator',
			'Cookbook\OAuth2\Validators\Users\UserChangePasswordValidator',

		];
	}
}