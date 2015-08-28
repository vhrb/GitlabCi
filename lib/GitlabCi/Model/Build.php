<?php

namespace GitlabCi\Model;

use GitlabCi\Client;

class Build extends AbstractModel
{
	protected static $_properties = [
		"id",
		"commands",
		"path",
		"ref",
		"sha",
		"build_id",
		"repo_url",
		"before_sha"
	];

	public function __construct($id = NULL)
	{
		$this->id = $id;
	}

	public static function register(Client $client, $token)
	{
		$data = $client->api('builds')->register($token);

		return static::fromArray($client, $data);
	}

	public static function fromArray(Client $client, array $data)
	{
		$build = new static($data['id']);
		$build->setClient($client);

		return $build->hydrate($data);
	}

	public function update($params)
	{
		$this->api('builds')->update($this->id, $params);

		return TRUE;
	}

}
