<?php 

namespace Apify\Middleware\Api;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Auth {
    
    private $container;

    public function __construct ($container) 
    {
        $this->container = $container;
    }

    public function __invoke( Request $request, Response $response, $next )
    {

        $pdo    = $this->container->pdo;
        $params = $request->getQueryParams();

        // Check if API key is set
        if ( ! isset( $params['api_key'] ) ) {

            $data = [
                'status'  => 'error',
                'code'    => 400,
                'message' => 'API key is missing'
            ];
            
            return $response->withJson($data, 400);

        }
        
        $stmt = $pdo->prepare( 'SELECT * from api_keys WHERE api_key = :key');
        $stmt->execute([
            'key' => $params['api_key']
        ]);

        $api = $stmt->fetch();

        // API key exists?
        if ( ! $api ) {

            return $response->withJson([
                'status'  => 'error',
                'code'    => 403,
                'message' => 'Invalid API key'
            ], 403);

        }

        // Active API key?
        if ( ! $api->active ) {

            return $response->withJson([
                'status'  => 'error',
                'code'    => 403,
                'message' => 'Inactive API key'
            ], 403);
        }

        // Expired API key?
        if ( ! is_null( $api->date_expire ) ) {

            $expire = new \DateTime( $api->date_expire );
            $current = new \DateTime();

            if ( $current > $expire ) {
                return $response->withJson([
                    'status'  => 'error',
                    'code'    => 403,
                    'message' => 'Expired API key'
                ], 403);
            }

        }

        // Check request domain
        if ( ! is_null( $api->domain ) ) {

            $host = $request->getUri()->getHost();

            $allowed_domains = explode( ',', $api->domain );
            $allowed_domains = array_map( 'trim', $allowed_domains );

            if ( ! in_array( $host, $allowed_domains ) ) {
                return $response->withJson([
                    'status'  => 'error',
                    'code'    => 403,
                    'message' => 'Invalid domain'
                ], 403);
            }

        }

        return $next( $request, $response );
    }

}