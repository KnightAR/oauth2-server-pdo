<?php
/**
 * Created by IntelliJ IDEA.
 * User: david
 * Date: 16.03.16
 * Time: 10:09
 */

namespace DBoho\OAuth2\Server\Storage\PDO;


use League\OAuth2\Server\Storage\AbstractStorage;

class Storage extends AbstractStorage
{
	/**
	 * @var \PDO
	 */
	protected $pdo;


	/**
	 * Storage constructor.
	 * @param \PDO $pdo
	 */
	public function __construct(\PDO $pdo)
	{
		$this->pdo = $pdo;
	}
}