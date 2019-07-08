<?php 

namespace Apify\Api;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Login {

    private $container;

    public function __construct ($container) 
    {
        $this->container = $container;
    }

    public function __invoke( Request $request, Response $response, $args )
    {
        
        $params = $request->getParams();

        $errors = [];

        if ( ! isset( $params['username'] ) || empty( trim( $params['username'] ) ) ) 
            $errors[] = 'Enter username';
    
        if ( ! isset( $params['password'] ) || empty( trim( $params['password'] ) ) ) 
            $errors[] = 'Enter password';
    

        if ( ! empty( $errors ) ) {

            $response = $response->withJson([
                'status'  => 'error',
                'code'    => 403,
                'message' => $errors
            ], 403 );

            return $response;
        }


        $stmt = $this->container->pdo->prepare('
            SELECT id, name 
            FROM users 
            WHERE username = :username 
            AND password = :password
        ');

        $stmt->execute([
            'username' => $params['username'],
            'password' => $params['password']
        ]);

        $user = $stmt->fetch(); 

        if ( ! $user ) {

            $response = $response->withJson([
                'status'  => 'error',
                'code'    => 403,
                'message' => 'Invalid username or password'
            ], 403 );

            return $response;

        }
        

    }

}