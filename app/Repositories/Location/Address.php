<?php
declare(strict_types = 1);
namespace App\Repositories\Location;

use App\Repositories\Model;

class Address extends Model implements GeoLocationInterface
{

    /**
     *
     * @var string
     */
    private $line_one;

    /**
     *
     * @var string
     */
    private $line_two;

    /**
     *
     * @var string
     */
    private $zip_code;

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
     * @var Country
     */
    private $country;


    /**
     *
     * @var string
     */
    private $locality;
    /**
     *
     * @var string
     */
    private $sub_locality;
    
    /**
     *
     * @return string|NULL
     */
    public function getLineOne(): ?string
    {
        return $this->line_one;
    }
    
    /**
     *
     * @param string $line
     */
    public function setLineOne(?string $line)
    {
        $this->line_one = $line;
    }
    
    /**
     *
     * @return string|NULL
     */
    public function getLineTwo(): ?string
    {
        return $this->line_two;
    }
    
    /**
     *
     * @param string $line
     */
    public function setLineTwo(?string $line)
    {
        $this->line_two = $line;
    }
    

    /**
     *
     * @return string|NULL
     */
    public function getZipCode(): ?string
    {
        return $this->zip_code;
    }

    /**
     *
     * @param string $code
     */
    public function setZipCode(?string $code)
    {
        $this->zip_code = $code;
    }

    /**
     *
     * @return Country|NULL
     */
    public function getCountry(): ?Country
    {
        return $this->country;
    }

    /**
     *
     * @param Country $country
     */
    public function setCountry(?Country $country)
    {
        $this->country = $country;
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
     * @return string|NULL
     */
    public function getLocality(): ?string
    {
        return $this->locality;
    }
    
    /**
     *
     * @param string $locality
     */
    public function setLocality(?string $locality)
    {
        $this->locality = $locality;
    }

  /**
     *
     * @return string|NULL
     */
    public function getSubLocality(): ?string
    {
        return $this->sub_locality;
    }
    
    /**
     *
     * @param string $line
     */
    public function setSubLocality(?string $sub_locality)
    {
        $this->sub_locality = $sub_locality;
    }



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

