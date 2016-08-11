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

		$this->artisan('migrate', [
			'--database' => 'testbench',
			'--realpath' => realpath(__DIR__.'/../../vendor/cookbook/users/database/migrations'),
		]);

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
			'port'		=> '33060',
			'database'	=> 'cookbook_testbench',
			'username'  => 'homestead',
			'password'  => 'secret',
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
		    ]
		]);
	}

	protected function getPackageProviders($app)
	{
		return [
			'Cookbook\OAuth2\OAuth2ServiceProvider', 
			'Cookbook\Users\UsersServiceProvider', 
			'Cookbook\Core\CoreServiceProvider',
			'LucaDegasperi\OAuth2Server\Storage\FluentStorageServiceProvider',
			'LucaDegasperi\OAuth2Server\OAuth2ServerServiceProvider'
		];
	}

	public function testRoute()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		// Config::set('oauth2.grant_types', [
		// 	'client_credentials' => [
		//         'class' => '\League\OAuth2\Server\Grant\ClientCredentialsGrant',
		//         'access_token_ttl' => 3600
		//     ],
		// 	'password' => [
		//         'class' => '\League\OAuth2\Server\Grant\PasswordGrant',
		//         'callback' => '\App\PasswordGrantVerifier@verify',
		//         'access_token_ttl' => 3600
		//     ]
		// ]);
		Route::get('test', function(){
			return 'Voila!';
		});

		$response = $this->call('GET', 'test', []);

		$this->see('Voila!');
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

	// /**
	//  * @expectedException \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
	//  */
	// public function testFailedAuthWithNoAuthHeader()
	// {
	// 	fwrite(STDOUT, __METHOD__ . "\n");

	// 	Route::get('test', ['middleware' => 'cb.oauth.auth', 'uses' => function(){
	// 		return 'Voila!';
	// 	}]);
		
	// 	$response = $this->call('GET', 'test', []);
	// }

	// /**
	//  * @expectedException \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
	//  */
	// public function testFailedAuthWithConsumerOnlyAuthHeader()
	// {
	// 	fwrite(STDOUT, __METHOD__ . "\n");

	// 	Route::get('test', ['middleware' => 'cb.oauth.auth', 'uses' => function(){
	// 		return 'Voila!';
	// 	}]);
		
	// 	$response = $this->call('GET', 'test', [], [], [], ['HTTP_Authorization' => $this->authHeaderConsumerOnly]);
	// }

	// public function testAuthWithOneLeggedAuthHeader()
	// {
	// 	fwrite(STDOUT, __METHOD__ . "\n");

	// 	Route::get('test', ['middleware' => 'cb.oauth.auth', 'uses' => function(){
	// 		$user = Auth::getUser();
	// 		return Response::json($user);
	// 		// return 'Voila!';
	// 	}]);

	// 	// call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
	// 	$response = $this->call('GET', 'test', [], [], [], ['HTTP_Authorization' => $this->oneLeggedAuthHeader]);

	// 	$this->d->dump(json_decode($response->getContent()));
	// 	$this->seeJson(['id' => 1, 'name' => 'Jane Doe']);
	// }

	// /**
	//  * @expectedException \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
	//  */
	// public function testFailedConsumerOnlyAuthWithNoAuthHeader()
	// {
	// 	fwrite(STDOUT, __METHOD__ . "\n");

	// 	Route::get('test', ['middleware' => 'cb.oauth.auth-consumer', 'uses' => function(){
	// 		return 'Voila!';
	// 	}]);
		
	// 	$response = $this->call('GET', 'test', []);
	// }

	// public function testConsumerOnlyAuthWithAuthHeader()
	// {
	// 	fwrite(STDOUT, __METHOD__ . "\n");

	// 	Route::get('test', ['middleware' => 'cb.oauth.auth-consumer', 'uses' => function(){
	// 		$user = Auth::getUser();
	// 		return Response::json($user);
	// 	}]);

	// 	// call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
	// 	$response = $this->call('GET', 'test', [], [], [], ['HTTP_Authorization' => $this->authHeader]);

	// 	$this->d->dump(json_decode($response->getContent()));
	// 	$this->seeJson(['id' => 1, 'name' => 'Jane Doe']);
	// }

	// public function testConsumerOnlyAuthWithConsumerOnlyAuthHeader()
	// {
	// 	fwrite(STDOUT, __METHOD__ . "\n");

	// 	Route::get('test', ['middleware' => 'cb.oauth.auth-consumer', 'uses' => function(){
	// 		$user = Auth::getUser();
	// 		return Response::json($user);
	// 	}]);

	// 	// call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
	// 	$response = $this->call('GET', 'test', [], [], [], ['HTTP_Authorization' => $this->authHeaderConsumerOnly]);

	// 	$this->d->dump(json_decode($response->getContent()));
	// 	$this->seeJson([]);
	// }

	// public function testAuthWithAuthHeader()
	// {
	// 	fwrite(STDOUT, __METHOD__ . "\n");

	// 	Route::get('test', ['middleware' => 'cb.oauth.auth', 'uses' => function(){
	// 		$user = Auth::getUser();
	// 		return Response::json($user);
	// 	}]);

	// 	// call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
	// 	$response = $this->call('GET', 'test', [], [], [], ['HTTP_Authorization' => $this->authHeader]);

	// 	$this->d->dump(json_decode($response->getContent()));
	// 	$this->seeJson(['id' => 1, 'name' => 'Jane Doe']);
	// }

	// public function testBasicAuth()
	// {
	// 	fwrite(STDOUT, __METHOD__ . "\n");

	// 	$this->app['router']->middleware('auth.basic', \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class);
	// 	Route::get('test', ['uses' => function(Request $request){

	//         $credentials = $request->only('email', 'password');

	//         if (Auth::attempt($credentials, $request->has('remember')))
	//         {
	//             $user = Auth::getUser();
	// 			return Response::json($user);
	//         }

	// 		return Response::json(['login' => false]);
	// 	}]);

	// 	Route::get('testsession', ['uses' => function(Request $request){
	// 		return Response::json([ 'logged_in' => ! Auth::guest() ]);
	// 	}]);

	// 	// call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
	// 	$response = $this->call('GET', 'test', ['email' => 'jane.doe@email.com', 'password' => 'secret123']);

	// 	$this->d->dump(json_decode($response->getContent()));
	// 	$this->seeJson(['id' => 1, 'name' => 'Jane Doe']);

	// 	$response = $this->call('GET', 'testsession', []);
	// 	$this->d->dump(json_decode($response->getContent()));
	// 	$this->seeJson([ 'logged_in' => true ]);
	// }
}