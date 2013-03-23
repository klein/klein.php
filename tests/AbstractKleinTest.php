<?php

require_once dirname(__FILE__) . '/setup.php';

/**
 * AbstractKleinTest 
 * 
 * @uses PHPUnit_Framework_TestCase
 * @abstract
 */
abstract class AbstractKleinTest extends PHPUnit_Framework_TestCase {

	protected function setUp() {
		global $__routes;
		$__routes = array();

		global $__namespace;
		$__namespace = null;
	}

	protected function assertOutputSame($expected, $callback, $message = '') {
	    ob_start();
	    call_user_func($callback);
	    $out = ob_get_contents();
	    ob_end_clean();
	    $this->assertSame($expected, $out, $message);
	}

	protected function loadExternalRoutes() {
		$route_directory = __DIR__ . '/routes/';
		$route_files = scandir( $route_directory );
		$route_namespaces = array();

		foreach( $route_files as $file ) {
			if ( is_file( $route_directory . $file ) ) {
				$route_namespace = '/' . basename( $file, '.php' );
				$route_namespaces[] = $route_namespace;

				with( $route_namespace, $route_directory . $file );
			}
		}

		return $route_namespaces;
	}

} // End class AbstractKleinTest
