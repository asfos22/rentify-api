<?php

namespace App\Repositories\GeoCode;

use App\Repositories\Model;

class Location extends Model
{

    /**
     * @var id
     */
    protected $id;

    /**
     * @var
     */
    private $name;
    /**
     * @var
     */

    private $address;
    /**
     * @var
     */

    private $country;
    /**
     * @var
     */

    private $locality;
    /**
     * @var
     */

    private $sub_locality;
    /**
     * @var
     */

    private $latitude;
    /**
     * @var
     */

    private $longitude;


    /**
     * @var
     */
    private $maxDistance;


    /**
     * Returns location id
     *
     * @return int|NULL
     */
    public function getID(): ?int
    {
        return $this->id;
    }

    /**
     * Set location id
     * @param int|null $id
     */
    public function setID(?int $id)
    {
        $this->id = $id;
    }


    /**
     * Returns location name
     *
     * @return string|NULL
     */
    public function getName(): ?String
    {
        return $this->name;
    }

    /**
     * Set location name
     * @param string|null $name
     */
    public function setName(?String $name)
    {
        $this->name = $name;
    }

    /**
     * Returns location address
     *
     * @return string|NULL
     */
    public function getAddress(): ?String
    {
        return $this->address;
    }

    /**
     * Set location address
     * @param string|null $address
     */
    public function setAddress(?String $address)
    {
        $this->address = $address;
    }


    /**
     * Returns location country
     *
     * @return string|NULL
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * Set location country
     * @param string|null $country
     */
    public function setCountry(?String $country)
    {
        $this->country = $country;
    }

    /**
     * Returns location locality
     *
     * @return string|NULL
     */
    public function getLocality(): ?string
    {
        return $this->locality;
    }

    /**
     * Set location locality
     * @param string|null $locality
     */
    public function setLocality(?String $locality)
    {
        $this->locality = $locality;
    }

    /**
     * Returns location sub locality
     *
     * @return string|NULL
     */
    public function getSubLocality(): ?string
    {
        return $this->sub_locality;
    }

    /**
     * Set location  sub locality
     * @param string|null $subLocality
     */
    public function setSubLocality(?String $subLocality)
    {
        $this->sub_locality = $subLocality;
    }


    /**
     * Returns location latitude
     * @return float|null
     */
    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    /**
     * Set location  latitude
     * @param float|null $latitude
     */
    public function setLatitude(?float $latitude)
    {
        $this->latitude = $latitude;
    }

    /**
     * Returns location longitude
     * @return decimal|null
     */
    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    /**
     * Set location  longitude
     * @param float|null $longitude
     */
    public function setLongitude(?float $longitude)
    {
        $this->longitude = $longitude;
    }


    /**
     * Returns location maxDistance
     * @return decimal|null
     */
    public function getMaxDistance(): ?float
    {
        return $this->maxDistance;
    }

    /**
     * Set location  maxDistance
     * @param float|null maxDistance
     */
    public function setMaxDistance(?float $distance)
    {
        $this->maxDistance = $distance;
    }

    /**
     * @return array
     */
    protected function toJson()
    {
        return get_object_vars($this);
    }

}