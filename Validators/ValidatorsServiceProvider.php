<?php
/*
 * This file is part of the congraph/oauth-2 package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Congraph\OAuth2\Validators;

use Illuminate\Support\ServiceProvider;

use Congraph\OAuth2\Validators\Clients\ClientCreateValidator;
use Congraph\OAuth2\Validators\Clients\ClientUpdateValidator;
use Congraph\OAuth2\Validators\Clients\ClientDeleteValidator;
use Congraph\OAuth2\Validators\Clients\ClientFetchValidator;
use Congraph\OAuth2\Validators\Clients\ClientGetValidator;

use Congraph\OAuth2\Validators\Roles\RoleCreateValidator;
use Congraph\OAuth2\Validators\Roles\RoleUpdateValidator;
use Congraph\OAuth2\Validators\Roles\RoleDeleteValidator;
use Congraph\OAuth2\Validators\Roles\RoleFetchValidator;
use Congraph\OAuth2\Validators\Roles\RoleGetValidator;

use Congraph\OAuth2\Validators\Users\UserCreateValidator;
use Congraph\OAuth2\Validators\Users\UserUpdateValidator;
use Congraph\OAuth2\Validators\Users\UserDeleteValidator;
use Congraph\OAuth2\Validators\Users\UserFetchValidator;
use Congraph\OAuth2\Validators\Users\UserGetValidator;
use Congraph\OAuth2\Validators\Users\UserChangePasswordValidator;


/**
 * ValidatorsServiceProvider service provider for validators
 * 
 * It will register all validators to app container
 * 
 * @uses   		Illuminate\Support\ServiceProvider
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/oauth-2
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
			'Congraph\OAuth2\Commands\Clients\ClientCreateCommand' => 
				'Congraph\OAuth2\Validators\Clients\ClientCreateValidator@validate',
			'Congraph\OAuth2\Commands\Clients\ClientUpdateCommand' => 
				'Congraph\OAuth2\Validators\Clients\ClientUpdateValidator@validate',
			'Congraph\OAuth2\Commands\Clients\ClientDeleteCommand' => 
				'Congraph\OAuth2\Validators\Clients\ClientDeleteValidator@validate',
			'Congraph\OAuth2\Commands\Clients\ClientFetchCommand' => 
				'Congraph\OAuth2\Validators\Clients\ClientFetchValidator@validate',
			'Congraph\OAuth2\Commands\Clients\ClientGetCommand' => 
				'Congraph\OAuth2\Validators\Clients\ClientGetValidator@validate',

			// Roles
			'Congraph\OAuth2\Commands\Roles\RoleCreateCommand' => 
				'Congraph\OAuth2\Validators\Roles\RoleCreateValidator@validate',
			'Congraph\OAuth2\Commands\Roles\RoleUpdateCommand' => 
				'Congraph\OAuth2\Validators\Roles\RoleUpdateValidator@validate',
			'Congraph\OAuth2\Commands\Roles\RoleDeleteCommand' => 
				'Congraph\OAuth2\Validators\Roles\RoleDeleteValidator@validate',
			'Congraph\OAuth2\Commands\Roles\RoleFetchCommand' => 
				'Congraph\OAuth2\Validators\Roles\RoleFetchValidator@validate',
			'Congraph\OAuth2\Commands\Roles\RoleGetCommand' => 
				'Congraph\OAuth2\Validators\Roles\RoleGetValidator@validate',

			// Users
			'Congraph\OAuth2\Commands\Users\UserCreateCommand' => 
				'Congraph\OAuth2\Validators\Users\UserCreateValidator@validate',
			'Congraph\OAuth2\Commands\Users\UserUpdateCommand' => 
				'Congraph\OAuth2\Validators\Users\UserUpdateValidator@validate',
			'Congraph\OAuth2\Commands\Users\UserDeleteCommand' => 
				'Congraph\OAuth2\Validators\Users\UserDeleteValidator@validate',
			'Congraph\OAuth2\Commands\Users\UserFetchCommand' => 
				'Congraph\OAuth2\Validators\Users\UserFetchValidator@validate',
			'Congraph\OAuth2\Commands\Users\UserGetCommand' => 
				'Congraph\OAuth2\Validators\Users\UserGetValidator@validate',
			'Congraph\OAuth2\Commands\Users\UserChangePasswordCommand' => 
				'Congraph\OAuth2\Validators\Users\UserChangePasswordValidator@validate',
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
		$this->app->bind('Congraph\OAuth2\Validators\Clients\ClientCreateValidator', function($app){
			return new ClientCreateValidator($app->make('Congraph\Contracts\OAuth2\ScopeRepositoryContract'));
		});

		$this->app->bind('Congraph\OAuth2\Validators\Clients\ClientUpdateValidator', function($app){
			return new ClientUpdateValidator($app->make('Congraph\Contracts\OAuth2\ScopeRepositoryContract'));
		});

		$this->app->bind('Congraph\OAuth2\Validators\Clients\ClientDeleteValidator', function($app){
			return new ClientDeleteValidator();
		});

		$this->app->bind('Congraph\OAuth2\Validators\Clients\ClientFetchValidator', function($app){
			return new ClientFetchValidator();
		});

		$this->app->bind('Congraph\OAuth2\Validators\Clients\ClientGetValidator', function($app){
			return new ClientGetValidator();
		});


		// Roles
		$this->app->bind('Congraph\OAuth2\Validators\Roles\RoleCreateValidator', function($app){
			return new RoleCreateValidator($app->make('Congraph\Contracts\OAuth2\ScopeRepositoryContract'));
		});

		$this->app->bind('Congraph\OAuth2\Validators\Roles\RoleUpdateValidator', function($app){
			return new RoleUpdateValidator($app->make('Congraph\Contracts\OAuth2\ScopeRepositoryContract'));
		});

		$this->app->bind('Congraph\OAuth2\Validators\Roles\RoleDeleteValidator', function($app){
			return new RoleDeleteValidator();
		});

		$this->app->bind('Congraph\OAuth2\Validators\Roles\RoleFetchValidator', function($app){
			return new RoleFetchValidator();
		});

		$this->app->bind('Congraph\OAuth2\Validators\Roles\RoleGetValidator', function($app){
			return new RoleGetValidator();
		});


		// Users
		$this->app->bind('Congraph\OAuth2\Validators\Users\UserCreateValidator', function($app){
			return new UserCreateValidator();
		});

		$this->app->bind('Congraph\OAuth2\Validators\Users\UserUpdateValidator', function($app){
			return new UserUpdateValidator();
		});

		$this->app->bind('Congraph\OAuth2\Validators\Users\UserDeleteValidator', function($app){
			return new UserDeleteValidator();
		});

		$this->app->bind('Congraph\OAuth2\Validators\Users\UserFetchValidator', function($app){
			return new UserFetchValidator();
		});

		$this->app->bind('Congraph\OAuth2\Validators\Users\UserGetValidator', function($app){
			return new UserGetValidator();
		});

		$this->app->bind('Congraph\OAuth2\Validators\Users\UserChangePasswordValidator', function($app){
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
			'Congraph\OAuth2\Validators\Clients\ClientCreateValidator',
			'Congraph\OAuth2\Validators\Clients\ClientUpdateValidator',
			'Congraph\OAuth2\Validators\Clients\ClientDeleteValidator',
			'Congraph\OAuth2\Validators\Clients\ClientFetchValidator',
			'Congraph\OAuth2\Validators\Clients\ClientGetValidator',


			// Roles
			'Congraph\OAuth2\Validators\Roles\RoleCreateValidator',
			'Congraph\OAuth2\Validators\Roles\RoleUpdateValidator',
			'Congraph\OAuth2\Validators\Roles\RoleDeleteValidator',
			'Congraph\OAuth2\Validators\Roles\RoleFetchValidator',
			'Congraph\OAuth2\Validators\Roles\RoleGetValidator',


			// Users
			'Congraph\OAuth2\Validators\Users\UserCreateValidator',
			'Congraph\OAuth2\Validators\Users\UserUpdateValidator',
			'Congraph\OAuth2\Validators\Users\UserDeleteValidator',
			'Congraph\OAuth2\Validators\Users\UserFetchValidator',
			'Congraph\OAuth2\Validators\Users\UserGetValidator',
			'Congraph\OAuth2\Validators\Users\UserChangePasswordValidator',

		];
	}
}