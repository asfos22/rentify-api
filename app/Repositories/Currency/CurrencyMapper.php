<?php
declare(strict_types = 1);
namespace App\Repositories\Currency;

use App\Repositories\Model;
use App\Repositories\ModelFormatter;

class CurrencyMapper extends ModelFormatter
{

   
    public function format(?\stdClass $model): ?Model
    {
        if (empty($model)) {
            return null;
        }
        //var_dump($model); exit;
        $type = new Currency();
        $type->setCode(isset($model->cCode) ? (string) $model->cCode : null);
        $type->setName(isset($model->cName) ? (string) $model->cName : null);
        $type->setSymbol(isset($model->cSymbol) ? $model->cSymbol : null);
        $type->setSupported($this->toBool($model->cSupported ?? null));
        $type->setCreatedAt($this->createDateTime(empty($model->cCreatedAt) ? null : (string) $model->cCreatedAt));
        $type->setUpdatedAt($this->createDateTime(empty($model->cUpdatedAt) ? null : (string) $model->cUpdatedAt));

        return $type;
    }
}

