<?php
/**
 * Created by IntelliJ IDEA.
 * User: david
 * Date: 16.03.16
 * Time: 17:58
 */

namespace DBoho\OAuth2\Server\Storage\PDO;


use League\OAuth2\Server\Entity\ClientEntity;
use League\OAuth2\Server\Entity\SessionEntity;
use League\OAuth2\Server\Storage\ClientInterface;

class ClientStorage extends Storage implements ClientInterface
{

	/**
	 * Validate a client
	 *
	 * @param string $clientId The client's ID
	 * @param string $clientSecret The client's secret (default = "null")
	 * @param string $redirectUri The client's redirect URI (default = "null")
	 * @param string $grantType The grant type used (default = "null")
	 *
	 * @return \League\OAuth2\Server\Entity\ClientEntity | null
	 */
	public function get($clientId, $clientSecret = null, $redirectUri = null, $grantType = null)
	{
		$sql = 'SELECT clients.* FROM oauth_clients as clients ';
		$where = [];
		$where[] = 'WHERE clients.id = :clientId';
		$binds = [];
		$binds[':clientId'] = $clientId;

		if ($clientSecret != null) {
			$where[] = ' clients.secret = :clientSecret ';
			$binds[':clientSecret'] = $clientSecret;
		}
		if ($redirectUri != null) {
			$sql .= ' LEFT JOIN oauth_client_redirect_uris as redirect ON (redirect.client_id = clients.id) ';
			$where[] = ' redirect.redirect_uri = :redirectUri ';
			$binds[':redirectUri'] = $redirectUri;
		}

		$stmt = $this->pdo->prepare($sql . implode(' AND ', $where));

		$stmt->execute($binds);

		$result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
		if (count($result) === 1) {
			$client = new ClientEntity($this->getServer());
			$client->hydrate($result[0]);
			return $client;
		}

		return null;
	}

	/**
	 * Get the client associated with a session
	 *
	 * @param \League\OAuth2\Server\Entity\SessionEntity $session The session
	 *
	 * @return \League\OAuth2\Server\Entity\ClientEntity | null
	 */
	public function getBySession(SessionEntity $session)
	{
		$stmt = $this->pdo->prepare('SELECT client.id, client.name FROM oauth_clients as client
							LEFT JOIN oauth_sessions as sess  ON(sess.client_id = client.id)
							WHERE sess.id = :sessionId');
		$stmt->bindValue(':sessionId', $session->getId());
		$stmt->execute();

		$result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
		if (count($result) === 1) {
			$client = new ClientEntity($this->getServer());
			$client->hydrate([
					'id' => $result[0]['id'],
					'name' => $result[0]['name']
			]);
			return $client;
		}
	}
}