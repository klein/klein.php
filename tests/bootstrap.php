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

// Set a default timezone in case the user has not defined one
date_default_timezone_set('UTC');

// Set some configuration values
ini_set('session.use_cookies', 0);      // Don't send headers when testing sessions
ini_set('session.cache_limiter', '');   // Don't send cache headers when testing sessions

// Load our functions bootstrap
require __DIR__ . '/functions-bootstrap.php';
