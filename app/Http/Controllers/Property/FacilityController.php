<?php

namespace App\Http\Controllers\Property;

use App\Constants;
use App\FCM\Push;
use App\Http\AccessControl\AccessManagerInterface;
use App\Http\Exception\InsufficientPrivilegeException;
use App\Http\Resources\ItemResource;
use App\Http\Resources\PropertyHouseResource;
use App\Models\PropertyHouse;
use App\Repositories\Auth\AuthRepositoryInterface;
use App\Repositories\Media\MediaInterface;
use App\Repositories\Permission\Permission;
use App\Repositories\Property\PropertyInterface;
use App\Repositories\Review\ReviewInterface;
use App\Repositories\Session\SessionInterface;
use App\Repositories\Tokens\TokenInterface;

use App\Repositories\GeoCode\NomiNatim\NomiNatimGeoCodeRepositoryInterface;
use Cookie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use function PHPUnit\Framework\isEmpty;
use Validator;
use App\Http\Controllers\Controller;
use App\Repositories\Currency\CurrencyRepositoryInterface;
use App\Repositories\Property\Age\PropertyAgeRepositoryInterface;
use App\Repositories\Property\Amenity\AmenityRepositoryInterface;
use App\Repositories\Property\Category\CategoryRepositoryInterface;
use App\Repositories\Property\Status\PropertyStatusRepositoryInterface;
use App\Repositories\Property\StayDuration\PropertyStayDurationRepositoryInterface;
use App\Repositories\Property\TermsRules\TermsRulesRepositoryInterface;
use Illuminate\Support\Facades\Session;

class FacilityController extends Controller
{

    /**
     * @var PropertyInterface
     */
    private $propertyInterface;

     /**
     * @var AmenityRepositoryInterface 
     */
    private $amenityRepositoryInterface;

    /**
     * @var  CategoryRepositoryInterface 
     */

    private  $categoryRepositoryInterface ;

    /**
     * @var  PropertyAgeRepositoryInterface
     */

    private $propertyStatusRepositoryInterface;

    /**
     * @var  CurrencyRepositoryInterface
     */

    private $currencyRepositoryInterface;

    private $propertyStayDurationRepositoryInterface;

    private $termsRulesRepositoryInterface;

    /**
     * FacilityController constructor.
     * @param PropertyInterface $propertyInterface
     */
    public function __construct(

        PropertyInterface $propertyInterface,
        AmenityRepositoryInterface  $amenityRepositoryInterface,
        CategoryRepositoryInterface  $categoryRepositoryInterface,
        PropertyStatusRepositoryInterface $propertyStatusRepositoryInterface,
        PropertyAgeRepositoryInterface $propertyAgeRepositoryInterface,
        CurrencyRepositoryInterface $currencyRepositoryInterface,
        PropertyStayDurationRepositoryInterface $propertyStayDurationRepositoryInterface,
        TermsRulesRepositoryInterface  $termsRulesRepositoryInterface 
    )
    {
        $this->propertyInterface = $propertyInterface;

        $this->amenityRepositoryInterface = $amenityRepositoryInterface;

        $this->categoryRepositoryInterface = $categoryRepositoryInterface;

        $this->propertyStatusRepositoryInterface =$propertyStatusRepositoryInterface;

        $this->propertyAgeRepositoryInterface =$propertyAgeRepositoryInterface;

        $this->currencyRepositoryInterface = $currencyRepositoryInterface;

        $this->propertyStayDurationRepositoryInterface = $propertyStayDurationRepositoryInterface;

        $this->termsRulesRepositoryInterface = $termsRulesRepositoryInterface;
    }

    /**
     * @param Request $request
     * @return string
     */
    public function getPropertyFacility(Request $request)
    {

         $fetchFacility = $this->amenityRepositoryInterface->fetch($this->amenityRepositoryInterface->count(),0);
        
         $fetchCategory = $this->categoryRepositoryInterface->fetch($this->categoryRepositoryInterface->count(),0);
   
         $fetchPropertyStatus = $this->propertyStatusRepositoryInterface->fetch($this->propertyStatusRepositoryInterface->count(),0);
       
         $fetchAge = $this->propertyAgeRepositoryInterface->fetch($this->propertyAgeRepositoryInterface->count(),0);
        
         $fetchCurrency = $this->currencyRepositoryInterface->fetch( $this->currencyRepositoryInterface->count(),0);

         $fetchPropertyStayDuration = $this->propertyStayDurationRepositoryInterface->fetch($this->propertyStayDurationRepositoryInterface->count(),0);
        
         $fetchPropertyTermsRules = $this->termsRulesRepositoryInterface->fetch($this->termsRulesRepositoryInterface ->count(),0);
    
        return response()->json(
            [
                "code" => 200,
                "message" => "OK",
                "payload" => [

                    "items" => [
                        "facility" => $fetchFacility,
                        "duration" =>  $fetchPropertyStayDuration,
                        "property_age" => $fetchAge,
                        "currencies" => $fetchCurrency,
                        "category" => $fetchCategory,
                        "status" =>  $fetchPropertyStatus,
                        "terms_rules" => $fetchPropertyTermsRules
                    ]]]);

    }


     /**
     * @param Request $request
     * @return facility  rules
     */
    public function getPropertyFacilityRules()
    {

         $fetchPropertyTermsRules = $this->termsRulesRepositoryInterface->fetch($this->termsRulesRepositoryInterface ->count(),0);
    
        return response()->json(
            [
                "code" => 200,
                "message" => "OK",
                "payload" => [
                    "items" =>  $fetchPropertyTermsRules,
                ],]);

    }


}
