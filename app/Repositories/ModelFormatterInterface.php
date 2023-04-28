<?php
declare(strict_types = 1);
namespace App\Repositories;

/**
 *
 * @author Foster Asante <asantefoster22@gmail.com>
 *        
 */
interface ModelFormatterInterface //extends IFieldMapper
{

    /**
     * Formats a given object into a Model instance
     *
     * @param \stdClass $model
     * @return Model|NULL
     */   
    public function format(?\stdClass $model);
}

