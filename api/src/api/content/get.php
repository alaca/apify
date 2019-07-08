<?php 

namespace Apify\Api\Content;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Get {

    use \Apify\Traits\Fields;
    use \Apify\Traits\Permissions;

    private $container;

    public function __construct ($container) 
    {
        $this->container = $container;
    }

    public function __invoke( Request $request, Response $response, $args )
    {
        
        $pdo = $this->container->pdo;
        $params = $request->getQueryParams();

        $stmt = $pdo->prepare( 'SELECT id from content_types WHERE content_type = :type');
        $stmt->execute([ 'type' => $args['type'] ]);

        $content = $stmt->fetch();

        if ( ! $content ) {
            
            $response = $response->withJson([
                'status'  => 'error',
                'code'    => 404,
                'message' => sprintf( 'Content type %s does not exist', $args['type'] )
            ], 404);

            return $response;

        }

        // Check API key content type permissions
        if ( ! $this->checkApiKeyContentTypePermissions( $params['api_key'], $args['type'] ) ) {

            $response = $response->withJson([
                'status'  => 'error',
                'code'    => 403,
                'message' => 'You are not allowed to do that'
            ], 403);

            return $response;
        }

        // Check API key action permissions
        if ( 
            $request->getAttribute('route')->getName() === 'public' 
            && ! $this->checkApiKeyActionPermissions( $params['api_key'], 'read' ) 
        ) {

            $response = $response->withJson([
                'status'  => 'error',
                'code'    => 401,
                'message' => 'You are not allowed to do that'
            ], 401 );

            return $response;
        }


        if ( isset( $args['id'] ) ) {

            $stmt = $pdo->prepare('
                SELECT ce.id, cf.field_name, cv.field_value
                FROM content_entries ce
                JOIN content_values cv ON (ce.id = cv.entry_id)
                JOIN content_fields cf ON (cv.field_id = cf.id)
                WHERE ce.content_id = :content_id
                AND ce.id = :entry_id
            ');

            $stmt->execute([
                'content_id' => $content->id,
                'entry_id' => $args['id'] 
            ]);


        } else {

            $stmt = $pdo->prepare('
                SELECT ce.id, cf.field_name, cv.field_value
                FROM content_entries ce
                JOIN content_values cv ON (ce.id = cv.entry_id)
                JOIN content_fields cf ON (cv.field_id = cf.id)
                WHERE ce.content_id = :content_id
                ORDER BY ce.id ASC
            ');

            $stmt->execute(['content_id' => $content->id]);
        }

        $data = $stmt->fetchAll();

        $entries = [];

        foreach( $data as $row ) {
            $entries[$row->id]['id'] = $row->id; 
            $entries[$row->id][$row->field_name] = $row->field_value; 
        }

        $fields = $this->getFields( $content->id );

        $output = [];

        foreach( $entries as $entry ) {

            // Include missing fields into response
            foreach( $fields as $field ) {
                if ( ! array_key_exists( $field->name, $entry ) ) {
                    $entry[$field->name] = '';
                }
            }

            $output[] = $entry;

        }

        $response = $response->withJson([
            'status'  => 'success',
            'code'    => 200,
            'data' => [
                'type' => $args['type'],
                'results' => $output
            ]
        ], 200);

        return $response;

    }

}