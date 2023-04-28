<?php
declare(strict_types = 1);
namespace App\Repositories\Location;
/**
 *
 * @author Foster Asante <asantefoster22@gmail.com>
 *        
 */

class GeoLocation implements GeoLocationInterface
{

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

    public function __construct(string $longitude, string $latitude)
    {
        $this->longitude = $longitude;

        $this->latitude = $latitude;
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Location\GeoLocationInterface::getLongitude()
     */
    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Location\GeoLocationInterface::getLatitude()
     */
    public function getLatitude(): ?string
    {
        return $this->latitude;
    }
}

