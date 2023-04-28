<?php
declare(strict_types = 1);
namespace App\Repositories\Location;

/**
 *
 * @author Foster Asante <asantefoster22@gmail.com>
 *        
 */
interface AddressInterface
{
    public function createPropertyAddress(Address $property, int $propertyID = null): ?int;

}

