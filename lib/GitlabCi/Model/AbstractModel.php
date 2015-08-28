<?php

namespace GitlabCi\Model;

use GitlabCi\Client;
use GitlabCi\Exception\RuntimeException;

abstract class AbstractModel
{
	protected static $_properties;

	protected $_data = [];
	protected $_client = NULL;

	public function api($api)
	{
		return $this->getClient()->api($api);
	}

	public function getClient()
	{
		return $this->_client;
	}

	public function setClient(Client $client = NULL)
	{
		if (NULL !== $client) {
			$this->_client = $client;
		}

		return $this;
	}

	public function hydrate(array $data = [])
	{
		if (!empty($data)) {
			foreach ($data as $k => $v) {
				if (in_array($k, static::$_properties)) {
					$this->$k = $v;
				}
			}
		}

		return $this;
	}

	public function __get($property)
	{
		if (!in_array($property, static::$_properties)) {
			throw new RuntimeException(sprintf(
				'Property "%s" does not exist for %s object',
				$property, get_called_class()
			));
		}

		if (isset($this->_data[$property])) {
			return $this->_data[$property];
		}

		return NULL;
	}

	public function __set($property, $value)
	{
		if (!in_array($property, static::$_properties)) {
			throw new RuntimeException(sprintf(
				'Property "%s" does not exist for %s object', $property, get_called_class()
			));
		}

		$this->_data[$property] = $value;
	}

}
