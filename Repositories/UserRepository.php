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

use Carbon\Carbon;
use Cookbook\Contracts\OAuth2\UserRepositoryContract;
use Cookbook\Core\Exceptions\Exception;
use Cookbook\Core\Exceptions\NotFoundException;
use Cookbook\Core\Facades\Trunk;
use Cookbook\Core\Repositories\AbstractRepository;
use Cookbook\Core\Repositories\Collection;
use Cookbook\Core\Repositories\Model;
use Cookbook\Core\Repositories\UsesCache;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use stdClass;

/**
 * UserRepository class
 *
 * Repository for user database queries
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
class UserRepository extends AbstractRepository implements UserRepositoryContract, UserProvider//, UsesCache
{
	// ----------------------------------------------------------------------------------------------
// PARAMS
// ----------------------------------------------------------------------------------------------
//
//
//

	/**
	 * Create new UserRepository
	 *
	 * @param Illuminate\Database\Connection $db
	 *
	 * @return void
	 */
	public function __construct(Connection $db)
	{
		$this->type = 'user';
		$this->table = 'users';

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
	 * Create new user
	 *
	 * @param array $model - user params (name, email, password...)
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 */
	protected function _create($model)
	{
		// get roles from model if there are any
		$roles = [];
		if(!empty($model['roles']) && is_array($model['roles']))
		{
			$roles = $model['roles'];
		}
		
		// unset roles from model 
		// for attribute insertation
		unset($model['roles']);

		$model['created_at'] = $model['updated_at'] = Carbon::now('UTC')->toDateTimeString();

		// insert user in database
		$userId = $this->db->table('users')->insertGetId($model);

		if (!$userId) {
			throw new \Exception('Failed to insert user');
		}

		$userRoleParams = [];

		// set relation to role in all scopes
		for($i = 0; $i < count($roles); $i++)
		{
			$userRoleParam = [];
			$userRoleParam['user_id'] = $userId;
			$userRoleParam['role_id'] = $roles[$i]['id'];
			$userRoleParam['created_at'] = $roleScopeParam['updated_at'] = Carbon::now('UTC')->toDateTimeString();
			$userRoleParams[] = $userRoleParam;
		}

		// update all roles for user
		$this->updateUserRoles($userRoleParams, $userId);

		// get user
		$user = $this->fetch($userId);

		// and return newly created user
		return $user;
	}

	/**
	 * Update user
	 *
	 * @param array $model - user params (name, email, password...)
	 *
	 * @return mixed
	 *
	 * @throws Cookbook\Core\Exceptions\NotFoundException
	 */
	protected function _update($id, $model)
	{
		// get roles from model if there are any
		$roles = false;
		if(!empty($model['roles']) && is_array($model['roles']))
		{
			$roles = $model['roles'];
		}
		
		// unset roles from model 
		// for attribute insertation
		unset($model['roles']);

		// find user with that ID
		$user = $this->fetch($id);

		if (! $user) {
			throw new NotFoundException(['There is no user with that ID.']);
		}

		$model['updated_at'] = Carbon::now('UTC')->toDateTimeString();

		$this->db->table('users')->where('id', '=', $id)->update($model);

		if($roles !== false) {
			$userRoleParams = [];

			// set relation to role in all scopes
			for($i = 0; $i < count($roles); $i++)
			{
				$userRoleParam = [];
				$userRoleParam['user_id'] = $id;
				$userRoleParam['role_id'] = $roles[$i]['id'];
				$userRoleParam['created_at'] = $roleScopeParam['updated_at'] = Carbon::now('UTC')->toDateTimeString();
				$userRoleParams[] = $userRoleParam;
			}

			// update all roles for user
			$this->updateUserRoles($userRoleParams, $id);
		}

		Trunk::forgetType('user');
		$user = $this->fetch($id);

		// and return user
		return $user;
	}

	/**
	 * Updates all roles in array
	 * 
	 * @param array $userRoles - new params for user roles
	 * @param array $userId (optional) - ID of user
	 * 
	 * @return boolean
	 */
	protected function updateUserRoles(array $userRoles, $userId)
	{

		$this->db->table('user_roles')
			 ->where('user_id', '=', $userId)
			 ->delete();

		foreach ($userRoles as $key => $params) {
			// if option is new - insert
			$userRoleId = $this->db->table('user_roles')->insertGetId($params);
		}
	}

	/**
	 * Change user password
	 *
	 * @param int $id - user id
	 * @param string $password - new password
	 *
	 * @return mixed
	 *
	 * @throws Cookbook\Core\Exceptions\NotFoundException
	 */
	public function changePassword($id, $password)
	{

		// find user with that ID
		$user = $this->fetch($id);

		if (! $user) {
			throw new NotFoundException(['There is no user with that ID.']);
		}

		$model['updated_at'] = Carbon::now('UTC')->toDateTimeString();

		$this->db->table('users')->where('id', '=', $id)->update(['password' => $password]);

		Trunk::forgetType('user');
		$user = $this->fetch($id);

		// and return user
		return $user;
	}

	/**
	 * Delete user from database
	 *
	 * @param integer $id - ID of user that will be deleted
	 *
	 * @return boolean
	 *
	 * @throws Cookbook\Core\Exceptions\NotFoundException
	 */
	protected function _delete($id)
	{
		// get the user
		$user = $this->fetch($id);
		if (!$user) {
			throw new NotFoundException(['There is no user with that ID.']);
		}

		$this->deleteUserRoles($user->id);
		
		// delete the user
		$this->db->table('users')->where('id', '=', $user->id)->delete();
		Trunk::forgetType('user');
		return $user;
	}

	/**
	 * Delete user roles by user
	 * 
	 * @param array 	$userIds
	 * 
	 * @return boolean
	 * 
	 * @throws InvalidArgumentException
	 */
	protected function deleteUserRoles($userIds)
	{
		if( ! is_array($userIds) )
		{
			$userIds = [$userIds];
		}

		$this->db->table('user_roles')
				 ->whereIn('user_id', $userIds)
				 ->delete();
	}
	


// ----------------------------------------------------------------------------------------------
// GETTERS
// ----------------------------------------------------------------------------------------------
//
//
//

	/**
	 * Get user by ID
	 *
	 * @param int $id - ID of user to be fetched
	 *
	 * @return array
	 */
	protected function _fetch($id, $include = [])
	{
		$params = func_get_args();
		$params['function'] = __METHOD__;
		
		if (Trunk::has($params, 'user')) {
			$user = Trunk::get($params, 'user');
			$user->clearIncluded();
			$user->load($include);
			$meta = ['id' => $id, 'include' => $include];
			$user->setMeta($meta);
			return $user;
		}

		$user = $this->db->table('users')
						 ->find($id);
		
		if (! $user) {
			throw new NotFoundException(['There is no user with that ID.']);
		}

		$roles = $this->db->table('user_roles')
							->where('user_id', '=', $id)
							->get();

		$user->roles = [];
		foreach ($roles as $role) 
		{
			$newRole = new stdClass();
			$newRole->id = $role->role_id;
			$newRole->type = 'role';
			$user->roles[] = $newRole;
		}

		$user->type = 'user';

		$timezone = (Config::get('app.timezone'))?Config::get('app.timezone'):'UTC';
		$user->created_at = Carbon::parse($user->created_at)->tz($timezone);
		$user->updated_at = Carbon::parse($user->updated_at)->tz($timezone);

		$result = new UserModel($user);
		
		$result->setParams($params);
		$meta = ['id' => $id, 'include' => $include];
		$result->setMeta($meta);
		$result->load($include);
		return $result;
	}

	/**
	 * Get user as owner by ID
	 *
	 * @param int $id - ID of user to be fetched
	 *
	 * @return array
	 */
	public function fetchOwner($id)
	{

		$user = $this->db->table($this->table)
						 ->select('id', 'name', 'email', 'created_at', 'updated_at')
						 ->find($id);
		
		if (! $user) {
			throw new NotFoundException(['There is no user with that ID.']);
		}

		$scopes = $this->db->table('oauth_scopes')
							->select('oauth_scopes.id as id')
							->join('role_scopes', 'oauth_scopes.id', '=', 'role_scopes.scope_id')
							->join('user_roles', 'user_roles.role_id', '=', 'role_scopes.role_id')
							->where('user_roles.user_id', '=', $id)
							->get();

		$user->scopes = [];
		foreach ($scopes as $scope) 
		{
			$user->scopes[] = $scope->id;
		}

		$user->type = $this->type;

		$timezone = (Config::get('app.timezone'))?Config::get('app.timezone'):'UTC';
		$user->created_at = Carbon::parse($user->created_at)->tz($timezone);
		$user->updated_at = Carbon::parse($user->updated_at)->tz($timezone);

		$result = new Model($user);
		
		return $result;
	}

	/**
	 * Get users
	 *
	 * @return array
	 */
	protected function _get($filter = [], $offset = 0, $limit = 0, $sort = [], $include = [])
	{
		$params = func_get_args();
		$params['function'] = __METHOD__;

		if (Trunk::has($params, 'user')) {
			$users = Trunk::get($params, 'user');
			$users->clearIncluded();
			$users->load($include);
			$meta = [
				'include' => $include
			];
			$users->setMeta($meta);
			return $users;
		}

		$query = $this->db->table('users');

		$query = $this->parseFilters($query, $filter);

		$total = $query->count();

		$query = $this->parsePaging($query, $offset, $limit);

		$query = $this->parseSorting($query, $sort);
		
		$users = $query->get();

		if (! $users) {
			$users = [];
		}

		$userIds = [];
		
		foreach ($users as &$user) {
			$user->type = 'user';
			$timezone = (Config::get('app.timezone'))?Config::get('app.timezone'):'UTC';
			$user->created_at = Carbon::parse($user->created_at)->tz($timezone);
			$user->updated_at = Carbon::parse($user->updated_at)->tz($timezone);

			$userIds[] = $user->id;
			$user->roles = [];
		}

		$roles = [];
		
		if( ! empty($userIds) )
		{
			$roles = $this->db->table('user_roles')
							->whereIn('user_id', $userIds)
							->get();
		}
		
		
		foreach ($roles as $role) 
		{
			foreach ($users as &$user)
			{
				if($user->id == $role->user_id)
				{
					$newRole = new stdClass();
					$newRole->id = $role->role_id;
					$newRole->type = 'role';
					$user->roles[] = $newRole;
					break;
				}
			}
		}

		$result = new Collection($users, UserModel::class);
		
		$result->setParams($params);

		$meta = [
			'count' => count($users),
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

	// UserProvider functions
	

	/**
	 * Retrieve a user by their unique identifier.
	 *
	 * @param  mixed  $identifier
	 * @return \Illuminate\Contracts\Auth\Authenticatable|null
	 */
	public function retrieveById($identifier)
	{
		try
		{
			return $this->fetch($identifier);
		}
		catch(NotFoundException $e)
		{
			return null;
		}
	}

	/**
	 * Retrieve a user by their unique identifier and "remember me" token.
	 *
	 * @param  mixed   $identifier
	 * @param  string  $token
	 * @return \Illuminate\Contracts\Auth\Authenticatable|null
	 */
	public function retrieveByToken($identifier, $token)
	{
		$params = func_get_args();
		$params['function'] = __METHOD__;
		
		if (Trunk::has($params, 'user')) {
			$user = Trunk::get($params, 'user');
			$user->clearIncluded();
			$meta = ['id' => $id];
			$user->setMeta($meta);
			return $user;
		}

		$user = $this->db->table('users')
						 ->select('id', 'name', 'email', 'created_at', 'updated_at')
						 ->where('id', '=', $identifier)
						 ->where('remember_token', '=', $token)
						 ->first();
		
		if (! $user) {
			return null;
		}

		$user->type = 'user';

		$timezone = (Config::get('app.timezone'))?Config::get('app.timezone'):'UTC';
		$user->created_at = Carbon::parse($user->created_at)->tz($timezone);
		$user->updated_at = Carbon::parse($user->updated_at)->tz($timezone);

		$result = new UserModel($user);
		
		$result->setParams($params);
		$meta = ['id' => $id];
		$result->setMeta($meta);
		return $result;
	}

	/**
	 * Update the "remember me" token for the given user in storage.
	 *
	 * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
	 * @param  string  $token
	 * @return void
	 */
	public function updateRememberToken(Authenticatable $user, $token)
	{
		$this->db->table('users')
				 ->where('id', '=', $user->getAuthIdentifier())
				 ->update(['remember_token' => $token]);
		Trunk::forgetType('user');
	}

	/**
	 * Retrieve a user by the given credentials.
	 *
	 * @param  array  $credentials
	 * @return \Illuminate\Contracts\Auth\Authenticatable|null
	 */
	public function retrieveByCredentials(array $credentials)
	{
		// First we will add each credential element to the query as a where clause.
		// Then we can execute the query and, if we found a user, return it in a
		// generic "user" object that will be utilized by the Guard instances.
		$query = $this->db->table('users');

		foreach ($credentials as $key => $value)
		{
			if (! Str::contains($key, 'password'))
			{
				$query->where($key, $value);
			}
		}

		// Now we are ready to execute the query to see if we have an user matching
		// the given credentials. If not, we will just return nulls and indicate
		// that there are no matching users for these given credential arrays.
		$user = $query->first();

		if (! $user) {
			return null;
		}

		$user->type = 'user';

		$timezone = (Config::get('app.timezone'))?Config::get('app.timezone'):'UTC';
		$user->created_at = Carbon::parse($user->created_at)->tz($timezone);
		$user->updated_at = Carbon::parse($user->updated_at)->tz($timezone);

		$result = new UserModel($user);
		
		return $result;
	}

	/**
	 * Validate a user against the given credentials.
	 *
	 * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
	 * @param  array  $credentials
	 * @return bool
	 */
	public function validateCredentials(Authenticatable $user, array $credentials)
	{
		$plain = $credentials['password'];

		return Hash::check($plain, $user->getAuthPassword());
	}
}
