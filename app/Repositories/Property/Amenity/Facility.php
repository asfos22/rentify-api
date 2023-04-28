<?php
declare (strict_types = 1);

namespace App\Repositories\Property\Amenity;

use App\Repositories\Model;

class Facility extends Model
{

    /**
     *
     * @var string
     */
    private $facility_id;

    /**
     *
     * @var string
     */
    private $house_id;

    /**
     * Returns facility id
     *
     * @return string|NULL
     */
    public function getFacilityId(): ?string
    {
        return $this->facility_id;
    }

    /**
     * Sets facility for id
     *
     * @param string $limit
     */
    public function setFacilityId(?string $facility)
    {
        $this->facility_id = $facility;
    }

    /**
     * Returns house id
     *
     * @return string|NULL
     */
    public function getHouseId(): ?string
    {
        return $this->house_id;
    }

    /**
     * Sets house id for property
     *
     * @param string $house_id
     */
    public function setHouseId(?string $house_id)
    {
        $this->house_id = $house_id;
    }

    /**
     *
     * {@inheritdoc}
     * @see \Api\Repositories\Model::toJson()
     */
    protected function toJson()
    {
        return get_object_vars($this);
    }
}
