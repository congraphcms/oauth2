<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Debug\Dumper;

require_once(__DIR__ . '/../database/seeders/UserTestDbSeeder.php');
require_once(__DIR__ . '/../database/seeders/ClientTestDbSeeder.php');
require_once(__DIR__ . '/../database/seeders/RoleTestDbSeeder.php');
require_once(__DIR__ . '/../database/seeders/ScopeTestDbSeeder.php');
require_once(__DIR__ . '/../database/seeders/ClearDB.php');

class RoleTest extends Orchestra\Testbench\TestCase
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
		// 	'--realpath' => realpath(__DIR__.'/../../vendor/congraph/users/database/migrations'),
		// ]);

		$this->artisan('migrate', [
			'--database' => 'testbench',
			'--realpath' => realpath(__DIR__.'/../../database/migrations'),
		]);

		$this->artisan('db:seed', [
			'--class' => 'UserTestDbSeeder'
		]);
		$this->artisan('db:seed', [
			'--class' => 'ClientTestDbSeeder'
		]);
		$this->artisan('db:seed', [
			'--class' => 'ScopeTestDbSeeder'
		]);
		$this->artisan('db:seed', [
			'--class' => 'RoleTestDbSeeder'
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
			'database'	=> 'congraph_testbench',
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
			'LucaDegasperi\OAuth2Server\Storage\FluentStorageServiceProvider',
			'LucaDegasperi\OAuth2Server\OAuth2ServerServiceProvider',
			'Congraph\OAuth2\OAuth2ServiceProvider',
			// 'Congraph\Users\UsersServiceProvider', 
			'Congraph\Core\CoreServiceProvider',
		];
	}

	public function testCreateRole()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'name' => 'Project Manager',
			'description' => 'Edits entities of only one project.',
			'scopes' => ['manage_users', 'manage_clients', 'manage_content_model', 'manage_entities']
		];


		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Congraph\OAuth2\Commands\Roles\RoleCreateCommand($params));

		$this->d->dump($result->toArray());
		$this->assertEquals('Project Manager', $result->name);
		$this->assertEquals('Edits entities of only one project.', $result->description);

		$this->seeInDatabase('roles', ['id' => $result->id, 'name' => $result->name, 'description' => $result->description]);
	}


	public function testUpdateRole()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$params = [
			'name' => 'Content Editor',
			'scopes' => ['manage_content_model']
		];

		$result = $bus->dispatch( new Congraph\OAuth2\Commands\Roles\RoleUpdateCommand($params, 2));

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertEquals(2, $result->id);
		$this->assertEquals('Content Editor', $result->name);
		$this->assertEquals(['manage_content_model'], $result->scopes);

		$this->seeInDatabase('roles', ['id' => 2, 'name' => 'Content Editor']);
		$this->seeInDatabase('role_scopes', ['role_id' => 2, 'scope_id' => 'manage_content_model']);
		$this->dontSeeInDatabase('role_scopes', ['role_id' => 2, 'scope_id' => 'manage_entities']);

		$this->d->dump($result->toArray());
	}


	public function testDeleteRole()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Congraph\OAuth2\Commands\Roles\RoleDeleteCommand([], 3));

		$this->assertEquals(3, $result);
		$this->d->dump($result);
		$this->dontSeeInDatabase('roles', ['id' => 3]);
		$this->dontSeeInDatabase('role_scopes', ['role_id' => 3, 'scope_id' => 'manage_clients']);
	}

	/**
	 * @expectedException \Congraph\Core\Exceptions\NotFoundException
	 */
	public function testDeleteException()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Congraph\OAuth2\Commands\Roles\RoleDeleteCommand([], 123));
	}

	public function testFetchRole()
	{

		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Congraph\OAuth2\Commands\Roles\RoleFetchCommand([], 3));

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Model);
		$this->assertEquals(3, $result->id);
		$this->assertEquals('Developer', $result->name);
		$this->assertEquals(['manage_clients'], $result->scopes);
		$this->d->dump($result->toArray());


	}


	public function testGetRoles()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');
		$result = $bus->dispatch( new Congraph\OAuth2\Commands\Roles\RoleGetCommand([]));

		$this->assertTrue($result instanceof Congraph\Core\Repositories\Collection);
		$this->assertEquals(count($result), 3);
		$this->d->dump($result->toArray());

	}

}
