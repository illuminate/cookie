<?php namespace Illuminate;

use Closure;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CookieJar {

	/*
	 * The current request instance.
	 *
	 * @var Symfony\Component\HttpFoundation\Request
	 */
	protected $request;

	/**
	 * The secret key used for fingerprinting.
	 *
	 * @var string
	 */
	protected $key;

	/**
	 * The default cookie options.
	 *
	 * @var array
	 */
	protected $defaults = array();

	/**
	 * The cookies queued by the creator.
	 *
	 * @var array
	 */
	protected $queued = array();

	/**
	 * Create a new cookie manager instance.
	 *
	 * @param  Symfony\Component\HttpFoundation\Request  $request
	 * @param  string  $key
	 * @param  array   $defaults
	 * @return void
	 */
	public function __construct(Request $request, $key, array $defaults)
	{
		$this->key = $key;
		$this->request = $request;
		$this->defaults = $defaults;
	}

	/**
	 * Determine if a cookie exists and is not null.
	 *
	 * @param  string  $key
	 * @return bool
	 */
	public function has($key)
	{
		return ! is_null($this->get($key));
	}

	/**
	 * Get the value of the given cookie.
	 *
	 * @param  string  $key
	 * @param  mixed   $default
	 * @return mixed
	 */
	public function get($key, $default = null)
	{
		$value = $this->parse($this->request->cookies->get($key));

		if (is_null($value))
		{
			return $default instanceof Closure ? $default() : $default;
		}

		return $value;
	}

	/**
	 * Put a new cookie in the queue.
	 *
	 * @param  string  $name
	 * @param  string  $value
	 * @param  int     $minutes
	 * @return Symfony\Component\HttpFoundation\Cookie
	 */
	public function put($name, $value, $minutes = 0)
	{
		$this->queued[] = $cookie = $this->make($name, $value, $minutes);

		return $cookie;
	}

	/**
	 * Create a new cookie instance.
	 *
	 * @param  string  $name
	 * @param  string  $value
	 * @param  int     $minutes
	 * @return Symfony\Component\HttpFoundation\Cookie
	 */
	public function make($name, $value, $minutes = 0)
	{
		extract($this->defaults);

		if ($minutes == 0)
		{
			$time = 0;
		}
		else
		{
			$time = time() + ($minutes * 60);
		}

		return new Cookie($name, $this->hash($value).'+'.$value, $time, $path, $domain, $secure, $httpOnly);
	}

	/**
	 * Create a cookie that lasts "forever" (five years).
	 *
	 * @param  string  $name
	 * @param  string  $value
	 * @return Symfony\Component\HttpFoundation\Cookie
	 */
	public function forever($name, $value)
	{
		return $this->make($name, $value, 2628000);
	}

	/**
	 * Expire the given cookie.
	 *
	 * @param  string  $name
	 * @return Symfony\Component\HttpFoundation\Cookie
	 */
	public function forget($name)
	{
		return $this->make($name, null, -2628000);
	}

	/**
	 * Hash the given cookie value using the key.
	 *
	 * @param  string  $value
	 * @return string
	 */
	public function hash($value)
	{
		return hash_hmac('sha1', $value, $this->key);
	}

	/**
	 * Parse the fingerprinted cookie value.
	 *
	 * @param  string       $value
	 * @return string|null
	 */
	protected function parse($value)
	{
		$segments = explode('+', $value);

		// If there are not even two segments in the array, it means that the cookie
		// value wasn't set by the application or was changed on the client so we
		// will return "null" since we can't assume this cookie values is safe.
		if (count($segments) < 2)
		{
			return null;
		}

		$value = implode('+', array_slice($segments, 1));

		// If the first segment in the arrays and the hash of the remaining segments
		// are not equal, it means the cookie has been changed on this client and
		// we will return null since we cannot be assured this cookie is valid.
		if ($this->hash($value) != $segments[0])
		{
			return null;
		}

		return $value;
	}

	/**
	 * Set all of the queued cookies on a Response instance.
	 *
	 * @param  Symfony\Component\HttpFoundation\Response  $response
	 * @return void
	 */
	public function moveQueued(Response $response)
	{
		foreach ($this->queued as $cookie)
		{
			$response->headers->setCookie($cookie);
		}
	}

	/**
	 * Get the request instance.
	 *
	 * @return Symfony\Component\HttpFoundation\Request
	 */
	public function getRequest()
	{
		return $this->request;
	}

}