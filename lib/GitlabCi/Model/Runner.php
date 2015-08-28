<?php

namespace GitlabCi\Model;

use GitlabCi\Client;

class Runner extends AbstractModel
{
	protected static $_properties = [
		"id",
		"token"
	];

	public function __construct($id = NULL)
	{
		$this->id = $id;
	}

	public static function register(Client $client, $token, $public_key)
	{
		$data = $client->api('runners')->register($token, $public_key);

		return static::fromArray($client, $data);
	}

	public static function fromArray(Client $client, array $data)
	{
		$runner = new static($data['id']);
		$runner->setClient($client);

		return $runner->hydrate($data);
	}

	public static function all(Client $client)
	{
		$data = $client->api('runners')->all();
		$runners = [];
		foreach ($data as $runnerData) {
			$runners[] = static::fromArray($client, $runnerData);
		}

		return $runners;
	}
}
