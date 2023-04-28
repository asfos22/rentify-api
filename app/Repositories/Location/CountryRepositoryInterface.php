<?php
declare(strict_types = 1);
namespace App\Repositories\Location;

/**
 *
 * @author Foster Asante <asantefoster22@gmail.com>
 *        
 */
interface CountryRepositoryInterface
{

    /**
     * Returns number of countries matching given filters
     *
     * @param FilterCollection $filters
     */
    public function count(): int;
    
    /**
     * Fetches a number of countries
     *
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function fetch(int $limit, int $offset): array;

    /**
     * Fetches a single country by code
     *
     * @param string $code
     * @param FieldCollection $fields
     * @return Country | null
     */
    public function findCode(string $code): ?Country;
}

