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
 * ScopeTestDbSeeder
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
class ScopeTestDbSeeder extends Seeder {

	public function run()
	{
		DB::table('oauth_scopes')->delete();
		DB::table('oauth_scopes')->insert([
			[
				'id' => 'manage_users',
				'label' => 'Manage Users',
				'description' => 'Allows user to manage other user accounts.',
				'created_at' => date("Y-m-d H:i:s"),
				'updated_at' => date("Y-m-d H:i:s")
			],
			[
				'id' => 'manage_clients',
				'label' => 'Manage Clients',
				'description' => 'Allows user to manage OAuth clients.',
				'created_at' => date("Y-m-d H:i:s"),
				'updated_at' => date("Y-m-d H:i:s")
			],
			[
				'id' => 'manage_content_model',
				'label' => 'Manage Content Model',
				'description' => 'Allows user to manage entity types and attributes.',
				'created_at' => date("Y-m-d H:i:s"),
				'updated_at' => date("Y-m-d H:i:s")
			],
			[
				'id' => 'manage_entities',
				'label' => 'Manage Entities',
				'description' => 'Allows user to content.',
				'created_at' => date("Y-m-d H:i:s"),
				'updated_at' => date("Y-m-d H:i:s")
			]
		]);

	}

}