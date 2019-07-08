<?php 

namespace Apify\Api;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Firebase\JWT\JWT;

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

        // Generate token
        $secret = $this->container->get('settings')['jwt'];
        $expire = new \DateTime('now +2 hours');
        
        $payload = [ 
            'exp' => $expire->getTimeStamp() 
        ];

        $token = JWT::encode($payload, $secret, 'HS256');

        $response = $response->withJson([
            'status'  => 'success',
            'code'    => 200,
            'token' => $token
        ], 200 );

        return $response;
        

    }

}