<?php

namespace GitlabCi\HttpClient;

use Buzz\Client\ClientInterface;
use Buzz\Listener\ListenerInterface;
use GitlabCi\Exception\ErrorException;
use GitlabCi\Exception\RuntimeException;
use GitlabCi\HttpClient\Listener\ErrorListener;
use GitlabCi\HttpClient\Message\Request;
use GitlabCi\HttpClient\Message\Response;

/**
 * Performs requests on GitlabCi API. API documentation should be self-explanatory.
 *
 * @author Joseph Bielawski <stloyd@gmail.com>
 */
class HttpClient implements HttpClientInterface
{
	/**
	 * @var array
	 */
	protected $options = [
		'user_agent' => 'php-gitlab-api (http://github.com/m4tthumphrey/php-gitlab-api)',
		'timeout' => 10,
	];

	protected $base_url = NULL;

	/**
	 * @var array
	 */
	protected $listeners = [];
	/**
	 * @var array
	 */
	protected $headers = [];

	private $lastResponse;
	private $lastRequest;

	/**
	 * @param array $options
	 * @param ClientInterface $client
	 */
	public function __construct($baseUrl, array $options, ClientInterface $client)
	{
		$this->base_url = $baseUrl;
		$this->options = array_merge($this->options, $options);
		$this->client = $client;

		$this->addListener(new ErrorListener($this->options));

		$this->clearHeaders();
	}

	/**
	 * {@inheritDoc}
	 */
	public function setOption($name, $value)
	{
		$this->options[$name] = $value;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setHeaders(array $headers)
	{
		$this->headers = array_merge($this->headers, $headers);
	}

	/**
	 * Clears used headers
	 */
	public function clearHeaders()
	{
		$this->headers = [];
	}

	/**
	 * @param ListenerInterface $listener
	 */
	public function addListener(ListenerInterface $listener)
	{
		$this->listeners[get_class($listener)] = $listener;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get($path, array $parameters = [], array $headers = [])
	{
		if (0 < count($parameters)) {
			$path .= (FALSE === strpos($path, '?') ? '?' : '&') . http_build_query($parameters, '', '&');
		}

		return $this->request($path, [], 'GET', $headers);
	}

	/**
	 * {@inheritDoc}
	 */
	public function post($path, array $parameters = [], array $headers = [])
	{
		return $this->request($path, $parameters, 'POST', $headers);
	}

	/**
	 * {@inheritDoc}
	 */
	public function patch($path, array $parameters = [], array $headers = [])
	{
		return $this->request($path, $parameters, 'PATCH', $headers);
	}

	/**
	 * {@inheritDoc}
	 */
	public function delete($path, array $parameters = [], array $headers = [])
	{
		return $this->request($path, $parameters, 'DELETE', $headers);
	}

	/**
	 * {@inheritDoc}
	 */
	public function put($path, array $parameters = [], array $headers = [])
	{
		return $this->request($path, $parameters, 'PUT', $headers);
	}

	/**
	 * {@inheritDoc}
	 */
	public function request($path, array $parameters = [], $httpMethod = 'GET', array $headers = [])
	{
		$path = trim($this->base_url . $path, '/');

		$request = $this->createRequest($httpMethod, $path);
		$request->addHeaders($headers);
		$request->setContent(http_build_query($parameters));

		$hasListeners = 0 < count($this->listeners);
		if ($hasListeners) {
			foreach ($this->listeners as $listener) {
				$listener->preSend($request);
			}
		}

		$response = new Response();

		try {
			$this->client->send($request, $response);
		}
		catch (\LogicException $e) {
			throw new ErrorException($e->getMessage());
		}
		catch (\RuntimeException $e) {
			throw new RuntimeException($e->getMessage());
		}

		$this->lastRequest = $request;
		$this->lastResponse = $response;
// $message = var_export($request, true);$f = @fopen('log.log', 'a');if ($f) {fputs($f, date('d.m.Y H:i:s').' '.$message."\n");fclose($f);}
// $message = var_export($response, true);$f = @fopen('log.log', 'a');if ($f) {fputs($f, date('d.m.Y H:i:s').' '.$message."\n");fclose($f);}

		if ($hasListeners) {
			foreach ($this->listeners as $listener) {
				$listener->postSend($request, $response);
			}
		}

		return $response;
	}

	/**
	 * @return Request
	 */
	public function getLastRequest()
	{
		return $this->lastRequest;
	}

	/**
	 * @return Response
	 */
	public function getLastResponse()
	{
		return $this->lastResponse;
	}

	/**
	 * @param string $httpMethod
	 * @param string $url
	 *
	 * @return Request
	 */
	private function createRequest($httpMethod, $url)
	{
		$request = new Request($httpMethod);
		$request->setHeaders($this->headers);
		$request->fromUrl($url);

		return $request;
	}
}
