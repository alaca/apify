<?php 

namespace Apify\Api\Content;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Delete {

    use \Apify\Traits\Permissions;
    use \Apify\Traits\Fields;

    private $container;

    public function __construct ($container) 
    {
        $this->container = $container;
    }

    public function __invoke( Request $request, Response $response, $args )
    {
        $params = $request->getParams();

        // Check content type
        $stmt = $this->container->pdo->prepare( 'SELECT id, name FROM content_types WHERE content_type = :type');
        $stmt->execute([
            'type' => $args['type']
        ]);

        $content = $stmt->fetch(); 

        if ( ! $content ) {

            $response = $response->withJson([
                'status'  => 'error',
                'code'    => 403,
                'message' => 'Invalid content type'
            ], 403 );

            return $response;

        }


        // Check API key permissions
        if ( 
            $request->getAttribute('route')->getName() === 'public' 
            && ! $this->checkApiKeyActionPermissions( $params['api_key'], 'delete' ) 
        ) {

            $response = $response->withJson([
                'status'  => 'error',
                'code'    => 401,
                'message' => 'You are not allowed to do that'
            ], 401 );

            return $response;
        }

        // Update data
        try {

            // Remove existing field
            $stmt = $this->container->pdo->prepare('
                DELETE ce, cv
                FROM content_entries ce
                JOIN content_values cv
                ON cv.entry_id = ce.id
                WHERE ce.id = :entry_id
            ');

            $stmt->execute(['entry_id' => $args['id'] ]);

            $response = $response->withJson([
                'status' => 'success',
                'code'   => 201
            ], 201);
    
            
            return $response;
    
        } catch( Exception $e ) {

            $response = $response->withJson([
                'status'  => 'error',
                'code'    => 403,
                'message' => 'Something went wrong'
            ], 403);
    
            
            return $response;

        }

    }


}