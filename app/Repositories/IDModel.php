<?php
namespace App\Repositories;

/**
 * Represents a model that has only and Id and timestamps properties
 *
 * @author Asante Foster
 *        
 */
class IDModel extends Model
{

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Model::toJson()
     */
    protected function toJson()
    {
        return get_object_vars($this);
    }
}

