<?php

namespace App\Repositories\Location;

use App\Models\PropertyFacility;
use App\Repositories\Repository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;
use DateTime;
use Exception;
use PDO;
use stdClass;

class AddressRepository extends Repository implements AddressInterface
{

    protected $connection;

    public function __construct()
    {
        $this->connection = DB::connection()->getPdo();
     }

    /**
     * Create user new property  address [House]
     * @param Request $request
     * @param int|null $propertyID
     * @return Property
     */

    public function createPropertyAddress(Address $address, int $propertyId = null): ?int
    {

     $time = (new DateTime())->format('Y-m-d H:i:s');
        
        try {

            $query = <<<QUERY
            
            INSERT INTO geo_location (
                feature_name,
                address_line,
                country,
                locality,
                sub_locality,
                latitude,
                longitude,
                house_id)

            VALUES (?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE updated_at = ?
            QUERY;

            $stmt = $this->connection->prepare($query);

                $stmt->bindValue(1, $address->getLineOne(), PDO::PARAM_STR);
                $stmt->bindValue(2, $address->getLineTwo(), PDO::PARAM_STR);
                $stmt->bindValue(3, $address->getCountry()->getName(), PDO::PARAM_STR);
                $stmt->bindValue(4, $address->getLocality(), PDO::PARAM_STR);
                $stmt->bindValue(5, $address->getSubLocality(), PDO::PARAM_STR);
                $stmt->bindValue(6, $address->getLatitude(), PDO::PARAM_STR);
                $stmt->bindValue(7, $address->getLongitude(), PDO::PARAM_STR);
                $stmt->bindValue(8, $propertyId, PDO::PARAM_STR);
                $stmt->bindValue(9, $time, PDO::PARAM_STR);

                if ($stmt->execute()) {
                    $id = (int) $this->connection->lastInsertId();
                }

                 return $id;
            

            } catch (Exception $e) {
                $this->connection->rollBack();
                throw $e;
            }

        return 0;
    }


    //-- address

    /**
     * @param Request $request
     * @param int $propertyId
     * @return Address
     */

   /* public function createAddress($array = [],int $propertyId): Address
    {
        $address = new Address();
        $address->feature_name = $array['address_name'] ?? null;
        $address->address_line = $array['address_line'] ?? null;
        $address->country = $array['country'] ?? null;
        $address->locality = $array['locality'] ?? null;
        $address->sub_locality = $array['sub_locality'] ?? null;
        $address->latitude = $array['latitude'] ?? null;
        $address->longitude = $array['longitude'] ?? null;
        $address->house_id = $propertyId;///$request->property_id;
        $address->save();

        return $address;


    }*/

    //-- create property facilities

    /**
     * @param Request $request
     * @param int $propertyId
     * @return PropertyFacility
     */
    /*public function createPropertyFacilities(Request $request, int $propertyId): PropertyFacility
    {
        $propertyFacility = new PropertyFacility();


        $facilities = $request->facility;


        // property facilities be saved
        $facilitiesArray = [];

        // Add to property facilities
        foreach ($facilities as $facility) {
            if (!empty($facility)) {
                // Get the current time
                $now = Carbon::now();

                // Formulate record that will be saved
                $facilitiesArray[] = [
                    'facility_id' => $facility,
                    'house_id' => $propertyId,
                    'updated_at' => $now,  // remove if not using timestamps
                    'created_at' => $now   // remove if not using timestamps
                ];
            }
        }

        // Insert property facilities
        $propertyFacility::insert($facilitiesArray);

        return $propertyFacility;
    }*/
}

    