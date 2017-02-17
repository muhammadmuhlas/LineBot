<?php

/*Loading Required Files*/
require __DIR__ . '/../lib/vendor/autoload.php';
require "response.php";

/* Boot Up Apps*/

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$configs =  [
	'settings' => [
        'displayErrorDetails' => true
        ]
    ];
$app = new Slim\App($configs);

/*
| Routes
| Define Routes Here
*/
$app->get('/', function ($request, $response) {

    echo "Hello World";

});

$app->post('/', function ($request, $response) {

    $response = new Response;
    $handler = $response->eventsHandler();

    return $handler;
});

$app->run();
