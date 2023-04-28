<?php
declare(strict_types = 1);
namespace App\Repositories\Location;

use App\Repositories\Repository;
use PDO;


/**
 *
 * @author Foster Asante <asantefoster22@gmail.com>
 *        
 */
class CountryRepository extends Repository implements CountryRepositoryInterface
{

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Location\CountryRepositoryInterface::fetch()
     */
    public function fetch(int $limit, int $offset): array
    {
       /* $selectables = ! $fields->isEmpty() ? $fields->getItems() : $this->filterer->getFieldMapper()->getSelectableFields();

        $selection = $this->filterer->getSelectionBuilder()->build(...$selectables);
        $filtering = $this->filterer->getFilterCompilter()->compile(...$filters);
        $orderingQuery = $this->filterer->getOrderBuilder()->build(...$ordering);

        $filterQuery = $filtering->getQuery();

        if (! empty($filterQuery)) {
            $filterQuery = 'WHERE ' . $filterQuery;
        }

        if (! empty($orderingQuery)) {
            $orderingQuery = 'ORDER BY ' . $orderingQuery;
        }

        $query = "SELECT $selection FROM countries c $filterQuery $orderingQuery LIMIT $limit OFFSET $offset";

        $stmt = $this->connection->prepare($query);
        
        foreach ($filtering->getParameters() as $index => $value) {
            $stmt->bindValue(1 + $index, $value);
        }

        $countries = [];

        if ($stmt->execute()) {
            $countries = array_map(function ($country) {
                return $this->filterer->getFieldMapper()->format($country);
            }, $stmt->fetchAll(PDO::FETCH_OBJ));
        }

        return $countries;*/
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Location\CountryRepositoryInterface::findCode()
     */
    public function findCode(string $code): ?Country
    {
       
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Location\CountryRepositoryInterface::count()
     */
    public function count(): int
    {
        
        /**$filtering = $this->filterer->getFilterCompilter()->compile(...$filters);
        
        $filterQuery = $filtering->getQuery();

        if (! empty($filterQuery)) {
            $filterQuery = 'WHERE ' . $filterQuery;
        }

        $query = "SELECT COUNT(*) AS numCountries FROM countries c $filterQuery";

        $stmt = $this->connection->prepare($query);

        foreach ($filtering->getParameters() as $index => $value) {
            $stmt->bindValue(1 + $index, $value);
        }

        $numCountries = 0;

        if ($stmt->execute()) {
            $result = $stmt->fetch(PDO::FETCH_OBJ);
         
            $numCountries = isset($result->numCountries) ? (int) $result->numCountries : 0;
        }

        return $numCountries;*/

        return 0;
    }
}

