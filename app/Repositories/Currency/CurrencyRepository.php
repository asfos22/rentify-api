<?php
declare(strict_types = 1);
namespace App\Repositories\Currency;

use App\Repositories\Repository;
use App\Repositories\User\User;
use Illuminate\Support\Facades\DB;

use PDO;

/**
 *
 * @author Asante Foster <asantefoster22@gmail.com>
 *        
 */
class CurrencyRepository extends Repository implements CurrencyRepositoryInterface
{


   /**
     * @var
     */
    protected $connection;

    private static $mainQuery = <<<MAINQUERY
    
    c.code AS cCode, c.name AS cName, c.symbol AS cSymbol,
    c.supported AS cSupported, c.updated_at AS cUpdatedAt FROM currencies c 
    

    MAINQUERY;



    public function __construct()
    {
    
    $this->connection = DB::connection()->getPdo();

    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Currency\CurrencyRepositoryInterface::findByCode()
     */
    public function findByCode(string $code): ?Currency
    {
        $selection = self::$mainQuery;

        $query = <<<MAINQUERY
        SELECT $selection 
        
        WHERE c.code = ?, c.supported =? LIMIT 1 OFFSET 0

        MAINQUERY;

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $code, PDO::PARAM_STR);
        $stmt->bindValue(2, 1, PDO::PARAM_INT);

        $types = [];

        if ($stmt->execute()) {

            $types = array_map(function ($model) {

                $currencyMapper  = new CurrencyMapper();
                return $currencyMapper->format($model);
            }, $stmt->fetchAll(PDO::FETCH_OBJ));
        }

        return array_shift($types);

    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Currency\CurrencyRepositoryInterface::fetch()
     */
    public function fetch(int $limit, int $offset): array
    {
    
        $selection = self::$mainQuery;

        $query = <<<MAINQUERY
        SELECT $selection 
        WHERE c.supported =? 
        LIMIT $limit OFFSET $offset

        MAINQUERY;

        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(1, 1, PDO::PARAM_INT);

        $types = [];

        if ($stmt->execute()) {

            $types = array_map(function ($model) {

                $currencyMapper  = new CurrencyMapper();

                return  json_decode(json_encode($currencyMapper->format($model)), true);
                //return $currencyMapper->format($model);
            }, $stmt->fetchAll(PDO::FETCH_OBJ));
        }

        return $types;
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Currency\CurrencyRepositoryInterface::count()
     */
    public function count(): int
    {
       
        $query = "SELECT COUNT(*) AS numCurrencies FROM currencies c";

        $stmt = $this->connection->prepare($query);

        if ($stmt->execute()) {
            $result = $stmt->fetch(PDO::FETCH_OBJ);
            return isset($result->numCurrencies) ? (int) $result->numCurrencies : 0;
        }

        return 0;
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Currency\CurrencyRepositoryInterface::create()
     */
    public function create(Currency $currency): int
    {
        return 0;
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Currency\CurrencyRepositoryInterface::update()
     */
    public function update(int $id, Currency $currency, User $updator): int
    {
        return 0;
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Currency\CurrencyRepositoryInterface::delete()
     */
    public function delete(int $id, User $updator): int
    {
        return 0;
    }
}

