<?php 

namespace Apify\Api\Content;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Insert {

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

        // Check fields
        $fields = $this->getFields( $content->id );

        if ( empty( $fields ) ) {

            $response = $response->withJson([
                'status'  => 'error',
                'code'    => 403,
                'message' => sprintf('There is no defined fields for %s yet',  $content->name )
            ], 403 );
    
            return $response;
            
        } else {

            foreach( $fields as $field ) {

                if ( $field->is_required ) {

                    if (
                        ! array_key_exists( $field->name, $params ) 
                        || empty( trim( $params[$field->name] ) )
                    ) {
        
                        $response = $response->withJson([
                            'status'  => 'error',
                            'code'    => 403,
                            'message' => sprintf( 'Field %s is required', $field->name ),
                        ], 403 );
            
                        return $response;
                    }

                }

            }

        }

        // Check API key content type permissions
        if ( ! $this->checkApiKeyContentTypePermissions( $params['api_key'], $args['type'] ) ) {

            $response = $response->withJson([
                'status'  => 'error',
                'code'    => 403,
                'message' => 'Restricted content type'
            ], 403);

            return $response;
        }
        


        // Check API key permissions
        if ( 
            $request->getAttribute('route')->getName() === 'public' 
            && ! $this->checkApiKeyActionPermissions( $params['api_key'], 'insert' ) 
        ) {

            $response = $response->withJson([
                'status'  => 'error',
                'code'    => 401,
                'message' => 'You are not allowed to do that'
            ], 401 );

            return $response;
        }

        // Insert data
        try {

            $stmt = $this->container->pdo->prepare('
                INSERT INTO content_entries ( content_id ) 
                VALUES ( :content_id )
            ');

            $stmt->execute([ 'content_id'  => $content->id ]);

            $entry_id = $this->container->pdo->lastInsertId();

            foreach( $fields as $field ) {

                $stmt = $this->container->pdo->prepare('
                    INSERT INTO content_values ( entry_id, field_id, field_value ) 
                    VALUES ( :entry_id, :field_id, :field_value )
                ');

                $value = isset( $params[ $field->name ] ) 
                    ? $params[ $field->name ] 
                    : '';

                switch( $field->type ) {
                    case 'text':
      
                        break;
                    case 'repeater':

                        break;
                }

                $stmt->execute([
                    'entry_id'    => $entry_id,
                    'field_id'    => $field->id,
                    'field_value' => $value
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