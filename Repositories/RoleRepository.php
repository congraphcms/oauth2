<?php
/*
 * This file is part of the cookbook/oauth-2 package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\OAuth2\Repositories;

use Cookbook\Contracts\OAuth2\RoleRepositoryContract;
use Cookbook\Core\Exceptions\Exception;
use Cookbook\Core\Exceptions\NotFoundException;
use Cookbook\Core\Facades\Trunk;
use Cookbook\Core\Repositories\AbstractRepository;
use Cookbook\Core\Repositories\Collection;
use Cookbook\Core\Repositories\Model;
use Cookbook\Core\Repositories\UsesCache;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;
use stdClass;

/**
 * RoleRepository class
 *
 * Repository for user role database queries
 *
 * @uses   		Illuminate\Database\Connection
 * @uses   		Cookbook\Core\Repository\AbstractRepository
 *
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/oauth-2
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class RoleRepository extends AbstractRepository implements RoleRepositoryContract//, UsesCache
{
// ----------------------------------------------------------------------------------------------
// PARAMS
// ----------------------------------------------------------------------------------------------
//
//
//

	/**
	 * Create new RoleRepository
	 *
	 * @param Illuminate\Database\Connection $db
	 *
	 * @return void
	 */
	public function __construct(Connection $db)
	{
		$this->type = 'role';
		$this->table = 'roles';

		// AbstractRepository constructor
		parent::__construct($db);
	}

// ----------------------------------------------------------------------------------------------
// CRUD
// ----------------------------------------------------------------------------------------------
//
//
//


	/**
	 * Create new role
	 *
	 * @param array $model - role params (name, permissions...)
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 */
	protected function _create($model)
	{
		// get scopes from model if there are any
		$scopes = [];
		if(!empty($model['scopes']) && is_array($model['scopes']))
		{
			$scopes = $model['scopes'];
		}
		
		// unset scopes from model 
		// for attribute insertation
		unset($model['scopes']);

		$model['created_at'] = $model['updated_at'] = Carbon::now('UTC')->toDateTimeString();

		// insert role in database
		$roleId = $this->db->table($this->table)->insertGetId($model);

		if (!$roleId) {
			throw new \Exception('Failed to insert role');
		}

		$roleScopeParams = [];

		// set relation to role in all scopes
		for($i = 0; $i < count($scopes); $i++)
		{
			$roleScopeParam = [];
			$roleScopeParam['role_id'] = $roleId;
			$roleScopeParam['scope_id'] = $scopes[$i];
			$roleScopeParam['created_at'] = $roleScopeParam['updated_at'] = Carbon::now('UTC')->toDateTimeString();
			$roleScopeParams[] = $roleScopeParam;
		}

		// update all scopes for role
		$this->updateRoleScopes($roleScopeParams, $roleId);

		// get role
		$role = $this->fetch($roleId);

		// and return newly created role
		return $role;
	}


	/**
	 * Updates all scopes in array
	 * 
	 * @param array $roleScopes - new params for role scopes
	 * @param array $roleId (optional) - ID of role
	 * 
	 * @return boolean
	 */
	protected function updateRoleScopes(array $roleScopes, $roleId)
	{

		$this->db->table('role_scopes')
			 ->where('role_id', '=', $roleId)
			 ->delete();

		foreach ($roleScopes as $key => $params) {
			// if option is new - insert
			$roleScopeId = $this->db->table('role_scopes')->insertGetId($params);
		}
	}


	/**
	 * Update role
	 *
	 * @param array $model - role params (name, description...)
	 *
	 * @return mixed
	 *
	 * @throws Cookbook\Core\Exceptions\NotFoundException
	 */
	protected function _update($id, $model)
	{

		// get scopes from model if there are any
		$scopes = false;
		if(!empty($model['scopes']) && is_array($model['scopes']))
		{
			$scopes = $model['scopes'];
		}
		
		// unset scopes from model 
		// for attribute insertation
		unset($model['scopes']);

		// find role with that ID
		$role = $this->fetch($id);

		if (! $role) {
			throw new NotFoundException(['There is no role with that ID.']);
		}

		$model['updated_at'] = Carbon::now('UTC')->toDateTimeString();

		$this->db->table($this->table)->where('id', '=', $id)->update($model);

		if($scopes !== false) {
			$roleScopeParams = [];

			// set relation to role in all scopes
			for($i = 0; $i < count($scopes); $i++)
			{
				$roleScopeParam = [];
				$roleScopeParam['role_id'] = $id;
				$roleScopeParam['scope_id'] = $scopes[$i];
				$roleScopeParam['created_at'] = $roleScopeParam['updated_at'] = Carbon::now('UTC')->toDateTimeString();
				$roleScopeParams[] = $roleScopeParam;
			}

			// update all scopes for role
			$this->updateRoleScopes($roleScopeParams, $id);
		}
		

		Trunk::forgetType('role');
		$role = $this->fetch($id);

		// and return role
		return $role;
	}

	/**
	 * Delete role from database
	 *
	 * @param integer $id - ID of role that will be deleted
	 *
	 * @return boolean
	 *
	 * @throws Cookbook\Core\Exceptions\NotFoundException
	 */
	protected function _delete($id)
	{
		// get the role
		$role = $this->fetch($id);
		if (!$role)
		{
			throw new NotFoundException(['There is no role with that ID.']);
		}

		$this->deleteRoleScopes($role->id);
		$this->deleteUserRoles($role->id);

		// delete the role
		$this->db->table($this->table)->where('id', '=', $role->id)->delete();
		Trunk::forgetType('role');
		return $role;
	}

	
	/**
	 * Delete role scopes by role
	 * 
	 * @param array 	$roleIds
	 * 
	 * @return boolean
	 * 
	 * @throws InvalidArgumentException
	 */
	protected function deleteRoleScopes($roleIds)
	{
		if( ! is_array($roleIds) )
		{
			$roleIds = [$roleIds];
		}

		$this->db->table('role_scopes')
				 ->whereIn('role_id', $roleIds)
				 ->delete();
	}

	/**
	 * Delete user roles by role
	 * 
	 * @param array 	$roleIds
	 * 
	 * @return boolean
	 * 
	 * @throws InvalidArgumentException
	 */
	protected function deleteUserRoles($roleIds)
	{
		if( ! is_array($roleIds) )
		{
			$roleIds = [$roleIds];
		}

		$this->db->table('user_roles')
				 ->whereIn('role_id', $roleIds)
				 ->delete();
	}

// ----------------------------------------------------------------------------------------------
// GETTERS
// ----------------------------------------------------------------------------------------------
//
//
//

	/**
	 * Get role by ID
	 *
	 * @param int $id - ID of role to be fetched
	 *
	 * @return array
	 */
	protected function _fetch($id, $include = [])
	{
		$params = func_get_args();
		$params['function'] = __METHOD__;
		
		if (Trunk::has($params, 'role')) {
			$role = Trunk::get($params, 'role');
			$role->clearIncluded();
			$role->load($include);
			$meta = ['id' => $id, 'include' => $include];
			$role->setMeta($meta);
			return $role;
		}

		$role = $this->db->table($this->table)
						 // ->select('id', 'name', 'description', 'created_at', 'updated_at')
						 ->find($id);
		
		if (! $role) {
			throw new NotFoundException(['There is no role with that ID.']);
		}

		$scopes = $this->db->table('role_scopes')
							->where('role_id', '=', $id)
							->get();

		$role->scopes = [];
		foreach ($scopes as $scope) 
		{
			$role->scopes[] = $scope->scope_id;
		}

		$role->type = $this->type;

		$timezone = (Config::get('app.timezone'))?Config::get('app.timezone'):'UTC';
		$role->created_at = Carbon::parse($role->created_at)->tz($timezone);
		$role->updated_at = Carbon::parse($role->updated_at)->tz($timezone);

		$result = new Model($role);
		
		$result->setParams($params);
		$meta = ['id' => $id, 'include' => $include];
		$result->setMeta($meta);
		$result->load($include);
		return $result;
	}

	/**
	 * Get role
	 *
	 * @return array
	 */
	protected function _get($filter = [], $offset = 0, $limit = 0, $sort = [], $include = [])
	{
		$params = func_get_args();
		$params['function'] = __METHOD__;

		if (Trunk::has($params, 'role')) {
			$roles = Trunk::get($params, 'role');
			$roles->clearIncluded();
			$roles->load($include);
			$meta = [
				'include' => $include
			];
			$roles->setMeta($meta);
			return $roles;
		}

		$query = $this->db->table($this->table);
		
		$query = $query->select('id', 'name', 'description', 'created_at', 'updated_at');

		$query = $this->parseFilters($query, $filter);

		$total = $query->count();

		$query = $this->parsePaging($query, $offset, $limit);

		$query = $this->parseSorting($query, $sort);
		
		$roles = $query->get();

		if (! $roles) {
			$roles = [];
		}

		$roleIds = [];
		
		foreach ($roles as &$role) {
			$role->type = $this->type;

			$timezone = (Config::get('app.timezone'))?Config::get('app.timezone'):'UTC';
			$role->created_at = Carbon::parse($role->created_at)->tz($timezone);
			$role->updated_at = Carbon::parse($role->updated_at)->tz($timezone);

			$roleIds[] = $role->id;
			$role->scopes = [];
		}

		$scopes = [];
		
		if( ! empty($roleIds) )
		{
			$scopes = $this->db->table('role_scopes')
							->whereIn('role_id', $roleIds)
							->get();
		}
		
		foreach ($scopes as $scope) 
		{
			foreach ($roles as &$role)
			{
				if($role->id == $scope->role_id)
				{
					$role->scopes[] = $scope->scope_id;
					break;
				}
			}
		}

		$result = new Collection($roles);
		
		$result->setParams($params);

		$meta = [
			'count' => count($result),
			'offset' => $offset,
			'limit' => $limit,
			'total' => $total,
			'filter' => $filter,
			'sort' => $sort,
			'include' => $include
		];
		$result->setMeta($meta);

		$result->load($include);
		
		return $result;
	}
}
