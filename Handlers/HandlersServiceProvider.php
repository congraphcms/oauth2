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
		];
	}
}