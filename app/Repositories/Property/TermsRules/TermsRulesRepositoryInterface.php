<?php
declare(strict_types = 1);
namespace App\Repositories\Property\TermsRules;

use App\Repositories\Property\Amenity\Amenity;
use App\Repositories\Property\Property;
use App\Repositories\User\User;


/**
 *
 * @author Asante Foster <asantefoster22@gmail.com>
 *        
 */
interface TermsRulesRepositoryInterface
{

    /**
     * Returns number of amenities
     * @return int
     */
    public function count(): int;

    /**
     * Fetches a number of amenities
     *
     * @param int $limit
     * @param int $offset
     * @return Amenities[]
     */
    public function fetch(int $limit, int $offset): array;

    /**
     * Finds a single amenity by Id
     *
     * @param string $name
     * @return Currency|NULL
     */
    public function findByName(string $name): ?Amenity;

    /**
     * Creates a single currency
     * @param Amenities amenity
     * @return int
     */
    public function create(Amenity $amenity, int $property);

    /**
     * @param Amenities amenity
     * int @property 
     */
   
    public function createOptinalRule(Amenity $amenity , int $property);
    

    /**
     * Updates a single existing amenity
     *
     * @param int $id
     * @param Currency $currency
     * @param User $updator
     * @return int
     */
    public function update(int $id, Amenity $amenity, User $updator): int;

    /**
     * Deletes an existing amenity
     *
     * @param int $id
     * @param User $updator
     * @return int
     */
    public function delete(int $id, User $updator): int;
}

