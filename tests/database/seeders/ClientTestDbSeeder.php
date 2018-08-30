<?php
/*
 * This file is part of the congraph/oauth-2 package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
/**
 * ClientTestDbSeeder
 * 
 * Seeds Database with needed entries before tests
 * 
 * @uses   		Illuminate\Database\Schema\Blueprint
 * @uses   		Illuminate\Database\Seeder
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/oauth-2
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class ClientTestDbSeeder extends Seeder {

	public function run()
	{
		DB::table('oauth_clients')->delete();
		DB::table('oauth_clients')->insert([
			[
				'id' => 'iuqp7E9myPGkoKuyvI9Jo06gIor2WsiivuUbuobR',
				'secret' => '3wMlLnCBONHSlrxUJESPm1VwF9kBnHEGcCFt8iVR',
				'name' => 'Test Client',
				'created_at' => date("Y-m-d H:i:s"),
				'updated_at' => date("Y-m-d H:i:s")
			]
		]);

		DB::table('oauth_grants')->delete();
		DB::table('oauth_grants')->insert([
			[
				'id' => 'password',
				'created_at' => date("Y-m-d H:i:s"),
				'updated_at' => date("Y-m-d H:i:s")
			],
			[
				'id' => 'client_credentials',
				'created_at' => date("Y-m-d H:i:s"),
				'updated_at' => date("Y-m-d H:i:s")
			]
		]);

		DB::table('oauth_client_scopes')->truncate();
		DB::table('oauth_client_scopes')->insert([
			[
				'client_id' => 'iuqp7E9myPGkoKuyvI9Jo06gIor2WsiivuUbuobR',
				'scope_id' => 'manage_content_model',
				'created_at' => date("Y-m-d H:i:s"),
				'updated_at' => date("Y-m-d H:i:s")
			],
			[
				'client_id' => 'iuqp7E9myPGkoKuyvI9Jo06gIor2WsiivuUbuobR',
				'scope_id' => 'manage_entities',
				'created_at' => date("Y-m-d H:i:s"),
				'updated_at' => date("Y-m-d H:i:s")
			],
			[
				'client_id' => 'iuqp7E9myPGkoKuyvI9Jo06gIor2WsiivuUbuobR',
				'scope_id' => 'manage_users',
				'created_at' => date("Y-m-d H:i:s"),
				'updated_at' => date("Y-m-d H:i:s")
			]
		]);

		DB::table('oauth_client_grants')->truncate();
		DB::table('oauth_client_grants')->insert([
			[
				'client_id' => 'iuqp7E9myPGkoKuyvI9Jo06gIor2WsiivuUbuobR',
				'grant_id' => 'password',
				'created_at' => date("Y-m-d H:i:s"),
				'updated_at' => date("Y-m-d H:i:s")
			]
		]);
	}

}