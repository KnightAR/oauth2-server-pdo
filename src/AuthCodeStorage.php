<?php
/**
 * Created by IntelliJ IDEA.
 * User: david
 * Date: 16.03.16
 * Time: 19:32
 */

namespace DBoho\OAuth2\Server\Storage\PDO;


use League\OAuth2\Server\Entity\AuthCodeEntity;
use League\OAuth2\Server\Entity\ScopeEntity;
use League\OAuth2\Server\Storage\AuthCodeInterface;
use PDO;

class AuthCodeStorage extends Storage implements AuthCodeInterface
{

	/**
	 * Get the auth code
	 *
	 * @param string $code
	 *
	 * @return \League\OAuth2\Server\Entity\AuthCodeEntity | null
	 */
	public function get($code)
	{
		$stmt = $this->pdo->prepare('SELECT * FROM oauth_auth_codes WHERE auth_code = :authCode');
		$stmt->bindValue(':authCode', $code);
		$stmt->execute();
		$result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
		if (count($result) === 1) {
			$token = new AuthCodeEntity($this->server);
			$token->setId($result[0]['auth_code']);
			$token->setRedirectUri($result[0]['client_redirect_uri']);
			$token->setExpireTime($result[0]['expire_time']);
			return $token;
		}
	}

	/**
	 * Create an auth code.
	 *
	 * @param string $token The token ID
	 * @param integer $expireTime Token expire time
	 * @param integer $sessionId Session identifier
	 * @param string $redirectUri Client redirect uri
	 *
	 * @return void
	 */
	public function create($token, $expireTime, $sessionId, $redirectUri)
	{
		$stmt = $this->pdo->prepare('INSERT INTO oauth_auth_codes (auth_code, expire_time, session_id, client_redirect_uri)
							VALUES (?,?,?,?)');
		$stmt->execute([$token, $expireTime, $sessionId, $redirectUri]);
		return $this->pdo->lastInsertId();

	}

	/**
	 * Get the scopes for an access token
	 *
	 * @param \League\OAuth2\Server\Entity\AuthCodeEntity $token The auth code
	 *
	 * @return \League\OAuth2\Server\Entity\ScopeEntity[] Array of \League\OAuth2\Server\Entity\ScopeEntity
	 */
	public function getScopes(AuthCodeEntity $token)
	{
		$stmt = $this->pdo->prepare('SELECT scope.* FROM oauth_auth_codes as code
							 JOIN oauth_auth_code_scopes AS acs ON(acs.auth_code=code.auth_code)
							 JOIN oauth_scopes as scope ON(scope.id=acs.scope)
							 WHERE code.auth_code = :authCode');
		$stmt->bindValue(':authCode', $token->getId());
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
	 * Associate a scope with an acess token
	 *
	 * @param \League\OAuth2\Server\Entity\AuthCodeEntity $token The auth code
	 * @param \League\OAuth2\Server\Entity\ScopeEntity $scope The scope
	 *
	 * @return void
	 */
	public function associateScope(AuthCodeEntity $token, ScopeEntity $scope)
	{
		$stmt = $this->pdo->prepare('INSERT INTO oauth_auth_code_scopes (auth_code, scope) VALUES (?,?)');
		$stmt->execute([$token->getId(), $scope->getId()]);
	}

	/**
	 * Delete an access token
	 *
	 * @param \League\OAuth2\Server\Entity\AuthCodeEntity $token The access token to delete
	 *
	 * @return void
	 */
	public function delete(AuthCodeEntity $token)
	{
		$stmt = $this->pdo->prepare('DELETE FROM oauth_auth_codes WHERE auth_code = :authCode');
		$stmt->bindValue(':authCode', $token->getId());
		$stmt->execute();
	}
}