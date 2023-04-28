<?php

namespace App\Repositories\Property;

use App\Models\Address;
use App\Models\PropertyFacility;
use App\Models\Media;
use App\Models\PropertyHouse;
use App\Repositories\GeoCode\Location;
use App\Repositories\User\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;



interface PropertyInterface
{

    /**
     * Get all properties
     * @return array
     */
    public function fetch(int $limit =50 , int $offset =0): array;

    /**
     * Get all properties by location
     * @param Location $location
     * @param limit
     * @return array
     */
    public function findPropertyByGeoLocation(Location $location, int $limit , int $offset): array;



    /**
     * @param int $Id
     * @return ?Property 
     */
    public function findById(int $Id): ?Property;


    /**
     * @param String $token
     * @return Property
     */
    public function findPropertyByToken(String $token):?Property;


    /**
     *  Create user new property [House]
     * @param Request $request
     * @param int|null $userID
     * @return PropertyHouse
     */
    public function createNewProperty(Property $property, int $userID = null): int;


    /**
     *  Find user property by id [House]
     * @param Request $request
     * @param int|null $userID
     * @return PropertyHouse
     */
    public function findUserByPropertyID(Property $property):? User;


    /**
     * @param Request $request
     * @param int $propertyID
     * @return PropertyFacility
     */

   // public function createPropertyFacilities(Request $request, int $propertyID): PropertyFacility;


    /**
     * Create property terms and rules 
     * @param Request $request
     * @param int $propertyID
     * @return PropertyFacility
     */
    //public function createPropertyOptionalFacilityTermsRules(Request $request, int $propertyID): PropertyFacility;


    /**
     * Create optinal property terms and rules 
     * @param Request $request
     * @param int $propertyID
     * @return PropertyFacility
     */
   // public function createPropertyFacilityTermsRules($array = [], int $propertyID): PropertyFacility;

    /**
     * @param Request $request
     * @return mixed
     */

   // public function findPropertyFacilities(Request $request);

    /**
     * @param Request $request
     * @return mixed
     */

    public function  findPropertyByCategoryId(int $categoryId):? Property;

    /**
     * @param Request $request
     * @return mixed
     */

   // public function findPropertyStayDuration(Request $request);


   /**
     * @param Request $request
     * @return mixed
     */

   // public function findPropertyCategories(Request $request);


    /**
     * @param Request $request
     * @return mixed
     */

  //  public function findPropertyAge(Request $request);

    /**
     * @param Request $request
     * @return mixed
     */

  //  public function findCurrencies(Request $request);

    /**
     * @param Request $request
     * @return mixed
     */
   // public function findPropertyStatus(Request $request);


}
