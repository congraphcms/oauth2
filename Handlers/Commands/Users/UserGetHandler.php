<?php
/*
 * This file is part of the cookbook/oauth-2 package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\OAuth2\Handlers\Commands\Users;


use Cookbook\Contracts\OAuth2\UserRepositoryContract;
use Cookbook\Core\Bus\RepositoryCommandHandler;
use Cookbook\Core\Bus\RepositoryCommand;

/**
 * UserGetHandler class
 * 
 * Handling command for getting users
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/oauth-2
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class UserGetHandler extends RepositoryCommandHandler
{

	/**
	 * Create new UserGetHandler
	 * 
	 * @param Cookbook\Contracts\OAuth2\UserRepositoryContract $repository
	 * 
	 * @return void
	 */
	public function __construct(UserRepositoryContract $repository)
	{
		parent::__construct($repository);
	}

	/**
	 * Handle RepositoryCommand
	 * 
	 * @param Cookbook\Core\Bus\RepositoryCommand $command
	 * 
	 * @return void
	 */
	public function handle(RepositoryCommand $command)
	{
		return $this->repository->get(
			(!empty($command->params['filter']))?$command->params['filter']:[],
			(!empty($command->params['offset']))?$command->params['offset']:0,
			(!empty($command->params['limit']))?$command->params['limit']:0,
			(!empty($command->params['sort']))?$command->params['sort']:[],
			(!empty($command->params['include']))?$command->params['include']:[]
		);
	}
}