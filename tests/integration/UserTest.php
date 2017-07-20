<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Debug\Dumper;

require_once(__DIR__ . '/../database/seeders/UserTestDbSeeder.php');
require_once(__DIR__ . '/../database/seeders/RoleTestDbSeeder.php');
require_once(__DIR__ . '/../database/seeders/ScopeTestDbSeeder.php');
require_once(__DIR__ . '/../database/seeders/ClearDB.php');

class UserTest extends Orchestra\Testbench\TestCase
{

	public function setUp()
	{
		parent::setUp();

		$this->artisan('migrate', [
			'--database' => 'testbench',
			'--realpath' => realpath(__DIR__.'/../../database/migrations'),
		]);

		$this->artisan('db:seed', [
			'--class' => 'UserTestDbSeeder'
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
		return ['Cookbook\OAuth2\OAuth2ServiceProvider', 'Cookbook\Core\CoreServiceProvider'];
	}

	public function testCreateUser()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'name' => 'John Doe',
			'email' => 'john.doe@email.com',
			'password' => 'secret123',
			'roles' => [
				[
					'id' => 2,
					'type' => 'role'
				]
			]
		];


		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Cookbook\OAuth2\Commands\Users\UserCreateCommand($params));

		$this->d->dump($result->toArray());
		$this->assertEquals('John Doe', $result->name);
		$this->assertEquals('john.doe@email.com', $result->email);
		$this->assertFalse(isset($result->password));

		$this->seeInDatabase('users', ['name' => 'John Doe', 'email' => 'john.doe@email.com']);
		$this->seeInDatabase('user_roles', ['user_id' => $result->id, 'role_id' => 2]);
	}

	/**
	 * @expectedException \Cookbook\Core\Exceptions\ValidationException
	 */
	public function testCreateException()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$params = [
			'name' => 'John Doe',
			'email' => 'john.doe',
			'password' => 'secret123'
		];


		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Cookbook\OAuth2\Commands\Users\UserCreateCommand($params));
	}

	public function testUpdateUser()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$params = [
			'name' => 'Jane Margaret Doe',
			'roles' => [
				['id' => 2, 'type' => 'role'],
				['id' => 3, 'type' => 'role']
			]
		];

		$result = $bus->dispatch( new Cookbook\OAuth2\Commands\Users\UserUpdateCommand($params, 1));

		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals(1, $result->id);
		$this->assertEquals('jane.doe@email.com', $result->email);
		$this->assertEquals('Jane Margaret Doe', $result->name);

		$this->seeInDatabase('user_roles', ['user_id' => $result->id, 'role_id' => 2]);
		$this->seeInDatabase('user_roles', ['user_id' => $result->id, 'role_id' => 3]);
		$this->dontSeeInDatabase('user_roles', ['user_id' => $result->id, 'role_id' => 1]);

		$this->d->dump($result->toArray());
	}

	/**
	 * @expectedException \Cookbook\Core\Exceptions\NotFoundException
	 */
	public function testUpdateException()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$params = [

		];

		$result = $bus->dispatch( new Cookbook\OAuth2\Commands\Users\UserUpdateCommand($params, 1222));
	}

	public function testChangeUserPassword()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$params = [
			'password' => 'newpassword123'
		];

		$result = $bus->dispatch( new Cookbook\OAuth2\Commands\Users\UserChangePasswordCommand($params, 1));

		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals(1, $result->id);
		$this->assertEquals('jane.doe@email.com', $result->email);
		$this->assertEquals('Jane Doe', $result->name);

		$this->d->dump($result->toArray());
	}

	/**
	 * @expectedException \Cookbook\Core\Exceptions\ValidationException
	 */
	public function testChangeUserPasswordException()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$params = [
			'password' => ''
		];

		$result = $bus->dispatch( new Cookbook\OAuth2\Commands\Users\UserChangePasswordCommand($params, 1222));
	}

	public function testDeleteUser()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Cookbook\OAuth2\Commands\Users\UserDeleteCommand([], 1));

		$this->assertEquals(1, $result);
		$this->d->dump($result);

	}

	/**
	 * @expectedException \Cookbook\Core\Exceptions\NotFoundException
	 */
	public function testDeleteException()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Cookbook\OAuth2\Commands\Users\UserDeleteCommand([], 133));
	}

	public function testFetchUser()
	{

		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');

		$result = $bus->dispatch( new Cookbook\OAuth2\Commands\Users\UserFetchCommand([], 1));

		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Model);
		$this->assertTrue(is_int($result->id));
		$this->assertEquals('Jane Doe', $result->name);
		$this->assertEquals('jane.doe@email.com', $result->email);
		$this->d->dump($result->toArray());


	}


	public function testGetUsers()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');
		$result = $bus->dispatch( new Cookbook\OAuth2\Commands\Users\UserGetCommand([]));

		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Collection);
		$this->assertEquals(2, count($result));
		$this->d->dump($result->toArray());

	}

	public function testGetUsersWithRoles()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$app = $this->createApplication();
		$bus = $app->make('Illuminate\Contracts\Bus\Dispatcher');
		$result = $bus->dispatch( new Cookbook\OAuth2\Commands\Users\UserGetCommand(['include' => 'roles']));

		$this->assertTrue($result instanceof Cookbook\Core\Repositories\Collection);
		$this->assertEquals(2, count($result));
		$users = $result->toArray();
		$this->d->dump($users);
		$this->assertEquals('Administrator',$users[0]['roles'][0]['name']);

	}

}
