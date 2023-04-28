<?php

namespace App\Http\Controllers\Media;

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

class MediaController extends Controller
{


    /**
     * @var MediaInterface
     */
    private $mediaInterface;


    /**
     * @var AuthRepositoryInterface
     */
    private $authRepository;

    /**
     * @var PropertyInterface
     */
    private $propertyInterface;


    /**
     * MediaController constructor.
     * @param MediaInterface $mediaInterface
     * @param AuthRepositoryInterface $authRepository
     */
    public function __construct(

        MediaInterface $mediaInterface,
        AuthRepositoryInterface $authRepository,
        PropertyInterface $propertyInterface,

    )
    {
        $this->mediaInterface = $mediaInterface;
        $this->authRepository = $authRepository;
        $this->propertyInterface = $propertyInterface;

    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws InsufficientPrivilegeException
     */
    public function createPropertyMedia(Request $request)
    {

            $auth = $this->authRepository->enforce($request);

            if (!$auth->hasPermission(Permission::PERM_PROPERTY_CREATE)) {
                throw new InsufficientPrivilegeException();
            }

            $property = $request['property'];

            $fetchPropertyByToken = $this->propertyInterface->findPropertyByToken($property);

            $this->mediaInterface->createPropertyMedia($request, $fetchPropertyByToken->getID(), $auth->getUser()->getId());

        return response()->json(

            [
                "code" => 200,
                "message" => "OK"

            ]);


    }


}
