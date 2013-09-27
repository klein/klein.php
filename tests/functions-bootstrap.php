<?php
// @codingStandardsIgnoreFile

// Really exploiting some functional/global PHP behaviors here. :P
function implement_custom_fastcgi_function() {
	// Check if the function doesn't exist
	if (!function_exists('fastcgi_finish_request')) {
		// Let's just define it then
		function fastcgi_finish_request() {
			ob_start();
			echo 'fastcgi_finish_request';
		}
	}
}
