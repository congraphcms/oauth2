<?php
/*
 * This file is part of the congraph/oauth-2 package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Congraph\OAuth2\Validators\Roles;

use Congraph\Core\Bus\RepositoryCommand;
use Congraph\Core\Validation\Validator;
use Illuminate\Support\Facades\Config;


/**
 * RoleDeleteValidator class
 * 
 * Validating command for deleting Role
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/oauth-2
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class RoleDeleteValidator extends Validator
{


	/**
	 * Set of rules for validating Role
	 *
	 * @var array
	 */
	protected $rules;

	/**
	 * Create new RoleDeleteValidator
	 * 
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();

		$this->exception->setErrorKey('role');
	}


	/**
	 * Validate RepositoryCommand
	 * 
	 * @param Congraph\Core\Bus\RepositoryCommand $command
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