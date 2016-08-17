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

use Cookbook\Contracts\OAuth2\ClientRepositoryContract;
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
 * ClientRepository class
 *
 * Repository for client database queries
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
class ClientRepository extends AbstractRepository implements ClientRepositoryContract//, UsesCache
{
// ----------------------------------------------------------------------------------------------
// PARAMS
// ----------------------------------------------------------------------------------------------
//
//
//

	/**
	 * Create new ClientRepository
	 *
	 * @param Illuminate\Database\Connection $db
	 *
	 * @return void
	 */
	public function __construct(Connection $db)
	{
		$this->type = 'client';
		$this->table = 'oauth_clients';

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
	 * Create new client
	 *
	 * @param array $model - client params (id, secret...)
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 */
	protected function _create($model)
	{
		$model['secret'] = str_random(40);

		$valid = false;
		do {
			$model['id'] = str_random(40);
			try
			{
				$client = $this->fetch($model['id']);
			} 
			catch (NotFoundException $e)
			{
				$valid = true;
			}
		} while (! $valid);

		$model['created_at'] = $model['updated_at'] = Carbon::now('UTC')->toDateTimeString();

		// get scopes from model if there are any
		$scopes = [];
		if(!empty($model['scopes']) && is_array($model['scopes']))
		{
			$scopes = $model['scopes'];
		}

		// unset scopes from model 
		// for client insertation
		unset($model['scopes']);

		// get grants from model if there are any
		$grants = [];
		if(!empty($model['grants']) && is_array($model['grants']))
		{
			$grants = $model['grants'];
		}

		// unset grants from model 
		// for client insertation
		unset($model['grants']);

		// insert client in database
		$this->db->table($this->table)->insert($model);

		$clientScopeParams = [];

		// set relation to role in all scopes
		for($i = 0; $i < count($scopes); $i++)
		{
			$clientScopeParam = [];
			$clientScopeParam['client_id'] = $model['id'];
			$clientScopeParam['scope_id'] = $scopes[$i];
			$clientScopeParam['created_at'] = $clientScopeParam['updated_at'] = Carbon::now('UTC')->toDateTimeString();
			$clientScopeParams[] = $clientScopeParam;
		}

		// update all scopes for client
		$this->updateClientScopes($clientScopeParams, $model['id']);

		$clientGrantParams = [];

		// set relation to role in all grants
		for($i = 0; $i < count($grants); $i++)
		{
			$clientGrantParam = [];
			$clientGrantParam['client_id'] = $model['id'];
			$clientGrantParam['grant_id'] = $grants[$i];
			$clientGrantParam['created_at'] = $clientGrantParam['updated_at'] = Carbon::now('UTC')->toDateTimeString();
			$clientGrantParams[] = $clientGrantParam;
		}

		// update all scopes for client
		$this->updateClientGrants($clientGrantParams, $model['id']);


		// get client
		$client = $this->fetch($model['id']);

		if (!$client) {
			throw new \Exception('Failed to insert client');
		}

		// and return newly created client
		return $client;
	}

	/**
	 * Update client
	 *
	 * @param array $model - client params (name, description...)
	 *
	 * @return mixed
	 *
	 * @throws Cookbook\Core\Exceptions\NotFoundException
	 */
	protected function _update($id, $model)
	{

		// find client with that ID
		$client = $this->fetch($id);

		if (! $client) {
			throw new NotFoundException(['There is no client with that ID.']);
		}

		// get scopes from model if there are any
		$scopes = false;
		if(!empty($model['scopes']) && is_array($model['scopes']))
		{
			$scopes = $model['scopes'];
		}
		
		// unset scopes from model 
		// for client update
		unset($model['scopes']);

		// get grants from model if there are any
		$grants = false;
		if(!empty($model['grants']) && is_array($model['grants']))
		{
			$grants = $model['grants'];
		}
		
		// unset grants from model 
		// for client update
		unset($model['grants']);

		$model['updated_at'] = Carbon::now('UTC')->toDateTimeString();

		$this->db->table($this->table)->where('id', '=', $id)->update($model);

		if($scopes !== false) {
			$clientScopeParams = [];

			// set relation to role in all scopes
			for($i = 0; $i < count($scopes); $i++)
			{
				$clientScopeParam = [];
				$clientScopeParam['client_id'] = $id;
				$clientScopeParam['scope_id'] = $scopes[$i];
				$clientScopeParam['created_at'] = $clientScopeParam['updated_at'] = Carbon::now('UTC')->toDateTimeString();
				$clientScopeParams[] = $clientScopeParam;
			}

			// update all scopes for role
			$this->updateClientScopes($clientScopeParams, $id);
		}

		if($grants !== false) {
			$clientGrantParams = [];

			// set relation to role in all grants
			for($i = 0; $i < count($grants); $i++)
			{
				$clientGrantParam = [];
				$clientGrantParam['client_id'] = $id;
				$clientGrantParam['grant_id'] = $grants[$i];
				$clientGrantParam['created_at'] = $clientGrantParam['updated_at'] = Carbon::now('UTC')->toDateTimeString();
				$clientGrantParams[] = $clientGrantParam;
			}

			// update all grants for role
			$this->updateClientGrants($clientGrantParams, $id);
		}

		Trunk::forgetType('client');
		$client = $this->fetch($id);

		// and return client
		return $client;
	}

	/**
	 * Updates all scopes in array
	 * 
	 * @param array $clientScopes - new params for client scopes
	 * @param array $clientId (optional) - ID of client
	 * 
	 * @return boolean
	 */
	protected function updateClientScopes(array $clientScopes, $clientId)
	{

		$this->db->table('oauth_client_scopes')
			 ->where('client_id', '=', $clientId)
			 ->delete();

		foreach ($clientScopes as $key => $params) {
			// if option is new - insert
			$clientScopeId = $this->db->table('oauth_client_scopes')->insertGetId($params);
		}
	}

	/**
	 * Updates all grants in array
	 * 
	 * @param array $clientGrants - new params for client grants
	 * @param array $clientId (optional) - ID of client
	 * 
	 * @return boolean
	 */
	protected function updateClientGrants(array $clientGrants, $clientId)
	{

		$this->db->table('oauth_client_grants')
			 ->where('client_id', '=', $clientId)
			 ->delete();

		foreach ($clientGrants as $key => $params) {
			$clientGrantId = $this->db->table('oauth_client_grants')->insertGetId($params);
		}
	}

	/**
	 * Delete client from database
	 *
	 * @param integer $id - ID of client that will be deleted
	 *
	 * @return boolean
	 *
	 * @throws Cookbook\Core\Exceptions\NotFoundException
	 */
	protected function _delete($id)
	{
		// get the client
		$client = $this->fetch($id);
		if (!$client)
		{
			throw new NotFoundException(['There is no client with that ID.']);
		}

		// delete the client
		$this->db->table($this->table)->where('id', '=', $client->id)->delete();
		Trunk::forgetType('access-token');
		Trunk::forgetType('request-token');
		Trunk::forgetType('client');
		return $client;
	}

	


// ----------------------------------------------------------------------------------------------
// GETTERS
// ----------------------------------------------------------------------------------------------
//
//
//

	/**
	 * Get client by ID
	 *
	 * @param int $id - ID of client to be fetched
	 *
	 * @return array
	 */
	protected function _fetch($id, $include = [])
	{
		$params = func_get_args();
		$params['function'] = __METHOD__;
		
		if (Trunk::has($params, 'client')) {
			$client = Trunk::get($params, 'client');
			$client->clearIncluded();
			$client->load($include);
			$meta = ['id' => $id, 'include' => $include];
			$client->setMeta($meta);
			return $client;
		}

		$client = $this->db->table($this->table)
						 ->select('id', 'secret', 'name', 'created_at', 'updated_at')
						 ->find($id);
		
		if (! $client) {
			throw new NotFoundException(['There is no client with that ID.']);
		}

		$scopes = $this->db->table('oauth_client_scopes')
							->where('client_id', '=', $id)
							->get();

		$client->scopes = [];
		foreach ($scopes as $scope) 
		{
			$client->scopes[] = $scope->scope_id;
		}

		$grants = $this->db->table('oauth_client_grants')
							->where('client_id', '=', $id)
							->get();

		$client->grants = [];
		foreach ($grants as $grant) 
		{
			$client->grants[] = $grant->grant_id;
		}

		$client->type = $this->type;

		$timezone = (Config::get('app.timezone'))?Config::get('app.timezone'):'UTC';
		$client->created_at = Carbon::parse($client->created_at)->tz($timezone);
		$client->updated_at = Carbon::parse($client->updated_at)->tz($timezone);

		$result = new Model($client);
		
		$result->setParams($params);
		$meta = ['id' => $id, 'include' => $include];
		$result->setMeta($meta);
		$result->load($include);
		return $result;
	}

	/**
	 * Get client
	 *
	 * @return array
	 */
	protected function _get($filter = [], $offset = 0, $limit = 0, $sort = [], $include = [])
	{
		$params = func_get_args();
		$params['function'] = __METHOD__;

		if (Trunk::has($params, 'client')) {
			$clients = Trunk::get($params, 'client');
			$clients->clearIncluded();
			$clients->load($include);
			$meta = [
				'include' => $include
			];
			$clients->setMeta($meta);
			return $clients;
		}

		$query = $this->db->table($this->table);
		
		$query = $query->select('id', 'secret', 'name', 'created_at', 'updated_at');

		$query = $this->parseFilters($query, $filter);

		$total = $query->count();

		$query = $this->parsePaging($query, $offset, $limit);

		$query = $this->parseSorting($query, $sort);
		
		$clients = $query->get();

		if (! $clients) {
			$clients = [];
		}

		$clientIds = [];

		foreach ($clients as &$client) {
			$client->type = $this->type;

			$timezone = (Config::get('app.timezone'))?Config::get('app.timezone'):'UTC';
			$client->created_at = Carbon::parse($client->created_at)->tz($timezone);
			$client->updated_at = Carbon::parse($client->updated_at)->tz($timezone);

			$clientIds[] = $client->id;
			$client->scopes = [];
			$client->grants = [];
		}

		$scopes = [];
		
		if( ! empty($clientIds) )
		{
			$scopes = $this->db->table('oauth_client_scopes')
							->whereIn('client_id', $clientIds)
							->get();
		}
		
		foreach ($scopes as $scope) 
		{
			foreach ($clients as &$client)
			{
				if($client->id == $scope->client_id)
				{
					$client->scopes[] = $scope->scope_id;
					break;
				}
			}
		}
		
		$grants = [];
		
		if( ! empty($clientIds) )
		{
			$grants = $this->db->table('oauth_client_grants')
							->whereIn('client_id', $clientIds)
							->get();
		}
		
		foreach ($grants as $grant) 
		{
			foreach ($clients as &$client)
			{
				if($client->id == $grant->client_id)
				{
					$client->grants[] = $grant->grant_id;
					break;
				}
			}
		}
		

		$result = new Collection($clients);
		
		$result->setParams($params);

		$meta = [
			'count' => count($clients),
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
