<?php
namespace App\Repositories\Location;
/**
 *
 * @author Foster Asante <asantefoster22@gmail.com>
 *        
 */
interface GeoLocationInterface
{

    /**
     * Returns longitude
     *
     * @return string|NULL
     */
    public function getLongitude(): ?string;

    /**
     * Returns latitude
     *
     * @return string|NULL
     */
    public function getLatitude(): ?string;
}

