<?php

namespace App\Services\GeoCode\NomiNatim;

use App\Repositories\Delivery\AssignmentRepositoryInterface;
use App\Repositories\GeoCode\NomiNatim\NomiNatimGeoCodeRepositoryInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * @author Foster Asante <asantefoster22@Rentifygh.com>
 */
class GeoCodeService implements NomiNatimGeoCodeRepositoryInterface
{


    /**
     * @param float $lat
     * @param float $lng
     * @return mixed|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function postGeoCode(float $lat, float $lng)
    {

        try {


            $client = new Client
            ();
            $data = array(

                "addressdetails=" => 1,
                "q=" => "5.659591,-0.1668977",
                "format=" => "json",
                "format=" => 1
            );


            // --
            $resp = $client->get('https://nominatim.openstreetmap.org/?addressdetails=1&q=' . $lat . ',' . $lng . '&format=json&limit=1',

                array(
                    'headers' => array(

                        'Content-Type' => 'application/json',
                    ),
                    'json' => $data
                ));

            $result = json_decode($resp->getBody()->getContents(), true);


            $respMessage = $result[0]['address'] ?? null;

            return $respMessage;


        } catch (RequestException $e) {

            // Catch all errors

        } catch (\Exception $e) {

        }


    }
}
