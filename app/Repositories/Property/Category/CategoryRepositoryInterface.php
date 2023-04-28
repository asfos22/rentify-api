<?php
declare(strict_types = 1);
namespace App\Repositories\Property\Category;

use App\Repositories\Amenity\Property\Amenity;
use App\Repositories\User\User;


/**
 *
 * @author Asante Foster <asantefoster22@gmail.com>
 *        
 */
interface CategoryRepositoryInterface
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
     *
     * @param Currency $currency
     * @return int
     */
    public function create(Amenity $amenity): int;

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

