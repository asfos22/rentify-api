<?php
declare (strict_types = 1);
namespace App\Repositories\Property\Amenity;

use App\Repositories\Location\Address;
use App\Repositories\Property\Property;
use App\Repositories\Repository;
use App\Repositories\User\User;
use DateTime;
use Exception;
use Illuminate\Support\Facades\DB;
use PDO;

/**
 *
 * @author Asante Foster <asantefoster22@gmail.com>
 *
 */
class AmenityRepository extends Repository implements AmenityRepositoryInterface
{

    /**
     * @var
     */
    protected $connection;

    /**
     * @var
     */

    private static $mainQuery = <<<MAINQUERY

    f.id AS Id, f.name AS Name, f.desc AS Description,
    f.created_at AS CreatedAt, f.updated_at AS UpdatedAt FROM facility f

    MAINQUERY;
    /**
     * @var
     */

    private static $termRulesMainQuery = <<<MAINQUERY

    htr.id AS Id, htr.name AS Name, htr.desc AS Description,
    htr.created_at AS CreatedAt, htr.updated_at AS UpdatedAt FROM house_terms_rules htr

    MAINQUERY;
    

    public function __construct()
    {

        $this->connection = DB::connection()->getPdo();

    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Amenity\Property\RepositoryInterface::findByCode()
     */
    public function findByName(string $name): ?Amenity
    {
        $selection = self::$mainQuery;

        $query = <<<MAINQUERY
        SELECT $selection

        WHERE f.name = ? LIMIT 1 OFFSET 0

        MAINQUERY;

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $name, PDO::PARAM_STR);

        $types = [];

        if ($stmt->execute()) {

            $types = array_map(function ($model) {

                $currencyMapper = new AmenityMapper();
                return $currencyMapper->format($model);
            }, $stmt->fetchAll(PDO::FETCH_OBJ));
        }

        return array_shift($types);

    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Amenity\Property\RepositoryInterface::fetch()
     */
    public function fetch(int $limit, int $offset): array
    {

        $selection = self::$mainQuery;

        $query = <<<MAINQUERY
        SELECT $selection

        LIMIT $limit OFFSET $offset

        MAINQUERY;

        $stmt = $this->connection->prepare($query);

        $types = [];

        if ($stmt->execute()) {

            $types = array_map(function ($model) {

                $amenityMapper = new AmenityMapper();

                return json_decode(json_encode($amenityMapper->format($model)), true); //$amenityMapper->format($model);
            }, $stmt->fetchAll(PDO::FETCH_OBJ));
        }

        return $types;
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Amenity\Property\RepositoryInterface::count()
     */
    public function count(): int
    {

        $query = "SELECT COUNT(*) AS numAmenities FROM facility f";

        $stmt = $this->connection->prepare($query);

        if ($stmt->execute()) {
            $result = $stmt->fetch(PDO::FETCH_OBJ);
            return isset($result->numAmenities) ? (int) $result->numAmenities : 0;
        }

        return 0;
    }

    /**
     * @param Amenity amenity
     *
     * {@inheritdoc}
     * @see \App\Repositories\Amenity\Property\RepositoryInterface::create()
     */
    public function create(Amenity $amenity, int $property)
    {

        $time = (new DateTime())->format('Y-m-d H:i:s');
        // Add to property facilities

        try {

            $query = <<<QUERY

            INSERT INTO house_facilities (facility_id,house_id,created_at)
            VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE updated_at = ?
            QUERY;

            $stmt = $this->connection->prepare($query);

            foreach ($amenity->getAmenities() as $facility) {

                if (!empty($facility)) {

                    $stmt->bindValue(1, $facility, PDO::PARAM_INT);
                    $stmt->bindValue(2, $property, PDO::PARAM_INT);
                    $stmt->bindValue(3, $time, PDO::PARAM_STR);
                    $stmt->bindValue(4, $time, PDO::PARAM_STR);
                    $stmt->execute();

                }
            }

        } catch (Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Amenity\Property\RepositoryInterface::createOptionalFacilityTermsRules()
     *
     * @param Amenity amenity
     */
    public function createOptionalFacility(Amenity $amenity, Amenity $amenityRule, int $property)
    {

        $time = (new DateTime())->format('Y-m-d H:i:s');

        try {

            $query = <<<QUERY
            INSERT INTO house_optional_facility_terms_rules (name,term_rule,house_id,created_at)
            VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE updated_at = ?
            QUERY;

            $stmt = $this->connection->prepare($query);

            $stmt->bindValue(1, $amenity->getName(), PDO::PARAM_STR);
            $stmt->bindValue(2, $amenityRule->getName(), PDO::PARAM_STR);
            $stmt->bindValue(3, $property, PDO::PARAM_INT);
            $stmt->bindValue(4, $time, PDO::PARAM_STR);
            $stmt->bindValue(5, $time, PDO::PARAM_STR);
            $stmt->execute();

        } catch (Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }


    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Amenity\Property\RepositoryInterface::createOptionalFacilityTermsRules()
     *
     * @param Amenity $amenity
     * @param int $propertyId
     */
    public function createPropertyFacilities(Amenity $amenity, int $propertyId)
    {

         $time = (new DateTime())->format('Y-m-d H:i:s');

        try {

            $query = <<<FACILITY_QUERY
            INSERT INTO house_facilities (facility_id,house_id,created_at) 
            VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE updated_at = ?;

            FACILITY_QUERY;

            $stmt = $this->connection->prepare($query);

            foreach ($amenity->getFacility() as $facility) {
                $stmt->bindValue(1, $facility->getFacilityId(), PDO::PARAM_STR);
                $stmt->bindValue(2, $facility->getHouseId(), PDO::PARAM_STR);
                $stmt->bindValue(3, $time, PDO::PARAM_STR);
                $stmt->bindValue(4, $time, PDO::PARAM_STR);

                $stmt->execute();
            }

            $this->connection->commit();


        } catch (Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }


    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Amenity\AmenityRepositoryInterface::fetchHouseFacility()
     */
    public function fetchHouseFacility(int $limit, int $offset, $fields): array
    {
       
       
        $selection = self::$mainQuery;

        $query = <<<MAINQUERY
        SELECT $selection  $fields
        
        LIMIT $limit OFFSET $offset

        MAINQUERY;
         
        $stmt = $this->connection->prepare($query);

    
        $items = [];

        if ($stmt->execute()) {

            $items = array_map(function ($model) {
                $amenityMapper = new AmenityMapper (); 
                return  $amenityMapper->format($model);
            }, $stmt->fetchAll(PDO::FETCH_OBJ));
        }

        return $items;
    }

    

     /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Amenity\AmenityRepositoryInterface::fetchHouseFacility()
     */
    public function fetchHouseFacilityRules(int $limit, int $offset, $fields): array
    {
    
        $selection = self::$termRulesMainQuery;

        $query = <<<MAINQUERY
        SELECT $selection  $fields
        
        LIMIT $limit OFFSET $offset

        MAINQUERY;
         
        $stmt = $this->connection->prepare($query);

    
        $items = [];

        if ($stmt->execute()) {

            $items = array_map(function ($model) {
                $amenityMapper = new AmenityMapper (); 
                return  $amenityMapper->format($model);
            }, $stmt->fetchAll(PDO::FETCH_OBJ));
        }

        return $items;
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Amenity\Property\RepositoryInterface::update()
     */
    public function update(int $id, Amenity $amenity, User $updator): int
    {
        return 0;
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Amenity\Property\RepositoryInterface::delete()
     */
    public function delete(int $id, User $updator): int
    {
        return 0;
    }

}

