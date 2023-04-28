<?php
declare(strict_types = 1);
namespace App\Repositories\Property\Amenity;

use App\Repositories\Model;
use App\Repositories\ModelFormatter;


class  AmenityMapper extends ModelFormatter
{

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\ModelFormatterInterface::format()
     */
    public function format(?\stdClass $model): ?Model
    {
    
        if (empty($model)) {
            return null;
        }

        $type = new Amenity();
        
        $type->setId(isset($model->Id) ? (int) $model->Id : null);
        $type->setName(isset($model->Name) ? (string) $model->Name : null);
        $type->setDecription(isset($model->Description) ? $model->Description : null);

        //$type->setCreatedAt($this->createDateTime(empty($model->cCreatedAt) ? null : (string) $model->cCreatedAt));
        //$type->setUpdatedAt($this->createDateTime(empty($model->cUpdatedAt) ? null : (string) $model->cUpdatedAt));

        return  $type;
    }
}

