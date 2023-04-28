<?php
declare(strict_types = 1);
namespace App\Repositories\Property\Amenity;

use App\Repositories\Property\Property;
use App\Repositories\User\User;


/**
 *
 * @author Asante Foster <asantefoster22@gmail.com>
 *        
 */
interface AmenityRepositoryInterface
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
     * Creates a amenities [property]
     *
     * @param Property $property
     * @param Amenity amenity
     * @return int //Property $property
     */
    public function create(Amenity $amenity,int $property);


    /**
     * Creates a optional amenities [property]
     * @param Property $property
     * @return int //Property $property
     */
    public function createOptionalFacility(Amenity $amenity, Amenity $amenityRule,int $property);

    /**
     * Creates facilities [property]
     * @param Amenity $amenity
     * @param int $propertyId
     * @return int null
     */
    public function createPropertyFacilities(Amenity $amenity, int $propertyId);

    /**
     * Creates facilities [property]
     * @param int $limit
     * @param int $offset
     * @return fields null
     */
    public function fetchHouseFacility(int $limit, int $offset, $fields): array;


    /**
     * Creates facilities rules [property]
     * @param int $limit
     * @param int $offset
     * @return fields null
     */
    public function fetchHouseFacilityRules(int $limit, int $offset, $fields): array;

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

