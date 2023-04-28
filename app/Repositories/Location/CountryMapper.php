<?php
declare(strict_types = 1);
namespace App\Repositories\Location;

use App\Repositories\Model;
use App\Repositories\ModelFormatter;
use stdClass;


/**
 *
 * @author Foster Asante <asantefoster22@gmail.com>
 *        
 */
class CountryMapper extends ModelFormatter
{

    public function __construct()
    {
        /*($fields = array(
            new Field('iso_code', 'c.iso_code', 'cIsoCode'),
            new Field('iso_code_3', 'c.iso_code_3', 'cIsoCode3'),
            new Field('name', 'c.name', 'cName'),
            new Field('phone_code', 'c.phone_code', 'cPhoneCode'),
            new Field('supported', 'c.supported', 'cSupported', true, false, new BoolConverter(), new BoolValidator())
        );*/
        (
            $fields = array()
        );

        //parent::__construct($fields, []);
    }

    /**
     *
     * {@inheritdoc}
     * @see App\Repositories\ModelFormatterInterface::format()
     */
    public function format(?stdClass $model): ?Model
    {
        if (empty($model)) {
            return null;
        }

        $country = new Country();

        $country->setIsoCode(isset($model->cIsoCode) ? $model->cIsoCode : null);
        $country->setIsoCode3(isset($model->cIsoCode3) ? $model->cIsoCode3 : null);
        $country->setName(isset($model->cName) ? $model->cName : null);
        $country->setPhoneCode(isset($model->cPhoneCode) ? $model->cPhoneCode : null);
        $country->setSupported(isset($model->cSupported) ? $this->toBool($model->cSupported) : null);

        return $country;
    }
}

