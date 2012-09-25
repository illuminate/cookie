<?php

use Mockery as m;
use Illuminate\CookieJar;
use Symfony\Component\HttpFoundation\Request;

class CookieTest extends PHPUnit_Framework_TestCase {

	public function tearDown()
	{
		m::close();
	}


	public function testCookiesAreCreatedWithProperOptions()
	{
		$cookie = $this->getCreator();
		$c = $cookie->make('color', 'blue', 10);
		$this->assertEquals($cookie->hash('blue').'+blue', $c->getValue());
		$this->assertFalse($c->isHttpOnly());
		$this->assertTrue($c->isSecure());
		$this->assertEquals('/domain', $c->getDomain());
		$this->assertEquals('/path', $c->getPath());

		$c2 = $cookie->forever('color', 'blue');
		$this->assertEquals($cookie->hash('blue').'+blue', $c->getValue());
		$this->assertFalse($c->isHttpOnly());
		$this->assertTrue($c->isSecure());
		$this->assertEquals('/domain', $c->getDomain());
		$this->assertEquals('/path', $c->getPath());
	}


	public function testCookiesAreProperlyParsed()
	{
		$cookie = $this->getCreator();
		$cookie->getRequest()->cookies->set('foo', $cookie->hash('bar').'+bar');
		$this->assertEquals('bar', $cookie->get('foo'));

		$cookie = $this->getCreator();
		$cookie->getRequest()->cookies->set('foo', $cookie->hash('bar').'bar');
		$this->assertNull($cookie->get('foo'));

		$cookie = $this->getCreator();
		$cookie->getRequest()->cookies->set('foo', $cookie->hash('bar').'291+bar');
		$this->assertNull($cookie->get('foo'));
	}


	public function getCreator()
	{
		return new CookieJar(Request::create('/foo', 'GET'), 'foo-bar', array(
			'path'     => '/path',
			'domain'   => '/domain',
			'secure'   => true,
			'httpOnly' => false,
		));
	}

}