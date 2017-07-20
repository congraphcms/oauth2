<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Debug\Dumper;
use Illuminate\Http\Request;

use LucaDegasperi\OAuth2Server\Facades\Authorizer;

require_once(__DIR__ . '/../database/seeders/UserTestDbSeeder.php');
require_once(__DIR__ . '/../database/seeders/ClientTestDbSeeder.php');
require_once(__DIR__ . '/../database/seeders/ClearDB.php');

class MiddlewareTest extends Orchestra\Testbench\TestCase
{

	public function setUp()
	{
		parent::setUp();

		// $this->artisan('migrate', [
		// 	'--database' => 'testbench',
		// 	'--realpath' => realpath(__DIR__.'/../migrations'),
		// ]);

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
			'--class' => 'ClientTestDbSeeder'
		]);

		$this->d = new Dumper();

		$this->clientId = 'iuqp7E9myPGkoKuyvI9Jo06gIor2WsiivuUbuobR';
		$this->clientSecret = '3wMlLnCBONHSlrxUJESPm1VwF9kBnHEGcCFt8iVR';
	}

	public function tearDown()
	{
		// $this->artisan('db:seed', [
		// 	'--class' => 'ClearDB'
		// ]);
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

		$app['config']->set('auth.driver', 'repository');

		$app['config']->set('oauth2.grant_types', [
			'client_credentials' => [
		        'class' => '\League\OAuth2\Server\Grant\ClientCredentialsGrant',
		        'access_token_ttl' => 3600
		    ],
			'password' => [
		        'class' => '\League\OAuth2\Server\Grant\PasswordGrant',
		        'callback' => '\Cookbook\OAuth2\PasswordGrantVerifier@verify',
		        'access_token_ttl' => 3600
		    ],
		    'refresh_token' => [
		        'class' => '\League\OAuth2\Server\Grant\RefreshTokenGrant',
		        'access_token_ttl' => 3600,
		        'refresh_token_ttl' => 604800,
		        'rotate_refresh_tokens' => false
		    ]
		]);
	}

	protected function getPackageProviders($app)
	{
		return [
			'LucaDegasperi\OAuth2Server\Storage\FluentStorageServiceProvider',
			'LucaDegasperi\OAuth2Server\OAuth2ServerServiceProvider',
			// 'Cookbook\Users\UsersServiceProvider',
			'Cookbook\Core\CoreServiceProvider',
			'Cookbook\OAuth2\OAuth2ServiceProvider',


		];
	}

	public function testRoute()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		Route::get('test', function(){
			return 'Voila!';
		});

		$response = $this->call('GET', 'test', []);

		$this->see('Voila!');
	}

	public function testConfig()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$config = Config::get('oauth2');

		$this->d->dump($config);
	}

	public function testClientCredentialsGrantGetAccessToken()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		Route::post('oauth/access_token', function() {
		    return Response::json(Authorizer::issueAccessToken());
		});

		$params = [
			'grant_type' => 'client_credentials',
			'client_id' => $this->clientId,
			'client_secret' => $this->clientSecret
		];

		$response = $this->call('POST', 'oauth/access_token', $params);
		$data = json_decode($response->getContent(), true);

		$this->d->dump($data);

		$access_token = $data['access_token'];

		$this->assertEquals(40, strlen($access_token));
		$this->assertEquals(3600, $data['expires_in']);
		$this->assertEquals('Bearer', $data['token_type']);

		$this->seeInDatabase('oauth_access_tokens', ['id' => $access_token]);


	}

	public function testClientCredentialsGrantAccessResource()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		Route::post('oauth/access_token', function() {
		    return Response::json(Authorizer::issueAccessToken());
		});

		Route::get('test', ['middleware' => 'oauth', 'uses' => function(){
			return 'Voila!';
		}]);

		$params = [
			'grant_type' => 'client_credentials',
			'client_id' => $this->clientId,
			'client_secret' => $this->clientSecret
		];

		$response = $this->call('POST', 'oauth/access_token', $params);
		$data = json_decode($response->getContent(), true);
		$access_token = $data['access_token'];

		$params = ['access_token' => $access_token];
		$response = $this->call('GET', 'test', $params);

		$this->assertEquals('Voila!', $response->getContent());

		$this->d->dump($response->getContent());
	}

	public function testPasswordGrantGetAccessToken()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		Route::post('oauth/access_token', function() {
		    return Response::json(Authorizer::issueAccessToken());
		});

		$params = [
			'grant_type' => 'password',
			'client_id' => $this->clientId,
			'client_secret' => $this->clientSecret,
			'username' => 'jane.doe@email.com',
			'password' => 'secret123'
		];

		$response = $this->call('POST', 'oauth/access_token', $params);
		$data = json_decode($response->getContent(), true);

		$this->d->dump($data);

		$access_token = $data['access_token'];

		$this->assertEquals(40, strlen($access_token));
		$this->assertEquals(3600, $data['expires_in']);
		$this->assertEquals('Bearer', $data['token_type']);

		$this->seeInDatabase('oauth_access_tokens', ['id' => $access_token]);
	}

	public function testPasswordGrantAccessResource()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		Route::post('oauth/access_token', function() {
		    return Response::json(Authorizer::issueAccessToken());
		});

		Route::get('test', ['middleware' => 'oauth', 'uses' => function(){
			return 'Voila!';
		}]);

		$params = [
			'grant_type' => 'client_credentials',
			'client_id' => $this->clientId,
			'client_secret' => $this->clientSecret
		];

		$response = $this->call('POST', 'oauth/access_token', $params);
		$data = json_decode($response->getContent(), true);
		$access_token = $data['access_token'];

		$params = ['access_token' => $access_token];
		$response = $this->call('GET', 'test', $params);

		$this->assertEquals('Voila!', $response->getContent());

		$this->d->dump($response->getContent());
	}

	public function testRefreshTokenGrantGetNewToken()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		Route::post('oauth/access_token', function() {
		    return Response::json(Authorizer::issueAccessToken());
		});

		$params = [
			'grant_type' => 'password',
			'client_id' => $this->clientId,
			'client_secret' => $this->clientSecret,
			'username' => 'jane.doe@email.com',
			'password' => 'secret123'
		];

		$response = $this->call('POST', 'oauth/access_token', $params);
		$data = json_decode($response->getContent(), true);

		$this->d->dump($data);

		$access_token = $data['access_token'];
		$refresh_token = $data['refresh_token'];

		$this->assertEquals(40, strlen($access_token));
		$this->assertEquals(40, strlen($refresh_token));
		$this->assertEquals(3600, $data['expires_in']);
		$this->assertEquals('Bearer', $data['token_type']);

		$params = [
			'grant_type' => 'refresh_token',
			'client_id' => $this->clientId,
			'client_secret' => $this->clientSecret,
			'refresh_token' => $refresh_token,
		];

		$response = $this->call('POST', 'oauth/access_token', $params);
		$data = json_decode($response->getContent(), true);

		$this->d->dump($data);

		$access_token = $data['access_token'];
		$refresh_token = $data['refresh_token'];

		$this->assertEquals(40, strlen($access_token));
		$this->assertEquals(40, strlen($refresh_token));
		$this->assertEquals(3600, $data['expires_in']);
		$this->assertEquals('Bearer', $data['token_type']);

		$this->seeInDatabase('oauth_access_tokens', ['id' => $access_token]);
	}

	/**
	 * @expectedException \League\OAuth2\Server\Exception\InvalidClientException
	 */
	public function testClientGrantNotAllowed()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		Route::post('oauth/access_token', function() {
		    return Response::json(Authorizer::issueAccessToken());
		});

		Config::set('oauth2.limit_clients_to_grants', true);

		$params = [
			'grant_type' => 'client_credentials',
			'client_id' => $this->clientId,
			'client_secret' => $this->clientSecret
		];

		$response = $this->call('POST', 'oauth/access_token', $params);
	}

	public function testClientGrantAllowed()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		Route::post('oauth/access_token', function() {
		    return Response::json(Authorizer::issueAccessToken());
		});

		Config::set('oauth2.limit_clients_to_grants', true);

		$params = [
			'grant_type' => 'password',
			'client_id' => $this->clientId,
			'client_secret' => $this->clientSecret,
			'username' => 'jane.doe@email.com',
			'password' => 'secret123'
		];

		$response = $this->call('POST', 'oauth/access_token', $params);
		$data = json_decode($response->getContent(), true);

		$this->d->dump($data);

		$access_token = $data['access_token'];

		$this->assertEquals(40, strlen($access_token));
		$this->assertEquals(3600, $data['expires_in']);
		$this->assertEquals('Bearer', $data['token_type']);

		$this->seeInDatabase('oauth_access_tokens', ['id' => $access_token]);
	}

	/**
	 * @expectedException \League\OAuth2\Server\Exception\InvalidScopeException
	 */
	public function testClientScopeNotAllowed()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		Route::post('oauth/access_token', function() {
		    return Response::json(Authorizer::issueAccessToken());
		});

		Config::set('oauth2.limit_clients_to_scopes', true);

		$params = [
			'grant_type' => 'password',
			'client_id' => $this->clientId,
			'client_secret' => $this->clientSecret,
			'username' => 'jane.doe@email.com',
			'password' => 'secret123',
			'scope' => 'manage_entities,manage_clients'
		];

		$response = $this->call('POST', 'oauth/access_token', $params);
	}

	public function testClientScopeAllowed()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		Route::post('oauth/access_token', function() {
		    return Response::json(Authorizer::issueAccessToken());
		});

		Config::set('oauth2.limit_clients_to_scopes', true);

		$params = [
			'grant_type' => 'password',
			'client_id' => $this->clientId,
			'client_secret' => $this->clientSecret,
			'username' => 'jane.doe@email.com',
			'password' => 'secret123',
			'scope' => 'manage_entities'
		];

		$response = $this->call('POST', 'oauth/access_token', $params);
		$data = json_decode($response->getContent(), true);

		$this->d->dump($data);

		$access_token = $data['access_token'];

		$this->assertEquals(40, strlen($access_token));
		$this->assertEquals(3600, $data['expires_in']);
		$this->assertEquals('Bearer', $data['token_type']);

		$this->seeInDatabase('oauth_access_tokens', ['id' => $access_token]);
	}


	public function testAccessResourceWithScope()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		Route::post('oauth/access_token', function() {
		    return Response::json(Authorizer::issueAccessToken());
		});

		Route::get('test', ['middleware' => 'oauth:manage_entities', 'uses' => function(){
			return 'Voila!';
		}]);

		$params = [
			'grant_type' => 'password',
			'client_id' => $this->clientId,
			'client_secret' => $this->clientSecret,
			'username' => 'jane.doe@email.com',
			'password' => 'secret123',
			'scope' => 'manage_entities'
		];

		$response = $this->call('POST', 'oauth/access_token', $params);
		$data = json_decode($response->getContent(), true);
		$access_token = $data['access_token'];

		$params = ['access_token' => $access_token];
		$response = $this->call('GET', 'test', $params);

		$this->assertEquals('Voila!', $response->getContent());

		$this->d->dump($response->getContent());
	}

	/**
	 * @expectedException \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException
	 */
	public function testAccessDeniedResourceWithScope()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		Route::post('oauth/access_token', function() {
		    return Response::json(Authorizer::issueAccessToken());
		});

		Route::get('test', ['middleware' => 'oauth:manage_clients', 'uses' => function(){
			return 'Voila!';
		}]);

		$params = [
			'grant_type' => 'password',
			'client_id' => $this->clientId,
			'client_secret' => $this->clientSecret,
			'username' => 'jane.doe@email.com',
			'password' => 'secret123',
			'scope' => 'manage_entities'
		];

		$response = $this->call('POST', 'oauth/access_token', $params);
		$data = json_decode($response->getContent(), true);
		$access_token = $data['access_token'];

		$params = ['access_token' => $access_token];
		$response = $this->call('GET', 'test', $params);

		$this->assertEquals('Voila!', $response->getContent());

		$this->d->dump($response->getContent());
	}

	/**
	 * @expectedException \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException
	 */
	public function testAccessDeniedForUserResourceWithScope()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		Route::post('oauth/access_token', function() {
		    return Response::json(Authorizer::issueAccessToken());
		});

		Route::get('test', ['middleware' => 'oauth:manage_users', 'uses' => function(){
			return 'Voila!';
		}]);

		$params = [
			'grant_type' => 'password',
			'client_id' => $this->clientId,
			'client_secret' => $this->clientSecret,
			'username' => 'dave.dee@email.com',
			'password' => 'secret123',
			'scope' => 'manage_entities,manage_users'
		];

		$response = $this->call('POST', 'oauth/access_token', $params);
		$data = json_decode($response->getContent(), true);
		$access_token = $data['access_token'];

		$params = ['access_token' => $access_token];
		$response = $this->call('GET', 'test', $params);

		$this->assertEquals('Voila!', $response->getContent());

		$this->d->dump($response->getContent());
	}
}
