<?php

respond( '/?', function( $request, $response, $app ) {
	echo 'yup';
});

respond( '/testing/?', function( $request, $response, $app ) {
	echo 'yup';
});
