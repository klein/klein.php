<?php
/**
 * Klein (klein.php) - A lightning fast router for PHP
 *
 * @author      Chris O'Hara <cohara87@gmail.com>
 * @author      Trevor Suarez (Rican7) (contributor and v2 refactorer)
 * @copyright   (c) Chris O'Hara
 * @link        https://github.com/chriso/klein.php
 * @license     MIT
 */

namespace Klein\Tests;

use \Klein\Headers;


// Load our autoloader, and add our Test class namespace
$autoloader = require( __DIR__ . '/../vendor/autoload.php' );
$autoloader->add( 'Klein\Tests', __DIR__ );


class HeadersEcho extends Headers {
	public function header($key, $value = null) {
		echo $this->_header($key, $value) . "\n";
	}
}

class HeadersNoOp extends Headers {
	public function header($key, $value = null) {
		// Do nothing. ;)
	}
}

class HeadersSave extends Headers {
	public $headers_values = array();

	public function __construct( &$headers_array_ref ) {
		$this->headers_values = &$headers_array_ref;
	}

	public function header($key, $value = null) {
		$this->headers_values[] = $this->_header($key, $value) . "\n";

		return $this->headers_values;
	}
}

class TestClass {
	static function GET($r, $r, $a) {
		echo 'ok';
	}
}
