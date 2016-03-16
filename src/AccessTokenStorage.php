<?php
/**
 * Created by IntelliJ IDEA.
 * User: david
 * Date: 16.03.16
 * Time: 20:43
 */

namespace DBoho\OAuth2\Server\Storage\PDO;


use League\OAuth2\Server\Entity\AccessTokenEntity;
use League\OAuth2\Server\Entity\ScopeEntity;
use League\OAuth2\Server\Storage\AccessTokenInterface;
use PDO;

class AccessTokenStorage extends Storage implements AccessTokenInterface
{

	/**
	 * Get an instance of Entity\AccessTokenEntity
	 *
	 * @param string $token The access token
	 *
	 * @return \League\OAuth2\Server\Entity\AccessTokenEntity | null
	 */
	public function get($token)
	{
		$stmt = $this->pdo->prepare('SELECT * FROM oauth_access_tokens WHERE access_token = :token');
		$stmt->bindValue(':token', $token);
		$stmt->execute();
		$result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
		if (count($result) === 1) {
			$token = new AccessTokenEntity($this->server);
			$token->setId($result[0]['access_token']);
			$token->setExpireTime($result[0]['expire_time']);
			return $token;
		}
	}

	/**
	 * Get the scopes for an access token
	 *
	 * @param \League\OAuth2\Server\Entity\AccessTokenEntity $token The access token
	 *
	 * @return \League\OAuth2\Server\Entity\ScopeEntity[] Array of \League\OAuth2\Server\Entity\ScopeEntity
	 */
	public function getScopes(AccessTokenEntity $token)
	{
		$stmt = $this->pdo->prepare('SELECT scope.* FROM oauth_access_tokens as token
							 JOIN oauth_access_token_scopes AS acs ON(acs.access_token=token.access_token)
							 JOIN oauth_scopes as scope ON(scope.id=acs.scope)
							 WHERE token.access_token = :token');
		$stmt->bindValue(':token', $token->getId());
		$stmt->execute();
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$scopes = [];
		foreach ($results as $scope) {
			$scopes[] = (new ScopeEntity($this->server))->hydrate([
					'id' => $scope['id'],
					'description' => $scope['description'],
			]);
		}
		return $scopes;

	}

	/**
	 * Creates a new access token
	 *
	 * @param string $token The access token
	 * @param integer $expireTime The expire time expressed as a unix timestamp
	 * @param string|integer $sessionId The session ID
	 *
	 * @return void
	 */
	public function create($token, $expireTime, $sessionId)
	{
		$stmt = $this->pdo->prepare('INSERT INTO oauth_access_tokens (access_token, expire_time, session_id)
							VALUES (?,?,?)');
		$stmt->execute([$token, $expireTime, $sessionId]);
		return $this->pdo->lastInsertId();
	}

	/**
	 * Associate a scope with an acess token
	 *
	 * @param \League\OAuth2\Server\Entity\AccessTokenEntity $token The access token
	 * @param \League\OAuth2\Server\Entity\ScopeEntity $scope The scope
	 *
	 * @return void
	 */
	public function associateScope(AccessTokenEntity $token, ScopeEntity $scope)
	{
		$stmt = $this->pdo->prepare('INSERT INTO oauth_access_token_scopes (access_token, scope) VALUES (?,?)');
		$stmt->execute([$token->getId(), $scope->getId()]);
	}

	/**
	 * Delete an access token
	 *
	 * @param \League\OAuth2\Server\Entity\AccessTokenEntity $token The access token to delete
	 *
	 * @return void
	 */
	public function delete(AccessTokenEntity $token)
	{
		$stmt = $this->pdo->prepare('DELETE FROM oauth_access_tokens WHERE access_token = :token');
		$stmt->bindValue(':token', $token->getId());
		$stmt->execute();
	}
}