<?php

use \Klein\Headers;

// require_once dirname(dirname(__FILE__)) . '/klein.php';
require_once __DIR__ . '/../vendor/autoload.php';

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
