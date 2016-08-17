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

use Cookbook\Contracts\OAuth2\ScopeRepositoryContract;
use League\OAuth2\Server\Storage\ScopeInterface;
use League\OAuth2\Server\Storage\AbstractStorage;
use League\OAuth2\Server\Entity\ScopeEntity;
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
 * ScopeRepository class
 *
 * Repository for scope database queries
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
class ScopeRepository extends AbstractStorage implements ScopeRepositoryContract, ScopeInterface//, UsesCache
{
// ----------------------------------------------------------------------------------------------
// PARAMS
// ----------------------------------------------------------------------------------------------
//
//
//

	/**
	 * Object type
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * The database connection to use.
	 *
	 * @var Illuminate\Database\Connection
	 */
	protected $db;

	/**
	 * Create new ScopeRepository
	 *
	 * @param Illuminate\Database\Connection $db
	 *
	 * @return void
	 */
	public function __construct(Connection $db)
	{
		$this->type = 'scope';
		$this->table = 'oauth_scopes';

		$this->setConnection($db);
	}

	/**
	 * Set the connection to run queries on.
	 *
	 * @param \Illuminate\Database\Connection $db
	 *
	 * @return $this
	 */
	public function setConnection(Connection $db)
	{
		$this->db = $db;
		return $this;
	}	


// ----------------------------------------------------------------------------------------------
// GETTERS
// ----------------------------------------------------------------------------------------------
//
//
//


	/**
     * Return information about a scope.
     *
     * Example SQL query:
     *
     * <code>
     * SELECT * FROM oauth_scopes WHERE scope = :scope
     * </code>
     *
     * @param string $scope The scope
     * @param string $grantType The grant type used in the request (default = "null")
     * @param string $clientId The client id used for the request (default = "null")
     *
     * @return \League\OAuth2\Server\Entity\ScopeEntity|null If the scope doesn't exist return false
     */
    public function get($scope, $grantType = null, $clientId = null)
    {
    	$limitClientsToScopes = Config::get('oauth2.limit_clients_to_scopes');
        $limitScopesToGrants = Config::get('oauth2.limit_scopes_to_grants');

        $query = $this->db->table($this->table)
                    ->select('oauth_scopes.id as id', 'oauth_scopes.label as label', 'oauth_scopes.description as description')
                    ->where('oauth_scopes.id', $scope);

        if ($limitClientsToScopes && !is_null($clientId)) {
            $query = $query->join('oauth_client_scopes', 'oauth_scopes.id', '=', 'oauth_client_scopes.scope_id')
                           ->where('oauth_client_scopes.client_id', $clientId);
        }

        if ($limitScopesToGrants && !is_null($grantType)) {
            $query = $query->join('oauth_grant_scopes', 'oauth_scopes.id', '=', 'oauth_grant_scopes.scope_id')
                           ->join('oauth_grants', 'oauth_grants.id', '=', 'oauth_grant_scopes.grant_id')
                           ->where('oauth_grants.id', $grantType);
        }

        $result = $query->first();

        if (is_null($result)) {
            return;
        }

        $scope = new ScopeEntity($this->getServer());
        $scope->hydrate([
            'id' => $result->id,
            'description' => $result->label,
        ]);

        return $scope;
    }


	/**
	 * Get scopes
	 *
	 * @return array
	 */
	public function getAll()
	{
		$params = func_get_args();
		$params['function'] = __METHOD__;

		if (Trunk::has($params, 'scope')) {
			$scopes = Trunk::get($params, 'scope');
			return $scopes;
		}

		$query = $this->db->table($this->table);
		
		$query = $query->select('id', 'label', 'description', 'created_at', 'updated_at');

		$total = $query->count();
		
		$scopes = $query->get();

		if (! $scopes) {
			$scopes = [];
		}
		
		foreach ($scopes as &$scope) {
			$scope->type = $this->type;

			$timezone = (Config::get('app.timezone'))?Config::get('app.timezone'):'UTC';
			$scope->created_at = Carbon::parse($scope->created_at)->tz($timezone);
			$scope->updated_at = Carbon::parse($scope->updated_at)->tz($timezone);
		}

		$result = new Collection($scopes);
		
		$result->setParams($params);
		
		return $result;
	}
}
