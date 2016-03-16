<?php
/**
 * Created by IntelliJ IDEA.
 * User: david
 * Date: 16.03.16
 * Time: 21:04
 */

namespace DBoho\OAuth2\Server\Storage\PDO;


use League\OAuth2\Server\Entity\RefreshTokenEntity;
use League\OAuth2\Server\Storage\RefreshTokenInterface;

class RefreshTokenStorage extends Storage implements RefreshTokenInterface
{

	/**
	 * Return a new instance of \League\OAuth2\Server\Entity\RefreshTokenEntity
	 *
	 * @param string $token
	 *
	 * @return \League\OAuth2\Server\Entity\RefreshTokenEntity | null
	 */
	public function get($token)
	{
		$stmt = $this->pdo->prepare('SELECT * FROM oauth_refresh_tokens WHERE refresh_token = :token');
		$stmt->bindValue(':token', $token);
		$stmt->execute();
		$result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
		if (count($result) === 1) {
			$token = new RefreshTokenEntity($this->server);
			$token->setId($result[0]['refresh_token']);
			$token->setExpireTime($result[0]['expire_time']);
			$token->setAccessTokenId($result[0]['access_token']);
			return $token;
		}
	}

	/**
	 * Create a new refresh token_name
	 *
	 * @param string $token
	 * @param integer $expireTime
	 * @param string $accessToken
	 *
	 * @return \League\OAuth2\Server\Entity\RefreshTokenEntity
	 */
	public function create($token, $expireTime, $accessToken)
	{
		$stmt = $this->pdo->prepare('INSERT INTO oauth_refresh_tokens (refresh_token, expire_time, access_token)
							VALUES (?,?,?)');
		$stmt->execute([$token, $expireTime, $accessToken]);
		return $this->pdo->lastInsertId();
	}

	/**
	 * Delete the refresh token
	 *
	 * @param \League\OAuth2\Server\Entity\RefreshTokenEntity $token
	 *
	 * @return void
	 */
	public function delete(RefreshTokenEntity $token)
	{
		$stmt = $this->pdo->prepare('DELETE FROM oauth_refresh_tokens WHERE refresh_token = :token');
		$stmt->bindValue(':token', $token->getId());
		$stmt->execute();
	}
}