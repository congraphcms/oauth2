<?php
/*
 * This file is part of the cookbook/oauth-2 package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\OAuth2\Validators\Clients;

use Cookbook\Core\Bus\RepositoryCommand;
use Cookbook\Core\Validation\Validator;
use Illuminate\Support\Facades\Config;


/**
 * ClientDeleteValidator class
 * 
 * Validating command for deleting Client
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/oauth-2
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class ClientDeleteValidator extends Validator
{


	/**
	 * Set of rules for validating Client
	 *
	 * @var array
	 */
	protected $rules;

	/**
	 * Create new ClientDeleteValidator
	 * 
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();

		$this->exception->setErrorKey('client');
	}


	/**
	 * Validate RepositoryCommand
	 * 
	 * @param Cookbook\Core\Bus\RepositoryCommand $command
	 * 
	 * @todo  Create custom validation for all db related checks (DO THIS FOR ALL VALIDATORS)
	 * @todo  Check all db rules | make validators on repositories
	 * 
	 * @return void
	 */
	public function validate(RepositoryCommand $command)
	{
		return true;
	}

}