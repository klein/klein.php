<?php

require_once dirname(__FILE__) . '/AbstractKleinTest.php';

class TestClass {
	static function GET($r, $r, $a) {
		echo 'ok';
	}
}

class RoutesTest extends AbstractKleinTest {

	protected function setUp() {
		parent::setUp();

		$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
	}

	public function testBasic() {
		$this->expectOutputString( 'x' );

		respond( '/', function(){ echo 'x'; });
		respond( '/something', function(){ echo 'y'; });
		dispatch( '/' );
	}

	public function testCallable() {
		$this->expectOutputString( 'okok' );
		respond( '/', array('TestClass', 'GET'));
		respond( '/', 'TestClass::GET');
		dispatch( '/' );
	}

	public function testAppReference() {
		$this->expectOutputString( 'ab' );
		respond( '/', function($r, $r ,$a){ $a->state = 'a'; });
		respond( '/', function($r, $r ,$a){ $a->state .= 'b'; });
		respond( '/', function($r, $r ,$a){ print $a->state; });
		dispatch( '/' );
	}

	public function testCatchallImplicit() {
		$this->expectOutputString( 'b' );

		respond( '/one', function(){ echo 'a'; });
		respond( function(){ echo 'b'; });
		respond( '/two', function(){ } );
		respond( '/three', function(){ echo 'c'; } );
		dispatch( '/two' );
	}

	public function testCatchallAsterisk() {
		$this->expectOutputString( 'b' );

		respond( '/one', function(){ echo 'a'; } );
		respond( '*', function(){ echo 'b'; } );
		respond( '/two', function(){ } );
		respond( '/three', function(){ echo 'c'; } );
		dispatch( '/two' );
	}

	public function testCatchallImplicitTriggers404() {
		$this->expectOutputString("b404\n");

		respond( function(){ echo 'b'; });
		respond( 404, function(){ echo "404\n"; } );
		dispatch( '/' );
	}

	public function testRegex() {
		$this->expectOutputString( 'z' );

		respond( '@/bar', function(){ echo 'z'; });
		dispatch( '/bar' );
	}

	public function testRegexNegate() {
		$this->expectOutputString( "y" );

		respond( '!@/foo', function(){ echo 'y'; });
		dispatch( '/bar' );
	}

	public function test404() {
		$this->expectOutputString("404\n");

		respond( '/', function(){ echo 'a'; } );
		respond( 404, function(){ echo "404\n"; } );
		dispatch( '/foo' );
	}

	public function testParamsBasic() {
		$this->expectOutputString( 'blue' );

		respond( '/[:color]', function($request){ echo $request->param('color'); });
		dispatch( '/blue' );
	}

	public function testParamsIntegerSuccess() {
		$this->expectOutputString( "string(3) \"987\"\n" );

		respond( '/[i:age]', function($request){ var_dump( $request->param('age') ); });
		dispatch( '/987' );
	}

	public function testParamsIntegerFail() {
		$this->expectOutputString( '404 Code' );

		respond( '/[i:age]', function($request){ var_dump( $request->param('age') ); });
		respond( '404', function(){ echo '404 Code'; } );
		dispatch( '/blue' );
	}

	public function testParamsAlphaNum() {
		respond( '/[a:audible]', function($request){ echo $request->param('audible'); });

		$this->assertOutputSame( 'blue42',  function(){ dispatch('/blue42'); });
		$this->assertOutputSame( '',        function(){ dispatch('/texas-29'); });
		$this->assertOutputSame( '',        function(){ dispatch('/texas29!'); });
	}

	public function testParamsHex() {
		respond( '/[h:hexcolor]', function($request){ echo $request->param('hexcolor'); });

		$this->assertOutputSame( '00f',     function(){ dispatch('/00f'); });
		$this->assertOutputSame( 'abc123',  function(){ dispatch('/abc123'); });
		$this->assertOutputSame( '',        function(){ dispatch('/876zih'); });
		$this->assertOutputSame( '',        function(){ dispatch('/00g'); });
		$this->assertOutputSame( '',        function(){ dispatch('/hi23'); });
	}

	public function test404TriggersOnce() {
		$this->expectOutputString( 'd404 Code' );

		respond( function(){ echo "d"; } );
		respond( '404', function(){ echo '404 Code'; } );
		dispatch( '/notroute' );
	}

	public function testMethodCatchAll() {
		$this->expectOutputString( 'yup!123' );

		respond( 'POST', null, function($request){ echo 'yup!'; });
		respond( 'POST', '*', function($request){ echo '1'; });
		respond( 'POST', '/', function($request){ echo '2'; });
		respond( function($request){ echo '3'; });
		dispatch( '/', 'POST' );
	}

	public function testLazyTrailingMatch() {
		$this->expectOutputString( 'this-is-a-title-123' );

		respond( '/posts/[*:title][i:id]', function($request){
			echo $request->param('title')
				. $request->param('id');
		});
		dispatch( '/posts/this-is-a-title-123' );
	}

	public function testFormatMatch() {
		$this->expectOutputString( 'xml' );

		respond( '/output.[xml|json:format]', function($request){
			echo $request->param('format');
		});
		dispatch( '/output.xml' );
	}

	public function testDotSeparator() {
		$this->expectOutputString( 'matchA:slug=ABCD_E--matchB:slug=ABCD_E--' );

		respond('/[*:cpath]/[:slug].[:format]',   function($rq){ echo 'matchA:slug='.$rq->param("slug").'--';});
		respond('/[*:cpath]/[:slug].[:format]?',  function($rq){ echo 'matchB:slug='.$rq->param("slug").'--';});
		respond('/[*:cpath]/[a:slug].[:format]?', function($rq){ echo 'matchC:slug='.$rq->param("slug").'--';});
		dispatch("/category1/categoryX/ABCD_E.php");

		$this->assertOutputSame(
			'matchA:slug=ABCD_E--matchB:slug=ABCD_E--',
			function(){dispatch( '/category1/categoryX/ABCD_E.php' );}
		);
		$this->assertOutputSame(
			'matchB:slug=ABCD_E--',
			function(){dispatch( '/category1/categoryX/ABCD_E' );}
		);
	}

	public function testControllerActionStyleRouteMatch() {
		$this->expectOutputString( 'donkey-kick' );

		respond( '/[:controller]?/[:action]?', function($request){
			echo $request->param('controller')
				. '-' . $request->param('action');
		});
		dispatch( '/donkey/kick' );
	}

	public function testRespondArgumentOrder() {
		$this->expectOutputString( 'abcdef' );

		respond( function(){ echo 'a'; });
		respond( null, function(){ echo 'b'; });
		respond( '/endpoint', function(){ echo 'c'; });
		respond( 'GET', null, function(){ echo 'd'; });
		respond( array( 'GET', 'POST' ), null, function(){ echo 'e'; });
		respond( array( 'GET', 'POST' ), '/endpoint', function(){ echo 'f'; });
		dispatch( '/endpoint' );
	}

	public function testTrailingMatch() {
		respond( '/?[*:trailing]/dog/?', function($request){ echo 'yup'; });

		$this->assertOutputSame( 'yup', function(){ dispatch('/cat/dog'); });
		$this->assertOutputSame( 'yup', function(){ dispatch('/cat/cheese/dog'); });
		$this->assertOutputSame( 'yup', function(){ dispatch('/cat/ball/cheese/dog/'); });
		$this->assertOutputSame( 'yup', function(){ dispatch('/cat/ball/cheese/dog'); });
		$this->assertOutputSame( 'yup', function(){ dispatch('cat/ball/cheese/dog/'); });
		$this->assertOutputSame( 'yup', function(){ dispatch('cat/ball/cheese/dog'); });
	}

	public function testTrailingPossessiveMatch() {
		respond( '/sub-dir/[**:trailing]', function($request){ echo 'yup'; });

		$this->assertOutputSame( 'yup', function(){ dispatch('/sub-dir/dog'); });
		$this->assertOutputSame( 'yup', function(){ dispatch('/sub-dir/cheese/dog'); });
		$this->assertOutputSame( 'yup', function(){ dispatch('/sub-dir/ball/cheese/dog/'); });
		$this->assertOutputSame( 'yup', function(){ dispatch('/sub-dir/ball/cheese/dog'); });
	}

	public function testNSDispatch() {
		with('/u', function () {
			respond('GET', '/?',     function ($request, $response) { echo "slash";   });
			respond('GET', '/[:id]', function ($request, $response) { echo "id"; });
		});
		respond(404, function ($request, $response) { echo "404"; });

		$this->assertOutputSame("slash",          function(){dispatch("/u");});
		$this->assertOutputSame("slash",          function(){dispatch("/u/");});
		$this->assertOutputSame("id",             function(){dispatch("/u/35");});
		$this->assertOutputSame("404",             function(){dispatch("/35");});
	}

	public function testNSDispatchExternal() {
		$ext_namespaces = $this->loadExternalRoutes();

		respond(404, function ($request, $response) { echo "404"; });

		foreach ( $ext_namespaces as $namespace ) {
			$this->assertOutputSame('yup',  function() use ( $namespace ) { dispatch( $namespace . '/' ); });
			$this->assertOutputSame('yup',  function() use ( $namespace ) { dispatch( $namespace . '/testing/' ); });
		}
	}

	public function testNSDispatchExternalRerequired() {
		$ext_namespaces = $this->loadExternalRoutes();

		respond(404, function ($request, $response) { echo "404"; });

		foreach ( $ext_namespaces as $namespace ) {
			$this->assertOutputSame('yup',  function() use ( $namespace ) { dispatch( $namespace . '/' ); });
			$this->assertOutputSame('yup',  function() use ( $namespace ) { dispatch( $namespace . '/testing/' ); });
		}
	}

	public function test405Routes() {
		$resultArray = array();

		$this->expectOutputString( '_' );

		respond( function(){ echo '_'; });
		respond( 'GET', null, function(){ echo 'fail'; });
		respond( array( 'GET', 'POST' ), null, function(){ echo 'fail'; });
		respond( 405, function($a,$b,$c,$d,$methods) use ( &$resultArray ) {
			$resultArray = $methods;
		});
		dispatch( '/sure', 'DELETE' );

		$this->assertCount( 2, $resultArray );
		$this->assertContains( 'GET', $resultArray );
		$this->assertContains( 'POST', $resultArray );
	}

}
