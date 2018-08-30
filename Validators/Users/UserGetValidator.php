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
 * UserGetValidator class
 * 
 * Validating command for fetching user
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/oauth-2
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class UserGetValidator extends Validator
{

	/**
	 * Available fields for sorting
	 *
	 * @var array
	 */
	protected $availableSorting;

	/**
	 * Available fields for filtering
	 *
	 * @var array
	 */
	protected $availableFilters;

	/**
	 * Default sorting criteria
	 *
	 * @var array
	 */
	protected $defaultSorting;

	/**
	 * Create new UserGetValidator
	 * 
	 * @return void
	 */
	public function __construct()
	{

		$this->availableSorting = [
			'id',
			'name',
			'email',
			'created_at',
			'updated_at'
		];

		$this->availableFilters = [
			'id' 			=> ['e', 'ne', 'lt', 'lte', 'gt', 'gte', 'in', 'nin'],
			'name'			=> ['e', 'ne', 'in', 'nin'],
			'email' 		=> ['e', 'ne', 'in', 'nin'],
			'created_at'	=> ['e', 'ne', 'lt', 'lte', 'gt', 'gte', 'in', 'nin'],
			'updated_at'	=> ['e', 'ne', 'lt', 'lte', 'gt', 'gte', 'in', 'nin']
		];

		$this->defaultSorting = ['-created_at'];

		parent::__construct();
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

		if( ! empty($command->params['filter']) )
		{
			$this->validateFilters($command->params['filter']);
		}

		if( empty($command->params['offset']) )
		{
			$command->params['offset'] = 0;
		}
		if( empty($command->params['limit']) )
		{
			$command->params['limit'] = 0;
		}
		$this->validatePaging($command->params['offset'], $command->params['limit']);
		
		if( ! empty($command->params['sort']) )
		{
			$this->validateSorting($command->params['sort']);
		}
		
		// if( ! empty($command->params['include']) )
		// {
		// 	$this->validateInclude($command->params['include']);
		// }
	}

	protected function validateFilters(&$filters)
	{
		if( ! empty($filters) )
		{
			foreach ($filters as $field => &$filter) {
				if( ! array_key_exists($field, $this->availableFilters) )
				{
					$e = new BadRequestException();
					$e->setErrorKey('filter');
					$e->addErrors('Filtering by \'' . $field . '\' is not allowed.');

					throw $e;
				}
				if( ! is_array($filter) )
				{
					if( ! in_array('e', $this->availableFilters[$field]) )
					{
						$e = new BadRequestException();
						$e->setErrorKey('filter');
						$e->addErrors('Filter operation is not allowed.');

						throw $e;
					}

					continue;
				}

				foreach ($filter as $operation => &$value) {
					if( ! in_array($operation, $this->availableFilters[$field]) )
					{
						$e = new BadRequestException();
						$e->setErrorKey('filter');
						$e->addErrors('Filter operation is not allowed.');

						throw $e;
					}

					if($operation == 'in' || $operation == 'nin')
					{
						if( ! is_array($value) )
						{
							$value = explode(',', strval($value));
						}
					}
				}
			}

			return;
		}

		$filters = [];
	}

	protected function validatePaging(&$offset = 0, &$limit = 0)
	{
		$offset = intval($offset);
		$limit = intval($limit);
	}

	protected function validateSorting(&$sort)
	{
		if( empty($sort) )
		{
			$sort = $this->defaultSorting;
		}

		if( ! is_array($sort) )
		{
			$sort = [$sort];
		}

		foreach ($sort as $criteria)
		{
			if( empty($criteria) )
			{
				continue;
			}

			if( $criteria[0] === '-' )
			{
				$criteria = substr($criteria, 1);
			}

			if( ! in_array($criteria, $this->availableSorting) )
			{
				$e = new BadRequestException();
				$e->setErrorKey('sort');
				$e->addErrors('Sorting by \'' . $criteria . '\' is not allowed.');

				throw $e;
			}
		}
	}
}