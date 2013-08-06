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


use \Klein\HttpResponseCache;

/**
 * HttpResponseCacheTest
 *
 * @uses AbstractKleinTest
 * @package Klein\Tests
 */
class HttpResponseCacheTest extends AbstractKleinTest
{
    public function testBasicExample()
    {
        $max_age = rand(1, 100);
        $responseCache = new HttpResponseCache();

        $responseCache->setPublic();
        $responseCache->setMaxAge($max_age);
        $responseCache->setSMaxage($max_age);

        $CacheString = $responseCache->generateCacheControlString();
        $this->assertContains('Cache-Control: ', $CacheString);
        $this->assertContains(' public', $CacheString);
        $this->assertContains(' max-age=' . $max_age, $CacheString);
        $this->assertContains(' s-maxage=' . $max_age, $CacheString);

        $this->assertNotContains(' private', $CacheString);
        $this->assertNotContains(' no-cache', $CacheString);
        $this->assertNotContains(' no-store', $CacheString);
    }

    public function testGenerateCacheControlString()
    {
        $responseCache = new HttpResponseCache();


        $CacheString = $responseCache->generateCacheControlString();
        $this->assertContains('Cache-Control: ', $CacheString);
        $this->assertContains(' no-cache', $CacheString);
    }

    public function testPublicGetSet()
    {
        $responseCache = new HttpResponseCache();

        $responseCache->setNoCache();
        $responseCache->setPrivate();
        $responseCache->setPublic();

        $this->assertTrue($responseCache->getPublic());
        $this->assertFalse($responseCache->getPrivate());
        $this->assertFalse($responseCache->getNoCache());

        $CacheString = $responseCache->generateCacheControlString();
        $this->assertContains('Cache-Control: ', $CacheString);
        $this->assertContains(' public', $CacheString);
        $this->assertNotContains(' private', $CacheString);
        $this->assertNotContains(' no-cache', $CacheString);
    }

    public function testPrivateGetSet()
    {
        $responseCache = new HttpResponseCache();

        $responseCache->setPublic();
        $responseCache->setNoCache();
        $responseCache->setPrivate();

        $this->assertTrue($responseCache->getPrivate());
        $this->assertFalse($responseCache->getPublic());
        $this->assertFalse($responseCache->getNoCache());


        $CacheString = $responseCache->generateCacheControlString();
        $this->assertContains('Cache-Control: ', $CacheString);
        $this->assertContains(' private', $CacheString);
        $this->assertNotContains(' public', $CacheString);
        $this->assertNotContains(' no-cache', $CacheString);
    }

    public function testNoCacheGetSet()
    {
        $responseCache = new HttpResponseCache();

        $responseCache->setPublic();
        $responseCache->setPrivate();
        $responseCache->setNoCache();

        $this->assertTrue($responseCache->getNoCache());
        $this->assertFalse($responseCache->getPublic());
        $this->assertFalse($responseCache->getPrivate());


        $CacheString = $responseCache->generateCacheControlString();
        $this->assertContains('Cache-Control: ', $CacheString);
        $this->assertContains(' no-cache', $CacheString);
        $this->assertNotContains(' public', $CacheString);
        $this->assertNotContains(' private', $CacheString);
    }

    /**
     * @dataProvider methodProvider
     */
    public function testOptionsGetSet($setMethod, $getMethod, $cacheString)
    {
        $responseCache = new HttpResponseCache();

        $responseCache->{$setMethod}(true);
        $this->assertTrue($responseCache->{$getMethod}());

        // Test default Cache-Control
        $generatedString = $responseCache->generateCacheControlString();
        $this->assertContains('Cache-Control: ', $generatedString);
        $this->assertContains(' ' . $cacheString, $generatedString);


        $responseCache->{$setMethod}(false);
        $this->assertFalse($responseCache->{$getMethod}());

        $generatedString = $responseCache->generateCacheControlString();
        $this->assertContains('Cache-Control: ', $generatedString);
        $this->assertNotContains(' ' . $cacheString, $generatedString);
    }

    public function methodProvider()
    {
        return array(
            array('setNoStore', 'getNoStore', 'no-store'),
            array('setNoTransform', 'getNoTransform', 'no-transform'),
            array('setMustRevalidate', 'getMustRevalidate', 'must-revalidate'),
            array('setProxyRevalidate', 'getProxyRevalidate', 'proxy-revalidate')
        );
    }

    public function testMaxAgeGetSet()
    {
        $responseCache = new HttpResponseCache();
        $randomAge = rand();

        $responseCache->setMaxAge($randomAge);
        $this->assertEquals($randomAge, $responseCache->getMaxAge());


        $generatedString = $responseCache->generateCacheControlString();
        $this->assertContains('Cache-Control: ', $generatedString);
        $this->assertContains(' max-age=' . $randomAge, $generatedString);


        $newAge = $randomAge - 1;
        $responseCache->setMaxAge($newAge);
        $this->assertEquals($newAge, $responseCache->getMaxAge());

        $generatedString = $responseCache->generateCacheControlString();
        $this->assertContains('Cache-Control: ', $generatedString);
        $this->assertContains(' max-age=' . $newAge, $generatedString);
    }

    public function testSMaxageGetSet()
    {
        $responseCache = new HttpResponseCache();
        $randomAge = rand();

        $responseCache->setSMaxage($randomAge);
        $this->assertEquals($randomAge, $responseCache->getSMaxage());

        // Test default Cache-Control
        $generatedString = $responseCache->generateCacheControlString();
        $this->assertContains('Cache-Control: ', $generatedString);
        $this->assertContains(' s-maxage=' . $randomAge, $generatedString);


        $newAge = $randomAge - 1;
        $responseCache->setSMaxage($newAge);
        $this->assertEquals($newAge, $responseCache->getSMaxage());

        $generatedString = $responseCache->generateCacheControlString();
        $this->assertContains('Cache-Control: ', $generatedString);
        $this->assertContains(' s-maxage=' . $newAge, $generatedString);
    }

    public function testExtensionsGetSet()
    {
        $key = "Extension";
        $value = "Option";

        $responseCache = new HttpResponseCache();

        $responseCache->setExtension($key);
        $this->assertEquals('', $responseCache->getExtension($key));

        $generatedString = $responseCache->generateCacheControlString();
        $this->assertContains('Cache-Control: ', $generatedString);
        $this->assertContains(' ' . $key, $generatedString);
        $this->assertNotContains(' ' . $key . '=', $generatedString);


        $responseCache->setExtension($key, $value);
        $this->assertEquals($value, $responseCache->getExtension($key));

        $generatedString = $responseCache->generateCacheControlString();
        $this->assertContains('Cache-Control: ', $generatedString);
        $this->assertContains(' ' . $key . '=' . $value, $generatedString);
    }
}
