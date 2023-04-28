<?php

namespace App\Http\Controllers;

use App\Http\AccessControl\AccessManagerInterface;
use App\Http\Exception\InsufficientPrivilegeException;
use App\Repositories\Auth\AuthRepositoryInterface;
use App\Repositories\Currency\Currency;
use App\Repositories\Currency\Money;
use App\Repositories\GeoCode\Location;
use App\Repositories\GeoCode\NomiNatim\NomiNatimGeoCodeRepositoryInterface;
use App\Repositories\Location\Address;
use App\Repositories\Location\AddressInterface;
use App\Repositories\Location\Country;
use App\Repositories\Media\MediaInterface;
use App\Repositories\Message\MessageInterface;
use App\Repositories\Permission\Permission;
use App\Repositories\Property\Amenity\Amenity;
use App\Repositories\Property\Amenity\AmenityRepositoryInterface;
use App\Repositories\Property\Property;
use App\Repositories\Property\PropertyInterface;
use App\Repositories\Property\TermsRules\TermsRulesRepositoryInterface;
use App\Repositories\Review\ReviewInterface;
use App\Repositories\Tokens\TokenInterface;
use App\Services\Messaging\MessageComposeInterface;
use App\Services\Messaging\MessageUserInterface;
use App\Repositories\User\User as UserUser;
use App\Repositories\Validation\NearbyPropertyValidator;
use App\Repositories\Validation\PropertyListValidator;
use App\Services\Messaging\Message;
use App\Services\Messaging\MessengerInterface;
use Illuminate\Http\Request;
use Validator;

class PropertyController extends Controller
{

    /**
     * @var TokenInterface
     */
    private $tokenInterface;

    /**
     * @var PropertyInterface
     */
    private $propertyInterface;
    /**
     * @var ReviewInterface
     */
    private $reviewInterface;

    /**
     * @var MessageInterface
     */
    private $messageInterface;

    /**
     * @var MediaInterface
     */
    private $mediaInterface;

    /**
     *
     */
    private $nearbyPropertyValidator;

    /**
     * @var AuthRepositoryInterface
     */
    private $authRepository;

    /**
     * @var NomiNatimGeoCodeRepositoryInterface
     */
    private $nomiNatimGeoCodeRepositoryInterface;
    //private $orderMailInterface;
    //private $sellerInterface;

    /**
     * @var
     */
    private $account;

    /**
     * @var
     */
    private $property;

    /**
     * @var AccessManagerInterface
     */
    private $accessManagerInterface;

    /**
     * @var AmenityRepositoryInterface
     */
    private $amenityRepositoryInterface;

    /**
     * @var  TermsRulesRepositoryInterface
     */
    private $termsRulesRepositoryInterface;

    /**
     * @var PropertyListValidator
     */
    private $propertyListValidator;

    /**
     * @var AddressInterface
     */
    private $addressInterface;

    /**
     * @var  MessageComposeInterface 
     */
    private  $messageComposeInterface;

    /**
     * @var  MessageUserInterface 
     */
    private $messageUserInterface;


    /**
     * PropertyController constructor.
     * @param NearbyPropertyValidator $nearbyPropertyValidator
     * @param TokenInterface $tokenInterface
     * @param PropertyInterface $propertyInterface
     * @param ReviewInterface $reviewInterface
     * @param MessageInterface $messageInterface
     * @param MediaInterface $mediaInterface
     * @param TermsRulesRepositoryInterface $termsRulesRepositoryInterface
     * @param AddressInterface $addressInterface
     * @param  MessageComposeInterface   $messageComposeInterface 
     * @param  MessageUserInterface $messageUserInterface
     */

    public function __construct(
        NearbyPropertyValidator $nearbyPropertyValidator,
        PropertyListValidator $propertyListValidator,
        TokenInterface $tokenInterface,
        PropertyInterface $propertyInterface,
        ReviewInterface $reviewInterface,
        MessageInterface $messageInterface,
        MediaInterface $mediaInterface,
        NomiNatimGeoCodeRepositoryInterface $nomiNatimGeoCodeRepositoryInterface,
        AccessManagerInterface $accessManager,
        AuthRepositoryInterface $authRepository,
        AmenityRepositoryInterface $amenityRepositoryInterface,
        TermsRulesRepositoryInterface $termsRulesRepositoryInterface,
        AddressInterface $addressInterface,
        MessageUserInterface $messageUserInterface,
        MessageComposeInterface  $messageComposeInterface 
    ) {

        $this->nearbyPropertyValidator = $nearbyPropertyValidator;
        $this->propertyListValidator = $propertyListValidator;
        $this->tokenInterface = $tokenInterface;
        $this->propertyInterface = $propertyInterface;
        $this->reviewInterface = $reviewInterface;
        $this->messageInterface = $messageInterface;
        $this->mediaInterface = $mediaInterface;
        $this->nomiNatimGeoCodeRepositoryInterface = $nomiNatimGeoCodeRepositoryInterface;
        $this->accessManagerInterface = $accessManager;
        $this->authRepository = $authRepository;
        $this->amenityRepositoryInterface = $amenityRepositoryInterface;
        $this->termsRulesRepositoryInterface = $termsRulesRepositoryInterface;
        $this->addressInterface = $addressInterface;
        $this->messageUserInterface = $messageUserInterface;
        $this->messageComposeInterface = $messageComposeInterface;  
        
    }

    /**
     * Get all properties
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function index(Request $request)
    {

        $sort = $request->sort;
        $order = $request->order;
        $limit = $request->limit ?? 100;
        $offset = $request->offset ?? 0;
        $tag = $request->tag;

        $property = $this->propertyInterface->fetch(limit:$limit, offset:$offset);

        $jsonString = json_encode($property);
        $decodedArray = json_decode($jsonString);

        return response()->json(

            [
                "code" => 200,
                "message" => "OK",
                "payload" => [

                    "items" => $decodedArray,
                ],
            ]

        );

    }

    public function fetchByToken(Request $request)
    {

        /*$sort = $request->sort;
        $order = $request->order;
        $limit = $request->limit;
        $tag = $request->tag;*/

        $token = $request['token'];

        $property = $this->propertyInterface->findPropertyByToken($token);

        $jsonString = json_encode($property);

        $decodedArray = json_decode($jsonString);

        if ($decodedArray != null) {

            return response()->json(

                [
                    "code" => 200,
                    "message" => "OK",
                    "payload" => [

                        "item" => $decodedArray,
                    ],
                ]

            );
        } else {
            return response()->json(

                [
                    "code" => 200,
                    "message" => "OK",
                    "payload" => [

                        "item" => [],
                    ],
                ]

            );
        }
    }

    public function getNearbyProperties(Request $request)
    {

        $this->nearbyPropertyValidator->validate();

        $sort = $request->sort;
        $order = $request->order;
        $limit = $request->limit;
        $tag = $request->tag;
        $lat = $request->latitude;
        $lng = $request->longitude;
        $distance = $request->radius;

        $location = new Location();
        $location->setLatitude($lat);
        $location->setLongitude($lng);
        $location->setMaxDistance($distance);

        $property = $this->propertyInterface->findPropertyByGeoLocation($location, 50, 0);

        return response()->json(

            [
                "code" => 200,
                "message" => "OK",
                "payload" => [
                    "items" => $property,
                ],
            ]

        );

    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function createNewProperty(Request $request)
    {
        $auth = $this->authRepository->enforce($request);

        if (!$auth->hasPermission(Permission::PERM_PROPERTY_CREATE)) {
            throw new InsufficientPrivilegeException();
        }

        $this->propertyListValidator->validate();

        $address = $request['address'];

        $property = new Property();
        $money = new Money();
        $currency = new Currency();
        $currency->setSymbol($request->currency);
        $user = new UserUser();
        $user->setId($auth->getUser()->getId());
        $property->setName($request->name);
        $money->setAmount($request->amount);
        $money->setCurrency($currency);
        $property->setMoney($money);
        $property->setStatus($request->status);
        $property->setAge($request->age);
        $property->setBathRoom($request->bath_room);
        $property->setBedRoom($request->bed_room);
        $property->setSqtFt($request->sq_ft);
        $property->setType($request->type);
        $property->setUser($user);
        $property->setDescription($request->description);

        $createProperty = $this->propertyInterface->createNewProperty($property, $auth->getUser()->getId());

        $property->setID((int) $createProperty);

        if (!empty($property->getID())) {

            $addressModel = new Address();
            $country = new Country();
            $country->setName($address['country'] ?? null);
            $addressModel->setLineOne($address['address_name'] ?? null);
            $addressModel->setLineTwo($address['address_line'] ?? null);
            $addressModel->setCountry($country);
            $addressModel->setLocality($address['locality'] ?? null);
            $addressModel->setSubLocality($address['sub_locality'] ?? null);
            $addressModel->setLatitude($address['latitude'] ?? null);
            $addressModel->setLongitude($address['longitude'] ?? null);

            $this->addressInterface->createPropertyAddress($addressModel, $property->getID());

            //$bagFacility = $request['facility']['facilities'];

            //$this->amenityRepositoryInterface->createPropertyFacilities()

            // $this->propertyInterface->createAddress($address, $property->getID());
            // $this->propertyInterface->createPropertyFacilities($request,  $property->getID());

            $amenity = new Amenity();
            $amenityRule = clone $amenity;

            if (isset($request['facility']['facilities'])) {

                $bagFacility = $request['facility']['facilities']; //json_decode($request['facility']['facilities']);

                $amenity->setAmenities(...$bagFacility);
                $amenity->setName($request['facility']['optional']);

                //-- this assign faciltities into array and compare it against db for verification
                $lengthFacilities = [];
                $lengthFacilitiesId = [];
                foreach ($bagFacility as $facility) {
                    if ($facility) {
                        $lengthFacilities[] = $facility;
                        sort($lengthFacilities);
                    }
                }

                if (count($lengthFacilities)) {

                    $query = ' WHERE f.id IN (' . "'" . implode("','", $lengthFacilities) . "')";

                    foreach ($this->amenityRepositoryInterface->fetchHouseFacility(count($lengthFacilities), 0, $query) as $hAmenity) {
                        if ($amenity instanceof Amenity) {
                            //$lengthFacilitiesId[$amenity->getId()] = $hAmenity;
                            $lengthFacilitiesId[] = $hAmenity->getId();

                            if (!empty($lengthFacilitiesId)) {
                                sort($lengthFacilitiesId);
                                $amenity->setAmenities(...$lengthFacilitiesId);
                            }

                        }
                    }

                }
                $this->amenityRepositoryInterface->create($amenity, $property->getID());
                //$this->amenityRepositoryInterface->createOptionalFacility($amenity, $amenityRule, $property->getID());

            }

            if (isset($request['terms_rules']['terms_rules']) || isset($request['facility']['optional'])) {
                //-- this assign faciltities into array and compare it against db for verification
                $lengthFacilityRules = [];
                $lengthFacilityRulesId = [];

                if (isset($request['terms_rules']['terms_rules'])) {
                    $bagFacilityRule = $request['terms_rules']['terms_rules'];

                    foreach ($bagFacilityRule as $rule) {
                        if ($rule) {
                            $lengthFacilityRules[] = $rule;
                            sort($lengthFacilityRules);
                        }
                    }

                    $amenityRule->setRulesTerms(...$lengthFacilityRules);
                }

                if (count($lengthFacilityRules)) {

                    $query = ' WHERE htr.id IN (' . "'" . implode("','", $lengthFacilityRules) . "')";

                    foreach ($this->amenityRepositoryInterface->fetchHouseFacilityRules(count($lengthFacilityRules), 0, $query) as $hTermsRule) {
                        if ($amenity instanceof Amenity) {
                            $lengthFacilityRulesId[] = $hTermsRule->getId();

                            if (!empty($lengthFacilityRulesId)) {
                                sort($lengthFacilityRulesId);
                                $amenity->setRulesTerms(...$lengthFacilityRulesId);

                            }

                        }
                    }

                }

                //$amenityRule->setOptionalRule($request['terms_rules']['optional']);

                if (isset($request['facility']['optional'])) {
                    $amenity->setOptionalRule($request['terms_rules']['optional']);
                }

                if ($amenity->getRulesTerms()) {
                    $this->termsRulesRepositoryInterface->create($amenity, $property->getID());
                }

                if ($amenity->getOptionalRule()) {
                    $this->termsRulesRepositoryInterface->createOptinalRule($amenity, $property->getID());
                }

            }

        }

        $findPropertyById = $this->propertyInterface->findById($property->getID());

        $content = <<<MESSAGE
                       we have listed your property
                      MESSAGE;

        $messagingMessage = new Message();
        $recipient = $this->messageUserInterface;
        $recipient->setId($auth->getUser()->getId());
        $recipient->setName($auth->getUser()->getName());
        $recipient->setPhone($auth->getUser()->getPhone());
        $recipient->setEmail($auth->getUser()->getEmail());
        $recipient->setSubject(sprintf( sprintf( $auth->getUser()->getName(), "'s")?? "Your", " property listed."));
        $recipient->setContent(sprintf("%s %s%s %s '%s'%s", 'Hi', $auth->getUser()->getName(), ',',$content,$findPropertyById->getDescription(),'.'));

        $notifications[] = $auth->getUser()->getId();

        $messagingMessage->setRecipients($recipient);
        $messagingMessage->setSender($recipient);
        $messagingMessage->setRecipient($recipient);
    
        $this->messageUserInterface->setIds(...$notifications);

    
       $this->messageComposeInterface->composeMessage( 
            $messagingMessage,
            $this->messageUserInterface,
            MessengerInterface::CHANNEL_SMS,
            MessengerInterface::CHANNEL_PUSH,
            MessengerInterface::CHANNEL_EMAIL
          );

        return response()->json([
            "code" => 200,
            "message" => sprintf("%s %s%s %s", 'Hi', $auth->getUser()->getName(), ',', 'we have listed your property.'),
            "payload" => [
                "item" => $findPropertyById, // $this->propertyInterface->findById( $property->getID())
            ],
        ]);

    }

   
}
