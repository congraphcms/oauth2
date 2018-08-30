<?php
/*
 * This file is part of the congraph/oauth package.
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
 * ClearDB
 * 
 * Clears Database after tests
 * 
 * @uses   		Illuminate\Database\Schema\Blueprint
 * @uses   		Illuminate\Database\Seeder
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/oauth
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class ClearDB extends Seeder {

	public function run()
	{
		DB::table('users')->truncate();
		DB::table('password_resets')->truncate();
		// DB::table('consumers')->truncate();
		// DB::table('access_tokens')->truncate();
		// DB::table('request_tokens')->truncate();
	
	}

}