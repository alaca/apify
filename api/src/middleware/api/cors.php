<?php

namespace Apify\Middleware\Api;

class Cors {

    public function __invoke($request, $response, $next)
    {
        $response = $next($request, $response);

        return $this->addHeaders( $response );

    }

    public function addHeaders( $response ) {
        return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Credentials', 'true')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
    }
}