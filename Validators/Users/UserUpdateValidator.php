<?php
/*
 * This file is part of the congraph/oauth-2 package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Congraph\OAuth2\Validators\Users;

use Congraph\Core\Bus\RepositoryCommand;
use Congraph\Core\Validation\Validator;
use Congraph\Core\Helpers\FileHelper;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Illuminate\Support\Facades\Config;


/**
 * UserUpdateValidator class
 * 
 * Validating command for updating user
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/oauth-2
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class UserUpdateValidator extends Validator
{


	/**
	 * Set of rules for validating user
	 *
	 * @var array
	 */
	protected $rules;

	/**
	 * Set of rules for validating user role
	 *
	 * @var array
	 */
	protected $roleRules;

	/**
	 * Create new UserUpdateValidator
	 * 
	 * @return void
	 */
	public function __construct()
	{


		$this->rules = [
			'name'					=> 'sometimes|required|min:3|max:150',
			'roles'					=> 'sometimes|array'
		];

		$this->roleRules = 
		[
			'id'			=> 'required|integer|exists:roles,id',
			'type'			=> 'required|in:role'
		];

		parent::__construct();

		$this->exception->setErrorKey('user');
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

		$validator = $this->newValidator($command->params, $this->rules, true);

		$this->validateParams($command->params, $this->rules, true);

		if( isset($command->params['roles']) && !empty($command->params['roles']))
		{
			$validator->each('roles', $this->roleRules);
		}

		if( $this->exception->hasErrors() )
		{
			throw $this->exception;
		}
	}

}