<?php 

namespace Apify\Traits;

trait Fields {

    /**
     * Get required fields for content
     *
     * @param int $id content id
     * @return array
     */
    private function getFields( $id ) {

        $stmt = $this->container->pdo->prepare('
            SELECT id, field_name as name, field_required as is_required, field_type as type
            FROM content_fields 
            WHERE content_id = :content_id
        ');

        $stmt->execute([ 'content_id' => $id ]);

        $data = $stmt->fetchAll();

        if ( ! $data ) 
            return [];

        return $data;

    }
    
}