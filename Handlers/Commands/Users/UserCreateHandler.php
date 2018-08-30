<?php
/*
 * This file is part of the congraph/users package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Congraph\OAuth2\Handlers\Commands\Users;

use Congraph\Contracts\OAuth2\UserRepositoryContract;
use Congraph\Core\Bus\RepositoryCommandHandler;
use Congraph\Core\Bus\RepositoryCommand;

/**
 * UserCreateHandler class
 * 
 * Handling command for creating user
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/oauth-2
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class UserCreateHandler extends RepositoryCommandHandler
{

	/**
	 * Create new UserCreateHandler
	 * 
	 * @param Congraph\Contracts\OAuth2\UserRepositoryContract $repository
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
	 * @param Congraph\Core\Bus\RepositoryCommand $command
	 * 
	 * @return void
	 */
	public function handle(RepositoryCommand $command)
	{
		$command->params['password'] = bcrypt($command->params['password']);
		$user = $this->repository->create($command->params);

		return $user;
	}
}