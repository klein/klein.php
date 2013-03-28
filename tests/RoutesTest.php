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


use \Klein\Klein;


/**
 * RoutesTest 
 * 
 * @uses AbstractKleinTest
 * @package Klein\Tests
 */
class RoutesTest extends AbstractKleinTest {

	protected function setUp() {
		parent::setUp();

		$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
	}

	public function testBasic() {
		$this->expectOutputString( 'x' );

		$this->klein_app->respond( '/', function(){ echo 'x'; });
		$this->klein_app->respond( '/something', function(){ echo 'y'; });
		$this->klein_app->dispatch( '/' );
	}

	public function testCallable() {
		$this->expectOutputString( 'okok' );

		$this->klein_app->respond( '/', array(__NAMESPACE__ . '\TestClass', 'GET'));
		$this->klein_app->respond( '/', __NAMESPACE__ . '\TestClass::GET');
		$this->klein_app->dispatch( '/' );
	}

	public function testAppReference() {
		$this->expectOutputString( 'ab' );

		$this->klein_app->respond( '/', function($r, $r ,$a){ $a->state = 'a'; });
		$this->klein_app->respond( '/', function($r, $r ,$a){ $a->state .= 'b'; });
		$this->klein_app->respond( '/', function($r, $r ,$a){ print $a->state; });
		$this->klein_app->dispatch( '/' );
	}

	public function testCatchallImplicit() {
		$this->expectOutputString( 'b' );

		$this->klein_app->respond( '/one', function(){ echo 'a'; });
		$this->klein_app->respond( function(){ echo 'b'; });
		$this->klein_app->respond( '/two', function(){ } );
		$this->klein_app->respond( '/three', function(){ echo 'c'; } );
		$this->klein_app->dispatch( '/two' );
	}

	public function testCatchallAsterisk() {
		$this->expectOutputString( 'b' );

		$this->klein_app->respond( '/one', function(){ echo 'a'; } );
		$this->klein_app->respond( '*', function(){ echo 'b'; } );
		$this->klein_app->respond( '/two', function(){ } );
		$this->klein_app->respond( '/three', function(){ echo 'c'; } );
		$this->klein_app->dispatch( '/two' );
	}

	public function testCatchallImplicitTriggers404() {
		$this->expectOutputString("b404\n");

		$this->klein_app->respond( function(){ echo 'b'; });
		$this->klein_app->respond( 404, function(){ echo "404\n"; } );
		$this->klein_app->dispatch( '/' );
	}

	public function testRegex() {
		$this->expectOutputString( 'z' );

		$this->klein_app->respond( '@/bar', function(){ echo 'z'; });
		$this->klein_app->dispatch( '/bar' );
	}

	public function testRegexNegate() {
		$this->expectOutputString( "y" );

		$this->klein_app->respond( '!@/foo', function(){ echo 'y'; });
		$this->klein_app->dispatch( '/bar' );
	}

	public function test404() {
		$this->expectOutputString("404\n");

		$this->klein_app->respond( '/', function(){ echo 'a'; } );
		$this->klein_app->respond( 404, function(){ echo "404\n"; } );
		$this->klein_app->dispatch( '/foo' );
	}

	public function testParamsBasic() {
		$this->expectOutputString( 'blue' );

		$this->klein_app->respond( '/[:color]', function($request){ echo $request->param('color'); });
		$this->klein_app->dispatch( '/blue' );
	}

	public function testParamsIntegerSuccess() {
		$this->expectOutputString( "string(3) \"987\"\n" );

		$this->klein_app->respond( '/[i:age]', function($request){ var_dump( $request->param('age') ); });
		$this->klein_app->dispatch( '/987' );
	}

	public function testParamsIntegerFail() {
		$this->expectOutputString( '404 Code' );

		$this->klein_app->respond( '/[i:age]', function($request){ var_dump( $request->param('age') ); });
		$this->klein_app->respond( '404', function(){ echo '404 Code'; } );
		$this->klein_app->dispatch( '/blue' );
	}

	public function testParamsAlphaNum() {
		$this->klein_app->respond( '/[a:audible]', function($request){ echo $request->param('audible'); });

		$this->assertOutputSame( 'blue42',  function() { $this->klein_app->dispatch('/blue42'); });
		$this->assertOutputSame( '',        function() { $this->klein_app->dispatch('/texas-29'); });
		$this->assertOutputSame( '',        function() { $this->klein_app->dispatch('/texas29!'); });
	}

	public function testParamsHex() {
		$this->klein_app->respond( '/[h:hexcolor]', function($request){ echo $request->param('hexcolor'); });

		$this->assertOutputSame( '00f',     function() { $this->klein_app->dispatch('/00f'); });
		$this->assertOutputSame( 'abc123',  function() { $this->klein_app->dispatch('/abc123'); });
		$this->assertOutputSame( '',        function() { $this->klein_app->dispatch('/876zih'); });
		$this->assertOutputSame( '',        function() { $this->klein_app->dispatch('/00g'); });
		$this->assertOutputSame( '',        function() { $this->klein_app->dispatch('/hi23'); });
	}

	public function test404TriggersOnce() {
		$this->expectOutputString( 'd404 Code' );

		$this->klein_app->respond( function(){ echo "d"; } );
		$this->klein_app->respond( '404', function(){ echo '404 Code'; } );
		$this->klein_app->dispatch( '/notroute' );
	}

	public function testMethodCatchAll() {
		$this->expectOutputString( 'yup!123' );

		$this->klein_app->respond( 'POST', null, function($request){ echo 'yup!'; });
		$this->klein_app->respond( 'POST', '*', function($request){ echo '1'; });
		$this->klein_app->respond( 'POST', '/', function($request){ echo '2'; });
		$this->klein_app->respond( function($request){ echo '3'; });
		$this->klein_app->dispatch( '/', 'POST' );
	}

	public function testLazyTrailingMatch() {
		$this->expectOutputString( 'this-is-a-title-123' );

		$this->klein_app->respond( '/posts/[*:title][i:id]', function($request){
			echo $request->param('title')
				. $request->param('id');
		});
		$this->klein_app->dispatch( '/posts/this-is-a-title-123' );
	}

	public function testFormatMatch() {
		$this->expectOutputString( 'xml' );

		$this->klein_app->respond( '/output.[xml|json:format]', function($request){
			echo $request->param('format');
		});
		$this->klein_app->dispatch( '/output.xml' );
	}

	public function testDotSeparator() {
		$this->expectOutputString( 'matchA:slug=ABCD_E--matchB:slug=ABCD_E--' );

		$this->klein_app->respond('/[*:cpath]/[:slug].[:format]',   function($rq){ echo 'matchA:slug='.$rq->param("slug").'--';});
		$this->klein_app->respond('/[*:cpath]/[:slug].[:format]?',  function($rq){ echo 'matchB:slug='.$rq->param("slug").'--';});
		$this->klein_app->respond('/[*:cpath]/[a:slug].[:format]?', function($rq){ echo 'matchC:slug='.$rq->param("slug").'--';});
		$this->klein_app->dispatch("/category1/categoryX/ABCD_E.php");

		$this->assertOutputSame(
			'matchA:slug=ABCD_E--matchB:slug=ABCD_E--',
			function() { $this->klein_app->dispatch( '/category1/categoryX/ABCD_E.php' );}
		);
		$this->assertOutputSame(
			'matchB:slug=ABCD_E--',
			function() { $this->klein_app->dispatch( '/category1/categoryX/ABCD_E' );}
		);
	}

	public function testControllerActionStyleRouteMatch() {
		$this->expectOutputString( 'donkey-kick' );

		$this->klein_app->respond( '/[:controller]?/[:action]?', function($request){
			echo $request->param('controller')
				. '-' . $request->param('action');
		});
		$this->klein_app->dispatch( '/donkey/kick' );
	}

	public function testRespondArgumentOrder() {
		$this->expectOutputString( 'abcdef' );

		$this->klein_app->respond( function(){ echo 'a'; });
		$this->klein_app->respond( null, function(){ echo 'b'; });
		$this->klein_app->respond( '/endpoint', function(){ echo 'c'; });
		$this->klein_app->respond( 'GET', null, function(){ echo 'd'; });
		$this->klein_app->respond( array( 'GET', 'POST' ), null, function(){ echo 'e'; });
		$this->klein_app->respond( array( 'GET', 'POST' ), '/endpoint', function(){ echo 'f'; });
		$this->klein_app->dispatch( '/endpoint' );
	}

	public function testTrailingMatch() {
		$this->klein_app->respond( '/?[*:trailing]/dog/?', function($request){ echo 'yup'; });

		$this->assertOutputSame( 'yup', function() { $this->klein_app->dispatch('/cat/dog'); });
		$this->assertOutputSame( 'yup', function() { $this->klein_app->dispatch('/cat/cheese/dog'); });
		$this->assertOutputSame( 'yup', function() { $this->klein_app->dispatch('/cat/ball/cheese/dog/'); });
		$this->assertOutputSame( 'yup', function() { $this->klein_app->dispatch('/cat/ball/cheese/dog'); });
		$this->assertOutputSame( 'yup', function() { $this->klein_app->dispatch('cat/ball/cheese/dog/'); });
		$this->assertOutputSame( 'yup', function() { $this->klein_app->dispatch('cat/ball/cheese/dog'); });
	}

	public function testTrailingPossessiveMatch() {
		$this->klein_app->respond( '/sub-dir/[**:trailing]', function($request){ echo 'yup'; });

		$this->assertOutputSame( 'yup', function() { $this->klein_app->dispatch('/sub-dir/dog'); });
		$this->assertOutputSame( 'yup', function() { $this->klein_app->dispatch('/sub-dir/cheese/dog'); });
		$this->assertOutputSame( 'yup', function() { $this->klein_app->dispatch('/sub-dir/ball/cheese/dog/'); });
		$this->assertOutputSame( 'yup', function() { $this->klein_app->dispatch('/sub-dir/ball/cheese/dog'); });
	}

	public function testNSDispatch() {
		$this->klein_app->with('/u', function () {
			$this->klein_app->respond('GET', '/?',     function ($request, $response) { echo "slash";   });
			$this->klein_app->respond('GET', '/[:id]', function ($request, $response) { echo "id"; });
		});
		$this->klein_app->respond(404, function ($request, $response) { echo "404"; });

		$this->assertOutputSame("slash",          function() { $this->klein_app->dispatch("/u");});
		$this->assertOutputSame("slash",          function() { $this->klein_app->dispatch("/u/");});
		$this->assertOutputSame("id",             function() { $this->klein_app->dispatch("/u/35");});
		$this->assertOutputSame("404",             function() { $this->klein_app->dispatch("/35");});
	}

	public function testNSDispatchExternal() {
		$ext_namespaces = $this->loadExternalRoutes();

		$this->klein_app->respond(404, function ($request, $response) { echo "404"; });

		foreach ( $ext_namespaces as $namespace ) {
			$this->assertOutputSame('yup',  function() use ( $namespace ) { $this->klein_app->dispatch( $namespace . '/' ); });
			$this->assertOutputSame('yup',  function() use ( $namespace ) { $this->klein_app->dispatch( $namespace . '/testing/' ); });
		}
	}

	public function testNSDispatchExternalRerequired() {
		$ext_namespaces = $this->loadExternalRoutes();

		$this->klein_app->respond(404, function ($request, $response) { echo "404"; });

		foreach ( $ext_namespaces as $namespace ) {
			$this->assertOutputSame('yup',  function() use ( $namespace ) { $this->klein_app->dispatch( $namespace . '/' ); });
			$this->assertOutputSame('yup',  function() use ( $namespace ) { $this->klein_app->dispatch( $namespace . '/testing/' ); });
		}
	}

	public function test405Routes() {
		$resultArray = array();

		$this->expectOutputString( '_' );

		$this->klein_app->respond( function(){ echo '_'; });
		$this->klein_app->respond( 'GET', null, function(){ echo 'fail'; });
		$this->klein_app->respond( array( 'GET', 'POST' ), null, function(){ echo 'fail'; });
		$this->klein_app->respond( 405, function($a,$b,$c,$d,$methods) use ( &$resultArray ) {
			$resultArray = $methods;
		});
		$this->klein_app->dispatch( '/sure', 'DELETE' );

		$this->assertCount( 2, $resultArray );
		$this->assertContains( 'GET', $resultArray );
		$this->assertContains( 'POST', $resultArray );
	}

} // End class RoutesTest
