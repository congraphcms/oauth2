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

use Cookbook\Contracts\OAuth2\ScopeRepositoryContract;
use Cookbook\Core\Bus\RepositoryCommand;
use Cookbook\Core\Validation\Validator;
use Illuminate\Support\Facades\Config;


/**
 * ClientCreateValidator class
 * 
 * Validating command for creating Client
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/oauth-2
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class ClientCreateValidator extends Validator
{


	/**
	 * Set of rules for validating Client
	 *
	 * @var array
	 */
	protected $rules;

	/**
	 * Scope repository
	 *
	 * @var Cookbook\Contracts\OAuth2\ScopeRepositoryContract
	 */
	protected $scopeRepository;

	/**
	 * List of available grant types
	 *
	 * @var array
	 */
	protected $availableGrants = ['password', 'client_credentials', 'refresh_token'];

	/**
	 * Create new ClientCreateValidator
	 * 
	 * @return void
	 */
	public function __construct(ScopeRepositoryContract $scopeRepository)
	{
		$this->scopeRepository = $scopeRepository;

		$this->rules = [
			'name'					=> 'required|max:150',
			'scopes'				=> 'required|array',
			'grants'				=> 'required|array'
		];

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

		$this->validateParams($command->params, $this->rules, true);

		if( $this->exception->hasErrors() )
		{
			throw $this->exception;
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

		foreach ($command->params['grants'] as $grant)
		{
			if(!in_array($grant, $this->availableGrants)) {
				$this->exception->addErrors(['grants' => 'Grant \''.$grant.'\' isn\'t allowed.']);
			}
		}

		if( $this->exception->hasErrors() )
		{
			throw $this->exception;
		}
	}

}