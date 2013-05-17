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

// Load our autoloader, and add our Test class namespace
$autoloader = require(__DIR__ . '/../vendor/autoload.php');
$autoloader->add('Klein\Tests', __DIR__);
