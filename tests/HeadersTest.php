<?php

require_once dirname(__FILE__) . '/setup.php';

class HeadersTest extends PHPUnit_Framework_TestCase {
	public function testHeaderKey() {
		$this->expectOutputString("Foo: Bar\n");

		$headers = new HeadersEcho;
		$headers->header( 'Foo', 'Bar' );
	}
}
