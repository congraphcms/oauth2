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

use Congraph\Contracts\OAuth2\ScopeRepositoryContract;
use Congraph\Core\Bus\RepositoryCommand;
use Congraph\Core\Validation\Validator;
use Illuminate\Support\Facades\Config;


/**
 * RoleUpdateValidator class
 * 
 * Validating command for updating Role
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/oauth-2
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class RoleUpdateValidator extends Validator
{


	/**
	 * Set of rules for validating Role
	 *
	 * @var array
	 */
	protected $rules;

	/**
	 * Scope repository
	 *
	 * @var Congraph\Contracts\OAuth2\ScopeRepositoryContract
	 */
	protected $scopeRepository;

	/**
	 * Create new RoleUpdateValidator
	 * 
	 * @return void
	 */
	public function __construct(ScopeRepositoryContract $scopeRepository)
	{

		$this->scopeRepository = $scopeRepository;

		$this->rules = [
			'name'					=> 'sometimes|required|max:150',
			'description'			=> 'sometimes',
			'scopes'				=> 'sometimes|array'
		];

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

		$this->validateParams($command->params, $this->rules, true);

		if( $this->exception->hasErrors() )
		{
			throw $this->exception;
		}

		if(!isset($command->params['scopes'])) {
			return;
		}

		$scopes = $this->scopeRepository->getAll();

		foreach ($command->params['scopes'] as $scope)
		{	
			$valid = false;
			foreach ($scopes as $validScope)
			{
				if($validScope->id == $scope)
				{
					$valid = true;
					break;
				}
			}
			if(!$valid) {
				$this->exception->addErrors(['scopes' => 'Scope \''.$scope.'\' doesn\'t exist.']);
			}
		}

		if( $this->exception->hasErrors() )
		{
			throw $this->exception;
		}
	}

}