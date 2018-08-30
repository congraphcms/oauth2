<?php
/*
 * This file is part of the congraph/oauth-2 package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Congraph\OAuth2\Handlers\Commands\Roles;


use Congraph\Contracts\OAuth2\RoleRepositoryContract;
use Congraph\Core\Bus\RepositoryCommandHandler;
use Congraph\Core\Bus\RepositoryCommand;

/**
 * RoleCreateHandler class
 * 
 * Handling command for creating Role
 * 
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/oauth-2
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class RoleCreateHandler extends RepositoryCommandHandler
{

	/**
	 * Create new RoleCreateHandler
	 * 
	 * @param Congraph\Contracts\OAuth2\RoleRepositoryContract $repository
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
	 * @param Congraph\Core\Bus\RepositoryCommand $command
	 * 
	 * @return void
	 */
	public function handle(RepositoryCommand $command)
	{
		$client = $this->repository->create($command->params);

		return $client;
	}
}