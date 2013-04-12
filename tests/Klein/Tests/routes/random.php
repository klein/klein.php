<?php

$this->respond(
    '/?',
    function ($request, $response, $app) {
        echo 'yup';
    }
);

$this->respond(
    '/testing/?',
    function ($request, $response, $app) {
        echo 'yup';
    }
);
