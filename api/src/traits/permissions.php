<?php 

namespace Apify\Traits;

trait Permissions {

    /**
     * Check API key permissions
     *
     * @param string $key API key
     * @return boolean
     */
    private function checkApiKeyActionPermissions( $key, $permission ) {
        
        $stmt = $this->container->pdo->prepare('
            SELECT can_read, can_insert, can_update, can_delete, can_query 
            FROM api_keys 
            WHERE api_key = :key
        ');

        $stmt->execute([ 'key' => $key ]);

        $api = $stmt->fetch();

        if ( ! $api )
            return false;

        switch( $permission ) {
            case 'read':
                return boolval($api->can_read);
            case 'insert':
                return boolval($api->can_insert);
            case 'update':
                return boolval($api->can_update);
            case 'delete':
                return boolval($api->can_delete);
            case 'query':
                return boolval($api->can_query);
            default:
                return false;
        }
    
    }


    /**
     * Check if API key is allowed to handle type
     *
     * @param string $key API key
     * @param string $type Content type
     * @return boolean
     */
    private function checkApiKeyContentTypePermissions( $key, $content_type ) {

        $stmt = $this->container->pdo->prepare('
            SELECT ct.content_type as name
            FROM content_types ct
            JOIN api_keys_permissions akp ON (akp.content_type_id = ct.id)
            JOIN api_keys ak ON (akp.api_key_id = ak.id)
            WHERE ak.api_key = :key 
        ');

        $stmt->execute([ 'key' => $key ]);

        $types = $stmt->fetchAll();

        if ( ! $types )
            return false;

        foreach( $types as $type ) { 

            if ( $type->name == $content_type )
                return true;

        }

        return false;

    }
    
}
