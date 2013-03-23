<?php

require_once dirname(__FILE__) . '/setup.php';

use \Klein\Klein;

/**
 * AbstractKleinTest 
 * 
 * @uses PHPUnit_Framework_TestCase
 * @abstract
 */
abstract class AbstractKleinTest extends PHPUnit_Framework_TestCase {

	/**
	 * Class properties
	 */
	protected $klein_app;

	protected function setUp() {
		// Create a new klein app,
		// since we need one pretty much everywhere
		$this->klein_app = new Klein();
	}

	protected function assertOutputSame($expected, $callback, $message = '') {
	    ob_start();
	    call_user_func($callback);
	    $out = ob_get_contents();
	    ob_end_clean();
	    $this->assertSame($expected, $out, $message);
	}

	protected function loadExternalRoutes( Klein $app_context = null ) {
		// Did we not pass an instance?
		if ( is_null( $app_context ) ) {
			$app_context = $this->klein_app ?: new Klein();
		}

		$route_directory = __DIR__ . '/routes/';
		$route_files = scandir( $route_directory );
		$route_namespaces = array();

		foreach( $route_files as $file ) {
			if ( is_file( $route_directory . $file ) ) {
				$route_namespace = '/' . basename( $file, '.php' );
				$route_namespaces[] = $route_namespace;

				$app_context->with( $route_namespace, $route_directory . $file );
			}
		}

		return $route_namespaces;
	}

} // End class AbstractKleinTest
