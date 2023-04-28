<?php
declare(strict_types = 1);
namespace App\Repositories\Property\TermsRules;

use App\Repositories\Property\Amenity\Amenity;
use App\Repositories\Property\Amenity\AmenityMapper;
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
class TermsRulesRepository extends Repository implements TermsRulesRepositoryInterface
{


   /**
     * @var
     */
    protected $connection;

    private static $mainQuery = <<<MAINQUERY
    
    htc.id AS Id, htc.name AS Name, htc.desc AS Description,
    htc.created_at AS CreatedAt, htc.updated_at AS UpdatedAt FROM house_terms_rules htc
    
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
        
        WHERE htc.name = ? LIMIT 1 OFFSET 0

        MAINQUERY;

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $name, PDO::PARAM_STR);

        $types = [];

        if ($stmt->execute()) {

            $types = array_map(function ($model) {

                $currencyMapper  = new AmenityMapper();
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

                $amenityMapper  = new AmenityMapper();
                return  json_decode(json_encode($amenityMapper->format($model)), true);
              //  return $amenityMapper->format($model);
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
       
        $query = "SELECT COUNT(*) AS numTermsRules FROM house_terms_rules htc";

        $stmt = $this->connection->prepare($query);

        if ($stmt->execute()) {
            $result = $stmt->fetch(PDO::FETCH_OBJ);
            return isset($result->numTermsRules) ? (int) $result->numTermsRules : 0;
        }

        return 0;
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Amenity\Property\RepositoryInterface::create()
     */
    public function create(Amenity $amenity, int $property)
    {
        $time = (new DateTime())->format('Y-m-d H:i:s');

       // $this->connection->beginTransaction();

        try {

            $query = <<<QUERY
                        
            INSERT INTO  house_optional_terms_rules (house_terms_rules_id,house_id,created_at)
            VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE updated_at = ?
            QUERY;
 
        $stmt = $this->connection->prepare($query);

        foreach ($amenity->getRulesTerms() as $rule) {
            $stmt->bindValue(1, $rule, PDO::PARAM_STR);
            $stmt->bindValue(2, $property, PDO::PARAM_INT);
            $stmt->bindValue(3, $time, PDO::PARAM_STR);
            $stmt->bindValue(4, $time, PDO::PARAM_STR);
            $stmt->execute();
        }

        } catch (Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }


    public function createOptinalRule(Amenity $amenity, int $property)
    {
        $time = (new DateTime())->format('Y-m-d H:i:s');

        try {

            $query = <<<QUERY
            INSERT INTO house_optional_facility_terms_rules (name,term_rule,house_id,created_at)
            VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE updated_at = ?
            QUERY;

        $stmt = $this->connection->prepare($query);

          ///foreach ($amenity as $amenity) {
            $stmt->bindValue(1, $amenity->getName(), PDO::PARAM_STR);
            $stmt->bindValue(2, $amenity->getOptionalRule(), PDO::PARAM_STR);
            $stmt->bindValue(3, $property, PDO::PARAM_INT);
            $stmt->bindValue(4, $time, PDO::PARAM_STR);
            $stmt->bindValue(5, $time, PDO::PARAM_STR);
            $stmt->execute();
          // }

           // return $id;
        } catch (Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }
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

