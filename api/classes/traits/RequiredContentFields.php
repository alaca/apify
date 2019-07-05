<?php 

namespace Apify\Traits;

trait RequiredContentFields {

    /**
     * Get required fields for content
     *
     * @param int $id content id
     * @return array
     */
    private function getRequiredFieldsForContent( $id ) {

        $stmt = $this->container->pdo->prepare('
            SELECT id, field_name 
            FROM content_fields 
            WHERE field_required = "1" 
            AND content_id = :content_id
        ');

        $stmt->execute([ 'content_id' => $id ]);

        $data = $stmt->fetchAll();

        if ( ! $data ) 
            return [];

        $fields = [];

        foreach( $data as $field ) {
            $fields[$field->id] = $field->field_name;
        }

        return $fields;

    }
    
}