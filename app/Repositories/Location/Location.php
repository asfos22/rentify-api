<?php
declare(strict_types=1);

namespace App\Repositories\Location;

use App\Repositories\Model;

/**
 *
 * @author Foster Asante <asantefoster22@gmail.com>
 *        
 */
class Location extends Model
{

    /**
     *
     * @var string
     */
    private $name;

    /**
     *
     * @var string
     */
    private $longitude;

    /**
     *
     * @var string
     */
    private $latitude;

    /**
     *
     * @return string|NULL
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     *
     * @param string $name
     */
    public function setName(?string $name)
    {
        $this->name = $name;
    }

    /**
     *
     * @return string|NULL
     */
    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    /**
     *
     * @param string $longitude
     */
    public function setLongitude(?string $longitude)
    {
        $this->longitude = $longitude;
    }

    /**
     *
     * @return string|NULL
     */
    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    /**
     *
     * @param string $latitude
     */
    public function setLatitude(?string $latitude)
    {
        $this->latitude = $latitude;
    }

    /**
     *
     * {@inheritDoc}
     * @see \App\Repositories\Model::toJson()
     */
    protected function toJson()
    {
        return get_object_vars($this);
    }
}

