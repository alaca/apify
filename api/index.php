<?php

require '../vendor/autoload.php';

$settings = require 'settings.php';

$app = new \Slim\App([
    'settings' => $settings
]);

$container = $app->getContainer();

// PDO
$container['pdo'] = function ($c) {

    $settings = $c->get('settings')['db'];

    $pdo = new PDO('mysql:host=' . $settings['host'] . ';dbname=' . $settings['dbname'], $settings['user'], $settings['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

    return $pdo;
};

// 404 handler
$container['notFoundHandler'] = function () {

    return function ($request, $response) {

        return $response->withJson([
            'status'  => 'error',
            'code'    => 404,
            'message' => 'Route not found'
        ], 404);

    };
};
            

// Public routes
$app->group('/public', function( $route ) {

    $route->get('/get/{type}[/{id}]',     \Apify\Api\GetContent::class);
    $route->post('/insert/{type}',        \Apify\Api\InsertContent::class);
    $route->put('/update/{type}/{id}',    \Apify\Api\Update::class);
    $route->delete('/delete/{type}/{id}', \Apify\Api\Delete::class);

})->add(\Apify\Middleware\Api\PublicAuth::class);

// Apify routes
$app->group('/apify', function( $route ) {

    $route->get('/get/{type}[/{id}]',     \Apify\Api\Get::class);
    $route->put('/update/{type}/{id}',    \Apify\Api\Update::class);
    $route->post('/insert/{type}',        \Apify\Api\Insert::class);
    $route->delete('/delete/{type}/{id}', \Apify\Api\Delete::class);
    $route->post('/add/{type}',           \Apify\Api\AddContent::class);
    $route->put('/edit/{type}',           \Apify\Api\EditContent::class);

} )->add(new Tuupola\Middleware\JwtAuthentication([
    'secret' => $settings['token']
]));


$app->run();