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


// use Cookbook\Contracts\OAuth2\ConsumerRepositoryContract;
use Cookbook\Contracts\OAuth2\UserRepositoryContract;
use Cookbook\Core\Bus\RepositoryCommand;
use Cookbook\Core\Bus\RepositoryCommandHandler;

/**
 * UserDeleteHandler class
 * 
 * Handling command for deleting user
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/oauth-2
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class UserDeleteHandler extends RepositoryCommandHandler
{
	/**
	 * Repository for handling consumers
	 * 
	 * @var \Cookbook\Contracts\OAuth2\ConsumerRepositoryContract
	 */
	// protected $consumerRepository;

	/**
	 * Create new UserDeleteHandler
	 * 
	 * @param Cookbook\Contracts\OAuth2\UserRepositoryContract $repository
	 * 
	 * @return void
	 */
	public function __construct(UserRepositoryContract $repository/*, ConsumerRepositoryContract $consumerRepository*/)
	{
		parent::__construct($repository);

		// $this->consumerRepository = $consumerRepository;
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
		$user = $this->repository->delete($command->id);

		// $this->consumerRepository->deleteByUser($user->id);

		return $user->id;
	}
}