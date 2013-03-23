<?php

require_once dirname(__FILE__) . '/AbstractKleinTest.php';

class HeadersTest extends AbstractKleinTest {
	public function setUp() {
		parent::setUp();

		$this->headers = new HeadersEcho;
	}
	public function testResponseCode() {
		$this->expectOutputString("HTTP/1.1 404 Not Found\n");
		$this->headers->header( 'HTTP/1.1 404 Not Found' );
	}

	public function testBlankHeader() {
		$this->expectOutputString("Foo: \n");
		$this->headers->header( 'Foo', '' );
	}

	public function testHeaderKeyValue() {
		$this->expectOutputString("Foo: Bar\n");
		$this->headers->header( 'Foo', 'Bar' );
	}

	public function testHeaderKeyTransform() {
		$this->expectOutputString("Foo-Bar: baz\n");
		$this->headers->header( 'foo bar', 'baz' );
	}
}
