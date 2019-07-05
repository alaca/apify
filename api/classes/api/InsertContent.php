<?php 

namespace Apify\Api;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Apify\Traits;

class InsertContent {

    use Traits\ApiKeyPermissions;
    use Traits\RequiredContentFields;

    private $container;

    public function __construct ($container) 
    {
        $this->container = $container;
    }

    public function __invoke( Request $request, Response $response, $args )
    {
        $params = $request->getParams();

        // Check content type
        $stmt = $this->container->pdo->prepare( 'SELECT id, name FROM content WHERE content_type = :type');
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

        // Check fields
        $fields = $this->getRequiredFieldsForContent( $content->id );

        if ( empty( $fields ) ) {

            $response = $response->withJson([
                'status'  => 'error',
                'code'    => 403,
                'message' => sprintf('There is no defined fields for %s yet',  $content->name )
            ], 403 );
    
            return $response;
            
        } else {

            foreach( $fields as $name ) {

                if (
                    ! array_key_exists( $name, $params ) 
                    || empty( $params[$name] )
                ) {
    
                    $response = $response->withJson([
                        'status'  => 'error',
                        'code'    => 403,
                        'message' => sprintf( 'Field %s is required', $name ),
                    ], 403 );
        
                    return $response;
                }
    
            }

        }


        // Check API key insert permissions
        if ( ! $this->checkApiKeyPermissions( $params['api_key'], 'insert' ) ) {

            $response = $response->withJson([
                'status'  => 'error',
                'code'    => 401,
                'message' => 'You are not allowed to do that'
            ], 401 );

            return $response;
        }

        // Insert data
        try {

            foreach( $fields as $id => $field_name ) {

                $stmt = $this->container->pdo->prepare('
                    INSERT INTO content_values ( content_id, field_id, field_value ) 
                    VALUES ( :content_id, :field_id, :field_value )
                ');

                $stmt->execute([
                    'content_id'  => $content->id,
                    'field_id'    => $id,
                    'field_value' => $params[ $field_name ] ?? ''
                ]);

            }

            $response = $response->withJson([
                'status' => 'success',
                'code'   => 201,
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