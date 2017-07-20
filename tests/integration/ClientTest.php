<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Debug\Dumper;

require_once(__DIR__ . '/../database/seeders/UserTestDbSeeder.php');
require_once(__DIR__ . '/../database/seeders/ScopeTestDbSeeder.php');
require_once(__DIR__ . '/../database/seeders/ClientTestDbSeeder.php');
require_once(__DIR__ . '/../database/seeders/ClearDB.php');

class ClientTest extends Orchestra\Testbench\TestCase
{

	public function setUp()
	{
		parent::setUp();

		$this->artisan('migrate', [
			'--database' => 'testbench',
			'--realpath' => realpath(__DIR__.'/../../vendor/lucadegasperi/oauth2-server-laravel/database/migrations'),
		]);

		// $this->artisan('migrate', [
		// 	'--database' => 'testbench',
		// 	'--realpath' => realpath(__DIR__.'/../../vendor/cookbook/users/database/migrations'),
		// ]);

		$this->artisan('db:seed', [
			'--class' => 'UserTestDbSeeder'
		]);
		$this->artisan('db:seed', [
			'--class' => 'ScopeTestDbSeeder'
		]);
		$this->artisan('db:seed', [
			'--class' => 'ClientTestDbSeeder'
		]);

		$this->d = new Dumper();


	}

	public function tearDown()
	{
		$this->artisan('db:seed', [
			'--class' => 'ClearDB'
		]);
		parent::tearDown();
	}

	/**
	 * Define environment setup.
	 *
	 * @param  \Illuminate\Foundation\Application  $app
	 *
	 * @return void
	 */
	protected function getEnvironmentSetUp($app)
	{
		$app['config']->set('database.default', 'testbench');
		$app['config']->set('database.connections.testbench', [
			'driver'   	=> 'mysql',
			'host'      => '127.0.0.1',
			'port'		=> '3306',
			'database'	=> 'cookbook_testbench',
			'username'  => 'root',
			'password'  => '',
			'charset'   => 'utf8',
			'collation' => 'utf8_unicode_ci',
			'prefix'    => '',
		]);

	}

	protected function getPackageProviders($app)
	{
		return [
			'Cookbook\OAuth2\OAuth2ServiceProvider',
			// 'Cookbook\Users\UsersServiceProvider', 
			'Cookbook\Core\CoreServiceProvider',
			'LucaDegasperi\OAuth2Server\Storage\FluentStorageServiceProvider',
			'LucaDegasperi\OAuth2Server\OAuth2ServerServiceProvider'
		];
	}

	public function testCreateClient()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'name' => 'Jane\'s Mobile App',
			'scopes' => ['manage_entities'],
			'grants' => ['client_credentials']
		];


		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Cookbook\OAuth2\Commands\Clients\ClientCreateCommand($params));

		$this->d->dump($result->toArray());
		$this->assertEquals('Jane\'s Mobile App', $result->name);
		$this->assertEquals(40, strlen($result->id));
		$this->assertEquals(40, strlen($result->secret));

		$this->seeInDatabase('oauth_clients', ['id' => $result->id, 'secret' => $result->secret, 'name' => 'Jane\'s Mobile App']);
		$this->seeInDatabase('oauth_client_scopes', ['client_id' => $result->id, 'scope_id' => 'manage_entities']);
		$this->seeInDatabase('oauth_client_grants', ['client_id' => $result->id, 'grant_id' => 'client_credentials']);
	}


	public function testUpdateClient()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$params = [
			'name' => 'Margaret\'s App',
			'scopes' => ['manage_content_model'],
			'grants' => ['client_credentials']
		];

		$result = $bus->dispatch( new Cookbook\OAuth2\Commands\Clients\ClientUpdateCommand($params, 'iuqp7E9myPGkoKuyvI9Jo06gIor2WsiivuUbuobR'));

		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Model);
		$this->assertTrue(is_string($result->id));
		$this->assertEquals('iuqp7E9myPGkoKuyvI9Jo06gIor2WsiivuUbuobR', $result->id);
		$this->assertEquals('Margaret\'s App', $result->name);

		$this->seeInDatabase('oauth_clients', ['id' => 'iuqp7E9myPGkoKuyvI9Jo06gIor2WsiivuUbuobR', 'name' => 'Margaret\'s App']);
		$this->seeInDatabase('oauth_client_scopes', ['client_id' => $result->id, 'scope_id' => 'manage_content_model']);
		$this->seeInDatabase('oauth_client_grants', ['client_id' => $result->id, 'grant_id' => 'client_credentials']);
		$this->dontSeeInDatabase('oauth_client_scopes', ['client_id' => $result->id, 'scope_id' => 'manage_entities']);
		$this->dontSeeInDatabase('oauth_client_grants', ['client_id' => $result->id, 'grant_id' => 'password']);

		$this->d->dump($result->toArray());
	}


	public function testDeleteClient()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Cookbook\OAuth2\Commands\Clients\ClientDeleteCommand([], 'iuqp7E9myPGkoKuyvI9Jo06gIor2WsiivuUbuobR'));

		$this->assertEquals('iuqp7E9myPGkoKuyvI9Jo06gIor2WsiivuUbuobR', $result);
		$this->d->dump($result);
		$this->dontSeeInDatabase('oauth_clients', ['id' => 'iuqp7E9myPGkoKuyvI9Jo06gIor2WsiivuUbuobR']);
	}

	/**
	 * @expectedException \Cookbook\Core\Exceptions\NotFoundException
	 */
	public function testDeleteException()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Cookbook\OAuth2\Commands\Clients\ClientDeleteCommand([], '123'));
	}

	public function testFetchClient()
	{

		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Cookbook\OAuth2\Commands\Clients\ClientFetchCommand([], 'iuqp7E9myPGkoKuyvI9Jo06gIor2WsiivuUbuobR'));

		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Model);
		$this->assertTrue(is_string($result->id));
		$this->assertEquals('iuqp7E9myPGkoKuyvI9Jo06gIor2WsiivuUbuobR', $result->id);
		$this->assertEquals('Test Client', $result->name);
		$this->d->dump($result->toArray());


	}


	public function testGetConsumers()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');
		$result = $bus->dispatch( new Cookbook\OAuth2\Commands\Clients\ClientGetCommand([]));

		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Collection);
		$this->assertEquals(count($result), 1);
		$this->d->dump($result->toArray());

	}

}
