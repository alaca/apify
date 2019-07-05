<?php 

namespace Apify\Traits;

trait ApiKeyPermissions {

    /**
     * Check API key permissions
     *
     * @param string $key API key
     * @return boolean
     */
    private function checkApiKeyPermissions( $key, $permission ) {

        $stmt = $this->container->pdo->prepare('
            SELECT permission
            FROM api_keys, api_keys_permissions 
            WHERE api_keys.api_key = :key 
            AND api_keys_permissions.api_key_id = api_keys.id
        ');

        $stmt->execute([ 'key' => $key ]);

        $permissions = $stmt->fetchAll();

        if ( ! $permissions )
            return false;


        foreach( $permissions as $row ) {
            if ( $row->permission == $permission ) {
                return true;
            }
        }

        return false;

    }
    
}
