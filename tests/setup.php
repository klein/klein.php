<?php

include dirname(dirname(__FILE__)) . '/klein.php';

class HeadersEcho extends Klein\_Headers {
	public function header($key, $value = null) {
		echo $this->_header($key, $value) . "\n";
	}
}

Klein\_Request::$_headers = Klein\_Response::$_headers = new HeadersEcho;
