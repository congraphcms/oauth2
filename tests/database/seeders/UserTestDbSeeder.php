<?php
/*
 * This file is part of the cookbook/oauth package.
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
 * UserTestDbSeeder
 * 
 * Seeds Database with needed entries before tests
 * 
 * @uses   		Illuminate\Database\Schema\Blueprint
 * @uses   		Illuminate\Database\Seeder
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/oauth
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class UserTestDbSeeder extends Seeder {

	public function run()
	{
		DB::table('users')->truncate();
		DB::table('users')->insert([
			[
				'name' => 'Jane Doe',
				'email' => 'jane.doe@email.com',
				// password: secret123
				'password' => '$2y$10$RwECgIpcFIb52MbTKCsFde0/vhsuLsaEWItcXKTWCaLh3beZoiWjG',
				'created_at' => date("Y-m-d H:i:s"),
				'updated_at' => date("Y-m-d H:i:s")
			],
			[
				'name' => 'Dave Dee',
				'email' => 'dave.dee@email.com',
				// password: secret123
				'password' => '$2y$10$RwECgIpcFIb52MbTKCsFde0/vhsuLsaEWItcXKTWCaLh3beZoiWjG',
				'created_at' => date("Y-m-d H:i:s"),
				'updated_at' => date("Y-m-d H:i:s")
			]
		]);
	}

}