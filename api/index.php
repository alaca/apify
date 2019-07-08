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

        $cors = new \Apify\Middleware\Api\Cors();
        
        return $cors->addHeaders($response)
            ->withJson([
                'status'  => 'error',
                'code'    => 404,
                'message' => 'Route not found'
            ], 404);
    };
};

// Error handler
$container['errorHandler'] = function ($c) {
    return function ($request, $response, $exception) use ($c) {

        $cors = new \Apify\Middleware\Api\Cors();

        return $cors->addHeaders($response)
            ->withJson([
                'status'  => 'error',
                'code'    => 500,
                'message' => $c->get('settings')['displayErrorDetails'] ? $exception->getMessage() : 'Something went wrong'
            ], 500);
    };
};
         

// Public routes
$app->group('/public', function( $route ) {

    // Get
    $route->get('/get/{type}[/{id}]', \Apify\Api\Content\Get::class)
        ->setName('public');

    // Update
    $route->put('/update/{type}/{id}', \Apify\Api\Content\Update::class)
        ->setName('public');

    // Insert
    $route->post('/insert/{type}', \Apify\Api\Content\Insert::class)
        ->setName('public');

    // Delete
    $route->delete('/delete/{type}/{id}', \Apify\Api\Content\Delete::class)
        ->setName('public');

    // Query
    $route->post('/query', \Apify\Api\Query::class)
        ->setName('public');

})->add(\Apify\Middleware\Api\Auth::class);

// Apify routes
$app->group('/apify', function( $route ) {

    $route->get('/get/{type}[/{id}]',     \Apify\Api\Content\Get::class);
    $route->put('/update/{type}/{id}',    \Apify\Api\Content\Update::class);
    $route->post('/insert/{type}',        \Apify\Api\Content\Insert::class);
    $route->delete('/delete/{type}/{id}', \Apify\Api\Content\Delete::class);

    $route->post('/content/add',             \Apify\Api\AddContentType::class);
    $route->put('/content/edit/{id}',        \Apify\Api\AddContentType::class);
    $route->post('/content/add/{type}',      \Apify\Api\AddContent::class);
    $route->put('/content/edit/{type}/{id}', \Apify\Api\EditContent::class);

} )->add(new Tuupola\Middleware\JwtAuthentication([
    'secret' => $settings['jwt']
]));

// User auth
$app->post('/login',  \Apify\Api\Login::class);


// Cors
$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

$app->add(\Apify\Middleware\Api\Cors::class);


$app->run();