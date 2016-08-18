<?php
/*
 * This file is part of the cookbook/oauth-2 package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\OAuth2\Handlers\Commands\Roles;


use Cookbook\Contracts\OAuth2\RoleRepositoryContract;
use Cookbook\Core\Bus\RepositoryCommandHandler;
use Cookbook\Core\Bus\RepositoryCommand;

/**
 * RoleDeleteHandler class
 * 
 * Handling command for deleting Role
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/oauth-2
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class RoleDeleteHandler extends RepositoryCommandHandler
{

	/**
	 * Create new RoleDeleteHandler
	 * 
	 * @param Cookbook\Contracts\OAuth2\RoleRepositoryContract $repository
	 * 
	 * @return void
	 */
	public function __construct(RoleRepositoryContract $repository)
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
		$client = $this->repository->delete($command->id);

		return $client->id;
	}
}