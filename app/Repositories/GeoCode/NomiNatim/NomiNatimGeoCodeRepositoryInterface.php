<?php

namespace App\Repositories\GeoCode\NomiNatim;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
use phpDocumentor\Reflection\Types\Integer;

/**
 * @author Foster Asante <asantefoster22@@gmail.com>
 */
interface NomiNatimGeoCodeRepositoryInterface
{
    /**
     * @param Double $lat
     * @param Double $lng
     * @return mixed
     */

    public function postGeoCode(float $lat, float $lng);


}
