<?php

use Illuminate\CookieCreator;

class CookieTest extends PHPUnit_Framework_TestCase {

	public function testCookiesAreCreatedWithProperOptions()
	{
		$cookie = new CookieCreator('/path', '/domain', true, false);
		$c = $cookie->make('color', 'blue', 10);
		$this->assertEquals('blue', $c->getValue());
		$this->assertFalse($c->isHttpOnly());
		$this->assertTrue($c->isSecure());
		$this->assertEquals('/domain', $c->getDomain());
		$this->assertEquals('/path', $c->getPath());

		$c2 = $cookie->forever('color', 'blue');
		$this->assertEquals('blue', $c->getValue());
		$this->assertFalse($c->isHttpOnly());
		$this->assertTrue($c->isSecure());
		$this->assertEquals('/domain', $c->getDomain());
		$this->assertEquals('/path', $c->getPath());
	}

}