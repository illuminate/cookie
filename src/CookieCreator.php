<?php namespace Illuminate;

use Symfony\Component\HttpFoundation\Cookie;

class CookieCreator {

	/**
	 * The default cookie options.
	 *
	 * @var array
	 */
	protected $defaults = array();

	/**
	 * Create a new cookie manager instance.
	 *
	 * @param  string  $path
	 * @param  string  $domain
	 * @param  bool    $secure
	 * @param  bool    $httpOnly
	 * @return void
	 */
	public function __construct($path = '/', $domain = null, $secure = false, $httpOnly = true)
	{
		$this->defaults = compact('path', 'domain', 'secure', 'httpOnly');
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

		return new Cookie($name, $value, $time, $path, $domain, $secure, $httpOnly);
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

}