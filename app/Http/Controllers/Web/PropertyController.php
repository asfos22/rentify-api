<?php

namespace App\Http\Controllers\Web;

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
use Illuminate\Support\Facades\Session;

class PropertyController extends Controller
{


    /**
     * @var PropertyInterface
     */
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
     * @var NomiNatimGeoCodeRepositoryInterface
     */
    private $nomiNatimGeoCodeRepositoryInterface;
    //private $orderMailInterface;
    //private $sellerInterface;

    /**
     * @var SessionInterface
     */
    private $sessionManager;

    /**
     * @var MediaInterface
     */
    private $mediaInterface;


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
     * @var AuthRepositoryInterface
     */
    private $authRepository;


    /**
     * PropertyController constructor.
     * @param TokenInterface $tokenInterface
     * @param PropertyInterface $propertyInterface
     * @param ReviewInterface $reviewInterface
     * @param NomiNatimGeoCodeRepositoryInterface $nomiNatimGeoCodeRepositoryInterface
     * @param SessionInterface $sessionManager
     * @param MediaInterface $mediaInterface
     * @param AccessManagerInterface $accessManager
     * @param AuthRepositoryInterface $authRepository
     */
    public function __construct(

        TokenInterface $tokenInterface,
        PropertyInterface $propertyInterface,
        ReviewInterface $reviewInterface,
        NomiNatimGeoCodeRepositoryInterface $nomiNatimGeoCodeRepositoryInterface,
        SessionInterface $sessionManager,
        MediaInterface $mediaInterface,
        AccessManagerInterface $accessManager,
        AuthRepositoryInterface $authRepository

    )
    {
        $this->tokenInterface = $tokenInterface;
        $this->propertyInterface = $propertyInterface;
        $this->reviewInterface = $reviewInterface;
        $this->nomiNatimGeoCodeRepositoryInterface = $nomiNatimGeoCodeRepositoryInterface;
        $this->sessionManager = $sessionManager;
        $this->mediaInterface = $mediaInterface;
        $this->accessManagerInterface = $accessManager;
        $this->authRepository = $authRepository;

    }

    //-- index

    /***
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function index(Request $request)
    {

        $sort = 'created_at'; //$request->sort;
        $order = 'desc';//$request->order;
        $limit = '50';//$request->limit;


        /*$properties = PropertyHouseResource::collection(

            PropertyHouse::with(
                'media',
                'property_facility',
                'address',
                'review',
                'user')->orderBy($sort, $order)
                ->limit($limit)->get())->toJson();

        $properties = json_decode($properties);*/

        return view('layouts/index', compact('properties'));

    }


    /***
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function exploreProperty(Request $request)
    {

        $sort = 'created_at'; //$request->sort;
        $order = 'desc';//$request->order;
        $limit = '10000';//$request->limit;
        try {

            //return view('main/host/index', compact('flats','sponsors', 'date', 'photos'));


            if (!empty($sort) && !empty($order) && !empty($limit)) {

                $properties = PropertyHouseResource::collection(

                    PropertyHouse::with(
                        'media',
                        'property_facility',
                        'address',
                        'review',
                        'user')->orderBy($sort, $order)
                        ->limit($limit)->get())->toJson();

                $properties = json_decode($properties);

                return view('layouts/property/explore/explore_grid', compact('properties'));

                return response()->json(

                    ["code" => 401, "message" => "Sorry we couldn't process"]

                );

            } else {

                //todo change to default param


                return response()->json(

                    ["code" => 401, "message" => "Sorry we couldn't process"]

                );
            }


        } catch (\Exception $e) {

            return response()->json(

                ["code" => 401, "message" => "Sorry we couldn't process"]

            );

        }

    }


    public function exploreGeolocationProperty(Request $request)
    {

        $sort = 'created_at'; //$request->sort;
        $order = 'desc';//$request->order;
        $limit = '10000';//$request->limit;
        try {

            //return view('main/host/index', compact('flats','sponsors', 'date', 'photos'));


            if (!empty($sort) && !empty($order) && !empty($limit)) {

                $properties = PropertyHouseResource::collection(

                    PropertyHouse::with(
                        'media',
                        'property_facility',
                        'address',
                        'review',
                        'user')->orderBy($sort, $order)
                        ->limit($limit)->get())->toJson();

                $properties = json_decode($properties);

                return view('layouts/property/explore/geo_location/geo_location', compact('properties'));

                return response()->json(

                    ["code" => 401, "message" => "Sorry we couldn't process"]

                );

            } else {

                //todo change to default param


                return response()->json(

                    ["code" => 401, "message" => "Sorry we couldn't process"]

                );
            }


        } catch (\Exception $e) {

            return response()->json(

                ["code" => 401, "message" => "Sorry we couldn't process"]


            // $request->sort, $request->order, $request->limit


            );

        }
    }


    /****
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */

    public function propertyDetails(Request $request)
    {

        $sort = 'created_at'; //$request->sort;
        $order = 'desc';//$request->order;
        $limit = '10000';//$request->limit;
        $token = $request->token;

        $review = $this->reviewInterface->toGetReview();


        //return $review;


        if (!empty($sort) && !empty($order) && !empty($limit)) {

            $properties = PropertyHouseResource::collection(

                PropertyHouse::with(
                    'media',
                    'property_facility',
                    'address',
                    'review')->orderBy($sort, $order)
                    ->where('uuid', $token)
                    ->limit($limit)->get())->toJson();


            if (!empty($properties)) {


                if ($request->is('api*')) {

                    response()->json(

                        ["code" => 200,
                            "message" => "OK",
                            "payload" => [

                                "item" => [

                                    "property" => $properties,
                                    "review_rate" => $review

                                ]
                            ]

                        ]

                    );

                };


                if (session()->get('message') != null) {

                    session()->forget('message');
                }

                $sessionVar = json_decode($properties, true);

                session()->put('message', $sessionVar[0]['id']);

                $properties = json_decode($properties);
                $review = json_decode($review);


                // $data = array_merge($properties, ['review_rate' => $review]);

                // return $review;

                return view('layouts/property/single_list')->with(
                    "properties", $properties)->with(
                    "review_rate", $review
                );


            }
        }

        return response()->json(

            ["code" => 401, "message" => "Sorry we couldn't process"]

        );


    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */

    public function propertyDetailsTest(Request $request)
    {

        $sort = 'created_at'; //$request->sort;
        $order = 'desc';//$request->order;
        $limit = '10000';//$request->limit;
        try {

            //return view('main/host/index', compact('flats','sponsors', 'date', 'photos'));


            if (!empty($sort) && !empty($order) && !empty($limit)) {


                return view('layouts/property/property_details', compact("test",

                    PropertyHouseResource::collection(

                        PropertyHouse::with(
                            'media',
                            'property_facility',
                            'address',
                            'review')->orderBy($sort, $order)
                            ->limit($limit)->get())));

                /*response()->json(

                     ["code" => 200, "message" => "OK", "items" =>
                         $this->propertyInterface->getPropertiesHouses(
                             $request->sort,
                             $request->order,
                             $request->limit)
                     ]

                 );*/

                return response()->json(

                    ["code" => 401, "message" => "Sorry we couldn't process"]

                );

            } else {

                //todo change to default param


                return response()->json(


                    ["code" => 401, "message" => "Sorry we couldn't process"]

                );
            }


        } catch (\Exception $e) {

            return response()->json(

                ["code" => 401, "message" => "Sorry we couldn't process"]


            // $request->sort, $request->order, $request->limit


            );

        }


        //$this->customerOrderInterface->getCustomerOrders();
    }


    // -- store

    /**
     * @param Request $request
     * @return string
     */
    public function newProperty(Request $request)
    {


        //  return $this->accessManagerInterface->enforce($request);

        $this->account = $this->sessionManager->getAccountSession(Constants::SESSION_IS_LOGIN_IN);


        if (empty($this->account)) {

            return redirect()->intended('auth/login');
        }


        //-- token check

        $auth = $this->tokenInterface->verifyAccountToken($request, $this->account['data']['id']);

        if ($auth === 401) {

            return response()->json([
                "message" => "Invalid token please re-login",
                "code" => 401
            ]);

        }


        $propertyCategory = $this->propertyInterface->getPropertyCategory($request);
        $propertyFacilities = $this->propertyInterface->getPropertyFacilities($request);

        $propertyStayDuration = $this->propertyInterface->getPropertyStayDuration($request);
        $propertyAge = $this->propertyInterface->getPropertyAge($request);
        $currencies = $this->propertyInterface->getCurrencies($request);
        $propertyStatus = $this->propertyInterface->getPropertyStatus($request);


        /*"category"=>  $propertyCategory,
                            "pension" =>  $propertyFacilities,
                            "duration"=>  $propertyStayDuration,
                            "age"=>  $propertyAge,
                            "category"=>  $currencies,
                        ],*/


        //$category = $propertyCategory;


        // -- set session

        session(['ex_d' => rand()]);
        //Session::put('email', 'fernandoborgesjr@outlook.com');
        // Session::put('somekey', 'somevalue');

        //$category = json_decode($category);

        $propertyFacilities = json_decode($propertyFacilities);
        $propertyStayDuration = json_decode($propertyStayDuration);
        $propertyAge = json_decode($propertyAge);
        $currencies = json_decode($currencies);
        $propertyCategory = json_decode($propertyCategory);
        $propertyStatus = json_decode($propertyStatus);

        if (!empty($propertyFacilities) &&
            !empty($propertyStayDuration) &&
            !empty($propertyAge) &&
            !empty($currencies) &&
            !empty($propertyCategory)
        ) {

            return view('layouts/property/new_property/new_property')
                ->with(
                    "facility", $propertyFacilities)->with(
                    "duration", $propertyStayDuration)->with(
                    "property_age", $propertyAge
                )->with(
                    "currencies", $currencies)->with(
                    "category", $propertyCategory
                )->with("status", $propertyStatus);
        }


    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     * @throws InsufficientPrivilegeException
     */
    public function createNewProperty(Request $request)
    {

        if ($request->is('api*')) {

            $auth = $this->authRepository->enforce($request);

            if (!$auth->hasPermission(Permission::PERM_PROPERTY_CREATE)) {
                throw new InsufficientPrivilegeException();
            }


            $address = $request['address'];
            $this->property = $this->propertyInterface->createNewProperty($request, $auth->getUser()->getId());

            if (!empty($this->property->id)) {

               // $this->propertyInterface->createAddress($address, $this->property->id);
               // $this->propertyInterface->createPropertyFacilities($request, $this->property->id);
                // $this->mediaInterface->createPropertyMedia($request, $this->property->id, $this->account['data']['id']);
            }

            return response()->json([
                "code" => 200,
                "message" => 'Hi ' . $auth->getUser()->getName() . " we have listed your property.",
                "payload" => [

                    "item" => $this->property
                ]
            ]);


        }

        $this->account = $this->sessionManager->getAccountSession(Constants::SESSION_IS_LOGIN_IN);


        if (empty($this->account)) {

            return redirect()->intended('auth/login');
        }


        //-- token check

        $auth = $this->tokenInterface->verifyAccountToken($request, $this->account['data']['id']);

        if ($auth === 401) {

            return response()->json([
                "message" => "Invalid token please re-login",
                "code" => 401
            ]);

        }


        $rules = [
            'name' => 'required',
            'currency' => 'required',
            'amount' => 'required',
            'status' => 'required',
            'description' => 'required',
            'sq_ft' => 'required',
            'age' => 'required',
            'bath_room' => 'required',
            'bed_room' => 'required',
            'type' => 'required',
            'ex_d' => 'required',
            'files' => 'required'
        ];

        $messages = [
            'name.required' => 'Hmm write something about your property',
            'currency.required' => 'Select currency',
            'amount.required' => 'Enter property rate',
            'status.required' => 'Select is what the  property  is for',
            'description.required' => 'Describe your property  for us',
            'sq_ft.required' => 'Enter floor area of your property',
            'age.required' => 'How old is this property please ?',
            'sq_ft.required' => 'Enter floor area of your property',
            'bath_room.required' => 'Number of bath room',
            'bed_room.required' => 'Number of bed room',
            'type.required' => 'Select category',
            'ex_d.required' => 'Select location',
            'files.required' => 'Upload at least one or more photo(s)'
        ];


        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->all());

        }


        $latLng = json_decode($request->ex_d, true);

        $latitude = $latLng['lat'] ?? 0;
        $longitude = $latLng['lng'] ?? 0;

        $nomiNatimGeoCodeAddress = $this->nomiNatimGeoCodeRepositoryInterface->postGeoCode($latitude ?? 0, $longitude ?? 0);


        $address = Array
        (
            "address_name" => $nomiNatimGeoCodeAddress['house_number'] ?? '' . (!empty($nomiNatimGeoCodeAddress['road'])) ?? '',
            "address_line" => $nomiNatimGeoCodeAddress['neighbourhood'] ?? '' . (!empty($nomiNatimGeoCodeAddress['city'])) ?? '' . $nomiNatimGeoCodeAddress['state'] ?? '',
            "locality" => $nomiNatimGeoCodeAddress['state'] ?? null,
            "sub_locality" => $nomiNatimGeoCodeAddress['county'] ?? null,
            "country" => $nomiNatimGeoCodeAddress['country'] ?? null,
            "latitude" => $latitude ?? 0,
            "longitude" => $longitude ?? 0,
        );

        $this->property = $this->propertyInterface->createNewProperty($request, $this->account['data']['id']);

        if (!empty($this->property->id)) {

            //$this->propertyInterface->createAddress($address, $this->property->id);
            $this->propertyInterface->createPropertyFacilities($request, $this->property->id);
            $this->mediaInterface->createPropertyMedia($request, $this->property->id, $this->account['data']['id']);


        }

        return redirect()->back()->with(

            'error', 200, 'Request submitted'

        );

        return response()->json(["message" => "Request cannot process", "code" => 403]);


    }


    /****
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */

    public function createRentHostMessage(Request $request)
    {

        $sort = $request->token;
        $order = $request->house_token;//$request->order;
        try {

            //return view('main/host/index', compact('flats','sponsors', 'date', 'photos'));


            if (!empty($sort) && !empty($order) && !empty($limit)) {


                return view('layouts/property/property_details', compact("test",

                    PropertyHouseResource::collection(

                        PropertyHouse::with(
                            'media',
                            'property_facility',
                            'address',
                            'review')->orderBy($sort, $order)
                            ->limit($limit)->get())));

                /*response()->json(

                     ["code" => 200, "message" => "OK", "items" =>
                         $this->propertyInterface->getPropertiesHouses(
                             $request->sort,
                             $request->order,
                             $request->limit)
                     ]

                 );*/

                return response()->json(

                    ["code" => 401, "message" => "Sorry we couldn't process"]

                );

            } else {

                //todo change to default param


                return response()->json(

                    ["code" => 401, "message" => "Sorry we couldn't process"]

                );
            }


        } catch (\Exception $e) {

            return response()->json(

                ["code" => 401, "message" => "Sorry we couldn't process"]


            // $request->sort, $request->order, $request->limit


            );

        }

    }


}
