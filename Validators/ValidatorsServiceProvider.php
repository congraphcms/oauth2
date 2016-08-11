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
			return new ClientCreateValidator();
		});

		$this->app->bind('Cookbook\OAuth2\Validators\Clients\ClientUpdateValidator', function($app){
			return new ClientUpdateValidator();
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
			'Cookbook\OAuth2\Validators\Clients\ClientGetValidator'

		];
	}
}