<?php
/**
 * Klein (klein.php) - A fast & flexible router for PHP
 *
 * @author      Chris O'Hara <cohara87@gmail.com>
 * @author      Trevor Suarez (Rican7) (contributor and v2 refactorer)
 * @copyright   (c) Chris O'Hara
 * @link        https://github.com/chriso/klein.php
 * @license     MIT
 */

namespace Klein\Tests;

use Klein\Request;
use Klein\Tests\Mocks\MockRequestFactory;

/**
 * RequestTest
 */
class RequestTest extends AbstractKleinTest
{

    public function testConstructorAndGetters()
    {
        // Test data
        $params_get  = array('get');
        $params_post = array('post');
        $cookies     = array('cookies');
        $server      = array('server');
        $files       = array('files');
        $body        = 'body';

        // Create the request
        $request = new Request(
            $params_get,
            $params_post,
            $cookies,
            $server,
            $files,
            $body
        );

        // Make sure our data's the same
        $this->assertSame($params_get, $request->paramsGet()->all());
        $this->assertSame($params_post, $request->paramsPost()->all());
        $this->assertSame($cookies, $request->cookies()->all());
        $this->assertSame($server, $request->server()->all());
        $this->assertSame($files, $request->files()->all());
        $this->assertSame($body, $request->body());
    }

    public function testGlobalsCreation()
    {
        // Create a unique key
        $key = uniqid();

        // Test data
        $_GET       = array_merge($_GET, array($key => 'get'));
        $_POST      = array_merge($_POST, array($key => 'post'));
        $_COOKIE    = array_merge($_COOKIE, array($key => 'cookies'));
        $_SERVER    = array_merge($_SERVER, array($key => 'server'));
        $_FILES     = array_merge($_FILES, array($key => 'files'));

        // Create the request
        $request = Request::createFromGlobals();

        // Make sure our data's the same
        $this->assertSame($_GET[$key], $request->paramsGet()->get($key));
        $this->assertSame($_POST[$key], $request->paramsPost()->get($key));
        $this->assertSame($_COOKIE[$key], $request->cookies()->get($key));
        $this->assertSame($_SERVER[$key], $request->server()->get($key));
        $this->assertSame($_FILES[$key], $request->files()->get($key));
    }

    public function testUniversalParams()
    {
        // Test data
        $params_get  = array('page' => 2, 'per_page' => 10, 'num' => 1, 5 => 'ok', 'empty' => null, 'blank' => '');
        $params_post = array('first_name' => 'Trevor', 'last_name' => 'Suarez', 'num' => 2, 3 => 'hmm', 4 => 'thing');
        $cookies     = array('user' => 'Rican7', 'PHPSESSID' => 'randomstring', 'num' => 3, 4 => 'dog');
        $named       = array('id' => '1f8ae', 'num' => 4);

        // Create the request
        $request = new Request(
            $params_get,
            $params_post,
            $cookies
        );

        // Set our named params
        $request->paramsNamed()->replace($named);

        // Merge our params for our expected results
        $params = array_merge($params_get, $params_post, $cookies, $named);

        $this->assertSame($params, $request->params());
        $this->assertSame($params['num'], $request->param('num'));
        $this->assertSame(null, $request->param('thisdoesntexist'));
    }

    public function testUniversalParamsWithFilter()
    {
        // Test data
        $params_get  = array('page' => 2, 'per_page' => 10, 'num' => 1, 5 => 'ok', 'empty' => null, 'blank' => '');
        $params_post = array('first_name' => 'Trevor', 'last_name' => 'Suarez', 'num' => 2, 3 => 'hmm', 4 => 'thing');
        $cookies     = array('user' => 'Rican7', 'PHPSESSID' => 'randomstring', 'num' => 3, 4 => 'dog');

        // Create our filter and expected results
        $filter      = array('page', 'user', 'num', 'this-key-never-showed-up-anywhere');
        $expected    = array('page' => 2, 'user' => 'Rican7', 'num' => 3, 'this-key-never-showed-up-anywhere' => null);

        // Create the request
        $request = new Request(
            $params_get,
            $params_post,
            $cookies
        );

        $this->assertSame($expected, $request->params($filter));
    }

    public function testMagic()
    {
        // Test data
        $params = array('page' => 2, 'per_page' => 10, 'num' => 1);

        // Create the request
        $request = new Request($params);

        // Test Exists
        $this->assertTrue(isset($request->per_page));

        // Test Getter
        $this->assertSame($params['per_page'], $request->per_page);

        // Test Setter
        $this->assertSame($request->test = '#yup', $request->param('test'));

        // Test Unsetter
        unset($request->test);
        $this->assertNull($request->param('test'));
    }

    public function testSecure()
    {
        $request = new Request();
        $request->server()->set('HTTPS', true);

        $this->assertTrue($request->isSecure());
    }

    public function testIp()
    {
        // Test data
        $ip = '127.0.0.1';

        $request = new Request();
        $request->server()->set('REMOTE_ADDR', $ip);

        $this->assertSame($ip, $request->ip());
    }

    public function testUserAgent()
    {
        // Test data
        $user_agent = 'phpunittt';

        $request = new Request();
        $request->headers()->set('USER_AGENT', $user_agent);

        $this->assertSame($user_agent, $request->userAgent());
    }

    public function testUri()
    {
        // Test data
        $uri = 'localhostofthingsandstuff';
        $query = '?q=search';

        $request = new Request();
        $request->server()->set('REQUEST_URI', $uri.$query);

        $this->assertSame($uri.$query, $request->uri());
    }

    public function testPathname()
    {
        // Test data
        $uri = 'localhostofthingsandstuff';
        $query = '?q=search';

        $request = new Request();
        $request->server()->set('REQUEST_URI', $uri.$query);

        $this->assertSame($uri, $request->pathname());
    }

    public function testBody()
    {
        // Test data
        $body = '_why is an interesting guy<br> - Trevor';

        // Blank constructor
        $request = new Request();

        $this->assertEmpty($request->body());

        // In constructor
        $request = new Request(array(), array(), array(), array(), array(), $body);

        $this->assertSame($body, $request->body());
    }

    public function testMethod()
    {
        // Test data
        $method = 'PATCH';

        $request = new Request();
        $request->server()->set('REQUEST_METHOD', $method);

        $this->assertSame($method, $request->method());
        $this->assertTrue($request->method($method));
        $this->assertTrue($request->method(strtolower($method)));
    }

    public function testMethodOverride()
    {
        // Test data
        $method                 = 'POST';
        $override_method        = 'TRACE';
        $weird_override_method  = 'DELETE';

        $request = new Request();
        $request->server()->set('REQUEST_METHOD', $method);
        $request->server()->set('X_HTTP_METHOD_OVERRIDE', $override_method);

        $this->assertSame($override_method, $request->method());
        $this->assertTrue($request->method($override_method));
        $this->assertTrue($request->method(strtolower($override_method)));

        $request->server()->remove('X_HTTP_METHOD_OVERRIDE');
        $request->paramsPost()->set('_method', $weird_override_method);

        $this->assertSame($weird_override_method, $request->method());
        $this->assertTrue($request->method($weird_override_method));
        $this->assertTrue($request->method(strtolower($weird_override_method)));
    }

    public function testQueryModify()
    {
        $test_uri = '/test?query';
        $query_string = 'search=string&page=2&per_page=3';
        $test_one = '';
        $test_two = '';
        $test_three = '';

        $request = new Request();
        $request->server()->set('REQUEST_URI', $test_uri);
        $request->server()->set('QUERY_STRING', $query_string);

        $this->klein_app->respond(
            function ($request, $response, $service) use (&$test_one, &$test_two, &$test_three) {
                // Add a new var
                $test_one = $request->query('test', 'dog');

                // Modify a current var
                $test_two = $request->query('page', 7);

                // Modify a current var
                $test_three = $request->query(array('per_page' => 10));
            }
        );

        $this->klein_app->dispatch($request);

        $expected_uri = parse_url($this->klein_app->request()->uri(), PHP_URL_PATH);

        $this->assertSame(
            $expected_uri . '?' . $query_string . '&test=dog',
            $test_one
        );

        $this->assertSame(
            $expected_uri . '?' . str_replace('page=2', 'page=7', $query_string),
            $test_two
        );

        $this->assertSame(
            $expected_uri . '?' . str_replace('per_page=3', 'per_page=10', $query_string),
            $test_three
        );
    }

    public function testId()
    {
        // Create two requests
        $request_one = new Request();
        $request_two = new Request();

        // Make sure the ID's aren't null
        $this->assertNotNull($request_one->id());
        $this->assertNotNull($request_two->id());

        // Make sure that multiple calls yield the same result
        $this->assertSame($request_one->id(), $request_one->id());
        $this->assertSame($request_one->id(), $request_one->id());
        $this->assertSame($request_two->id(), $request_two->id());
        $this->assertSame($request_two->id(), $request_two->id());

        // Make sure the ID's are unique to each request
        $this->assertNotSame($request_one->id(), $request_two->id());
    }

    public function testMockFactory()
    {
        // Test data
        $uri         = '/test/uri';
        $method      = 'OPTIONS';
        $params      = array('get');
        $cookies     = array('cookies');
        $server      = array('server');
        $files       = array('files');
        $body        = 'body';

        // Create the request
        $request = MockRequestFactory::create(
            $uri,
            $method,
            $params,
            $cookies,
            $server,
            $files,
            $body
        );

        // Make sure our data's the same
        $this->assertSame($uri, $request->uri());
        $this->assertSame($method, $request->method());
        $this->assertSame($params, $request->paramsGet()->all());

        $this->assertSame(array(), $request->paramsPost()->all());
        $this->assertSame(array(), $request->paramsNamed()->all());
        $this->assertSame($cookies, $request->cookies()->all());
        $this->assertContains($cookies[0], $request->params());
        $this->assertContains($server[0], $request->server()->all());
        $this->assertSame($files, $request->files()->all());
        $this->assertSame($body, $request->body());
    }
}
