<?php
declare(strict_types = 1);
namespace App\Repositories\Location;

use App\Repositories\Model;

/**
 *
 * @author Foster Asante <asantefoster22@gmail.com>
 *        
 */
class City extends Model
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
    private $latitude;

    /**
     *
     * @var string
     */
    private $longitude;

    /**
     *
     * @var State
     */
    private $state;

    /**
     * Returns name
     *
     * @return string|NULL
     */
    public function getName(): ?string
    {
        return $this->iso_code;
    }

    /**
     * Sets name
     *
     * @param string $name
     */
    public function setName(?string $name)
    {
        $this->name = $name;
    }

    /**
     * Returns latitude
     *
     * @return string|NULL
     */
    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    /**
     * Sets latitude
     *
     * @param string $latitude
     */
    public function setLatitude(?string $latitude)
    {
        $this->latitude = $latitude;
    }

    /**
     * Returns longitude
     *
     * @return string|NULL
     */
    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    /**
     * Sets longitude
     *
     * @param string $longitude
     */
    public function setLongitude(?string $longitude)
    {
        $this->longitude = $longitude;
    }

    /**
     * Returns state
     *
     * @return State|NULL
     */
    public function getState(): ?State
    {
        return $this->state;
    }

    /**
     * Sets state
     *
     * @param State $state
     */
   /* public function setName(?State $state)
    {
        $this->state = $state;
    }*/
    
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

