<?php
declare(strict_types = 1);
namespace App\Repositories\Property\Category;

use App\Repositories\Amenity\Property\Amenity;
use App\Repositories\Property\Amenity\AmenityMapper;
use App\Repositories\Repository;
use App\Repositories\User\User;
use Illuminate\Support\Facades\DB;
use PDO;

/**
 *
 * @author Asante Foster <asantefoster22@gmail.com>
 *        
 */
class CategoryRepository extends Repository implements CategoryRepositoryInterface
{


   /**
     * @var
     */
    protected $connection;

    /**
     * @var 
     */

    private static $mainQuery = <<<MAINQUERY
    
    c.id AS Id, c.name AS Name, c.desc AS Description,
    c.created_at AS CreatedAt, c.updated_at AS UpdatedAt FROM house_type c
    

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
        
        WHERE c.name = ? LIMIT 1 OFFSET 0

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

                return  json_decode(json_encode($amenityMapper->format($model)), true);//$amenityMapper->format($model);
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
       
        $query = "SELECT COUNT(*) AS numCategories FROM house_type c";

        $stmt = $this->connection->prepare($query);

        if ($stmt->execute()) {
            $result = $stmt->fetch(PDO::FETCH_OBJ);
            return isset($result->numCategories) ? (int) $result->numCategories : 0;
        }

        return 0;
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Amenity\Property\RepositoryInterface::create()
     */
    public function create(Amenity $amenity): int
    {
        return 0;
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

