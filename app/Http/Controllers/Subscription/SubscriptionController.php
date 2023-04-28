<?php

namespace App\Http\Controllers\Subscription;

use App\Http\Controllers\Controller;
use App\Repositories\Auth\AuthRepositoryInterface;
use App\Repositories\DateTime;
use App\Repositories\Guest\GuestToken;
use App\Repositories\Guest\GuestTokenInterface;
use App\Repositories\Guest\TokenInterface;
use App\Repositories\Notification\NotificationInterface;
use App\Repositories\NotificationToken\NotificationToken;
use App\Repositories\NotificationToken\NotificationTokenInterface;

use Illuminate\Http\Request;


class SubscriptionController extends Controller
{

    /**
     * @var NotificationInterface
     */
    private $notificationInterface;

    /**
     * @var
     */
    private $guestTokenInterface;

    /**
     * @var AuthRepositoryInterface
     */
    private $authRepository;


    public function __construct(

        AuthRepositoryInterface $authRepository,
        NotificationInterface $notificationInterface,
        GuestTokenInterface $guestTokenInterface

    )
    {

        $this->notificationInterface = $notificationInterface;
        $this->guestTokenInterface = $guestTokenInterface;
        $this->authRepository = $authRepository;

    }


    /**
     * Create user notification subscription
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */

    public function createUserNotificationToken(Request $request)
    {
    
        $notificationToken = new NotificationToken();
        //$authHeader = $this->authRepository->enforceHeader($request);

        //----
        $auth = $this->authRepository->enforce($request);

        /*if (!$auth->hasPermission(Permission::PERM_MESSAGE_SEND)) {
            throw new InsufficientPrivilegeException();
        }

        if (!$request->header(Constants::RENTIFY_PROPERTY_TOKEN)) {

            throw new AccessControlException('Sorry resource not found.', 401);
        }*/
        //---
       
        $ip = null;
        $client = null;

        if (null !== $request) {

            $ip = $request->ip();
            $client = $request->header('User-Agent');
        }

        //$notificationToken->setUser($authHeader);
        $notificationToken->setUser($auth->getUser());
        $notificationToken->setPushToken($request->token);
        $notificationToken->setDeviceNumberID($request->device_number);
        $notificationToken->setIsPhysicalDevice($request->is_physical_device);
        $notificationToken->setVersionRelease($request->release_version);
        $notificationToken->setPlatform($request->platform);
        $notificationToken->setModelName($request->model);
        $notificationToken->setIp($ip);
        $notificationToken->setClient($client);
        $notificationToken->setPushEnabled(true);
        $notificationToken->setCreatedAt(new DateTime());
        $notificationToken->setUpdatedAt(new DateTime());

        $notification = $this->notificationInterface->createNotificationToken($notificationToken);
       
        $notificationRef = $this->notificationInterface->fetchTokenDeviceID($notification);

        if ($notificationRef != null) {
            return response()->json([
                "message" => "OK",
                "code" => 200,
                "payload" => [

                    "item" => [
                        "ref" => $notificationRef[0]->ref??[]
                    ]
                ]]);

        }

        return response()->json([
                "code" => 404,
                "message" => "Resource not found",
            ]
        );


    }


    /***
     * Create user notification subscription
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function createGuestNotificationToken(Request $request)
    {

        $guestToken = $this->guestTokenInterface->createGuestToken($request);

        // dump($guestToken->getId());

        $token = new GuestToken();
        $token->setId($guestToken->getId());


        $guestToken = $this->guestTokenInterface->fetchGuestTokenByID($token);

        return response()->json([
            "message" => "OK",
            "code" => 200,
            "payload" => [

                "item" => [
                    "token" => $guestToken[0]->secret
                ]
            ]

        ]);

        return response()->json([
            "code" => 403,
            "message" => "Request cannot process"

        ]);


    }

}