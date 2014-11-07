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

/**
 * Really exploiting some functional/global PHP behaviors here. :P
 */
function implement_custom_fastcgi_function()
{
    // Check if the function doesn't exist
    if (!function_exists('fastcgi_finish_request')) {
        // Let's just define it then
        function fastcgi_finish_request()
        {
            echo 'fastcgi_finish_request';
        }
    }
}

function implement_custom_apc_cache_functions()
{
    // Check if the function doesn't exist
    if (!function_exists('apc_fetch')) {

        function apc_fetch($key)
        {
            return false;
        }

        function apc_store($key, $value)
        {
            return false;
        }
    }
}

function test_num_args_wrapper($args)
{
    echo func_num_args();
}

function test_response_edit_wrapper($klein)
{
    $klein->response()->body('after callbacks!');
}
