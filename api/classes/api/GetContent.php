<?php 

namespace Apify\Api;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class GetContent {

    private $container;

    public function __construct ($container) 
    {
        $this->container = $container;
    }

    public function __invoke( Request $request, Response $response, $args )
    {
        
        $pdo = $this->container->pdo;

        if ( isset( $args['id'] ) ) {

            $stmt = $pdo->prepare( 'SELECT * from content WHERE content_type = :type AND id = :id');
            $stmt->execute([
                'id'   => $args['id'],
                'type' => $args['type']
            ]);

            $data = $stmt->fetch();

        } else {

            $stmt = $pdo->prepare( 'SELECT * from content WHERE content_type = :type');
            $stmt->execute([
                'type' => $args['type']
            ]);

            $data = $stmt->fetchAll();

        }

        $response = $response->withJson([
            'status'  => 'success',
            'code'    => 200,
            'data' => $data
        ], 200);

        
        return $response;

    }

}