<?php

namespace App\Http\Controllers\Message;

use App\Constants;
use App\Http\Controllers\Controller;
use App\Http\Exception\AccessControlException;
use App\Http\Exception\InsufficientPrivilegeException;
use App\Http\Exception\ResourceNotFoundException;
use App\Repositories\Auth\Auth;
use App\Repositories\Auth\AuthRepositoryInterface;
use App\Repositories\Auth\Verification\VerificationRepositoryInterface;
use App\Repositories\Conversation\Conversation;
use App\Repositories\Message\Message;
use App\Repositories\Message\MessageInterface;
use App\Repositories\Message\MessageRepository;
use App\Repositories\Message\MessagingServiceInterface;
use App\Repositories\Messaging\RepoMessage;
use App\Repositories\Messaging\RepoMessageInterface;
use App\Repositories\Messaging\ServiceMessageInterface;
use App\Repositories\NotificationToken\NotificationToken;
use App\Repositories\NotificationToken\NotificationTokenInterface;
use App\Repositories\Notification\NotificationInterface;
use App\Repositories\Notification\NotificationRepository;
use App\Repositories\Permission\Permission;
use App\Repositories\Permission\PermissionRepositoryInterface;
use App\Repositories\Property\Property;
use App\Repositories\Property\PropertyInterface;
use App\Repositories\Review\ReviewInterface;
use App\Repositories\Role\Role;
use App\Repositories\Role\RoleRepositoryInterface;
use App\Repositories\Session\SessionInterface;
use App\Repositories\System\Event\SystemBroadcaseChannel;
use App\Repositories\System\Event\SystemEvent;
use App\Repositories\Tokens\TokenInterface;
use App\Repositories\User\User;
use App\Repositories\User\UserRepository;
use App\Repositories\Util\Utils;
use App\Repositories\Validation\GuestHostMessageValidator;
use App\Services\Messaging\Message as MessagingMessage;
use App\Services\Messaging\MessageComposeInterface;
use App\Services\Messaging\MessageInterface as MessageMessageInterface;
use App\Services\Messaging\MessageUserInterface;
use App\Services\Messaging\MessengerInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
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
     * @var NotificationInterface
     */
    private $notificationInterface;

    /**
     * @var PermissionRepositoryInterface
     */
    private $permissionRepository;

    /**
     * @var
     */
    private $messageID;
    /**
     * @var
     */

    private $message;

    /**
     * @var SessionInterface
     */
    private $sessionManager;

    /**
     * @var
     */
    private $account;

    /**
     * @var MessageRepository
     */
    private $messageRepository;

    /**
     * @var NotificationTokenInterface
     */
    private $notificationToken;
    /**
     * @var AuthRepositoryInterface
     */
    private $authRepository;

    /**
     * @var PropertyInterface
     */
    private $property;

    /**
     * @var
     */

    private $propertyID;

    /**
     * @var
     */
    private $notificationRepository;

    /**
     * @var
     */
    private $auth;

    /**
     * @var
     */
    private $guestHostMessageValidator;

    /**
     * @var
     */
    private $userRepository;

    /**
     * @var
     */
    private $verificationRepository;
    /**
     * @var
     */
    private $messenger;

    private $repoMessageInterface;
    /**
     * @var
     */
    private $serviceMessageInterface;

    private $messageUserInterface;

    private $interfaceAggregator;

    private $findPropertyHost;

    private $user;
    /**
     * @var  MessageComposeInterface
     */
    private $messageComposeInterface;

    /**
     * MessageController constructor.
     * @param AuthRepositoryInterface $authRepository
     * @param TokenInterface $tokenInterface
     * @param PropertyInterface $propertyInterface
     * @param ReviewInterface $reviewInterface
     * @param MessageInterface $messageInterface
     * @param NotificationInterface $notificationInterface
     * @param SessionInterface $sessionManager
     * @param PermissionRepositoryInterface $permissionRepository
     * @param MessageRepository $messageRepository
     * @param NotificationTokenInterface $notificationToken
     * @param NotificationRepository $notificationRepository
     * @param GuestHostMessageValidator $guestHostMessageValidator
     * @param RepoMessageInterface repoMessageInterface
     * @param ServiceMessageInterface serviceMessageInterface
     * @param MessageComposeInterface   $messageComposeInterface
     */

    public function __construct(

        AuthRepositoryInterface $authRepository,
        TokenInterface $tokenInterface,
        PropertyInterface $propertyInterface,
        ReviewInterface $reviewInterface,
        MessageInterface $messageInterface,
        NotificationInterface $notificationInterface,
        SessionInterface $sessionManager,
        PermissionRepositoryInterface $permissionRepository,
        MessageRepository $messageRepository,
        NotificationTokenInterface $notificationToken,
        NotificationRepository $notificationRepository,
        GuestHostMessageValidator $guestHostMessageValidator,
        VerificationRepositoryInterface $verificationRepository,
        RoleRepositoryInterface $roleRepository,
        UserRepository $userRepository,
        MessagingServiceInterface $messenger,
        MessageUserInterface $messageUserInterface,
        MessageComposeInterface $messageComposeInterface

    ) {
        $this->authRepository = $authRepository;
        $this->tokenInterface = $tokenInterface;
        $this->propertyInterface = $propertyInterface;
        $this->reviewInterface = $reviewInterface;
        $this->messageInterface = $messageInterface;
        $this->notificationInterface = $notificationInterface;
        $this->sessionManager = $sessionManager;
        $this->permissionRepository = $permissionRepository;
        $this->messageRepository = $messageRepository;
        $this->notificationToken = $notificationToken;
        $this->notificationRepository = $notificationRepository;
        $this->guestHostMessageValidator = $guestHostMessageValidator;
        $this->verificationRepository = $verificationRepository;
        $this->roleRepository = $roleRepository;
        $this->userRepository = $userRepository;
        $this->messenger = $messenger;
        $this->messageUserInterface = $messageUserInterface;
        $this->messageComposeInterface = $messageComposeInterface;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws InsufficientPrivilegeException
     */

    public function getMessages(Request $request)
    {

        $auth = $this->authRepository->enforce($request);

        if (!$auth->hasPermission(Permission::PERM_MESSAGE_READ)) {
            throw new InsufficientPrivilegeException();
        }

        $sort = $request->sort ?? 'created_at';
        $order = $request->order ?? 'desc';
        $limit = $request->limit ?? 500;
        //$user = $request->user;

        if (!empty($auth->getUser()->getId())) {

            $this->message = $this->messageInterface->fetchMessagesByUserID($auth->getUser()->getId(), $sort, $order, $limit);

            return response()->json(

                [
                    "code" => 200,
                    "message" => "OK",
                    "payload" => [
                        "items" =>
                        $this->message ?? [],

                    ],
                ]

            );

        };

    }

    /**
     * @param Request conversations $request
     * @return JsonResponse
     * @throws InsufficientPrivilegeException
     * @throws ResourceNotFoundException
     */

    public function getConversations(Request $request)
    {

        $auth = $this->authRepository->enforce($request);

        if (!$auth->hasPermission(Permission::PERM_MESSAGE_READ)) {
            throw new InsufficientPrivilegeException();
        }

        $sort = $request->sort ?? 'created_at';
        $order = $request->order ?? 'desc';
        $limit = $request->limit ?? 100;
        $token = $request->token ?? '';

        if (!empty($auth->getUser()->getId()) && !empty($token)) {

            $messageToken = $this->messageInterface->fetchMessagesByToken($sort, $order, 1, $token);

            if (empty($messageToken)) {

                throw new ResourceNotFoundException('Resource not found', 403);

            }

            $conversation = $this->messageInterface->fetchConversationByMessageID($sort, $order, $limit, $messageToken->getID());

            return response()->json(

                [
                    "code" => 200,
                    "message" => "OK",
                    "payload" => [
                        "items" =>
                        $conversation ?? [],
                    ],
                ]

            );

        };

    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws InsufficientPrivilegeException
     * @throws ResourceNotFoundException
     */

    public function getConversation(Request $request)
    {

        $auth = $this->authRepository->enforce($request);

        if (!$auth->hasPermission(Permission::PERM_MESSAGE_READ)) {
            throw new InsufficientPrivilegeException();
        }

        $sort = $request->sort ?? 'created_at';
        $order = $request->order ?? 'desc';
        $limit = $request->limit ?? 100;
        $token = $request->token;

        if (!empty($auth->getUser()->getId()) && !empty($token)) {

            $messageToken = $this->messageInterface->fetchMessagesByToken($sort, $order, 1, $token);

            if (empty($messageToken)) {

                throw new ResourceNotFoundException('Resource not found', 403);

            }

            $conversation = $this->messageInterface->fetchConversationByMessageID($sort, $order, $limit, $messageToken[0]->id);
            //$conversation = $this->messageInterface->fetchConversationByMessageID($sort, $order, 1, $messageToken[0]->getID());

            return response()->json(

                [
                    "code" => 200,
                    "message" => "OK",
                    "payload" => [
                        "items" =>
                        $conversation ?? [],
                    ],
                ]

            );

        };

    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws InsufficientPrivilegeException
     * @throws ResourceNotFoundException
     */

    public function createConversation(Request $request)
    {

        $sort = 'created_at'; //$request->sort;
        $order = 'desc'; //$request->order;

        $token = $request['token'];
        $msg = $request['message'];
        $refToken = $request['ref'];

        $auth = $this->authRepository->enforce($request);

        if (!$auth->hasPermission(Permission::PERM_MESSAGE_CONVERSATION_SEND)) {
            throw new InsufficientPrivilegeException();
        }

        if (!empty($token)) {

            $messageToken = $this->messageInterface->fetchMessagesByToken($sort, $order, 1, $token);
        }

        if (empty($messageToken)) {

            throw new ResourceNotFoundException('Resource not found', 403);

        }

        $message = new Message();
        $message->setToken($token);
        $message->setID($messageToken->getID());
        $conversation = new Conversation();
        $conversation->setName($auth->getUsername());
        $conversation->setText($msg);
        $conversation->setStatus(0);
        $message->setConversation($conversation);

        $user = new User();
        $user->setName($auth->getUser()->getName());
        $user->setId($auth->getUser()->getId());

        $notificationToken = new NotificationToken();
        $notificationToken->setRefCode($refToken);

        $notificationRepository = $this->notificationRepository->fetchTokenByRefCode($notificationToken);

        if (empty($notificationRepository)) {

            throw new ResourceNotFoundException('Resource not found', 403);

        }

        $message->setNotificationID($notificationRepository->getID());

        $createdConversation = $this->messageInterface->createConversation($message, $user);

        $conversation = $this->messageInterface->getConversation($sort, $order, 1, $createdConversation->getId());

        //---
        // $specificConversationByID = $this->messageInterface->fetchSpecificConversationByID($sort,$order,1, 1);
        $fetchConversationByIDS = $this->messageInterface->fetchConversationIDSByid((int) $messageToken->getId());
       
        //$encrypt = $crypto->encryptAesGcm($fecthForgottenPassword->getUser()->getEmail(), $keys['forgot-confirm-encryption-key'], "base64");

        //$decrypt = $crypto->decryptAesGcm($encrypt, $keys['confirm-encryption-key'], "base64");

        $subject = sprintf("%s", $conversation->getName() ?? "Rentify");
        $content = sprintf("%s", $conversation->getText() ?? "New message");

        /*<<<MESSAGE
        %s
        MESSAGE;*/

        /*<<<MESSAGE
        %s
        MESSAGE;*/
        //--

        $messagingMessage = new MessagingMessage();
        $recipient = $this->messageUserInterface;
        $recipient->setId($auth->getUser()->getId());
        $recipient->setName($auth->getUser()->getName());
        $recipient->setPhone($auth->getUser()->getPhone());
        $recipient->setEmail($auth->getUser()->getEmail());
        $recipient->setSubject($subject);
        $recipient->setContent($content);

        $messagingMessage->setSubject($subject);
        $messagingMessage->setContent($content);
        $messagingMessage->setEvent(SystemEvent::EVENT_SERVICE_CHAT_IN_PROGRESS);
        $messagingMessage->setState(MessageMessageInterface::STATE_SUCCESS);
        $messagingMessage->setRecipients($recipient);
        $messagingMessage->setRecipient($recipient);
        $messagingMessage->setSender($recipient);

        $messagingMessage->setPayload(
            array(
                //"image_url"=> "https://via.placeholder.com/150",
                "message" => "OK",
                "code" => 200,
                "channel" => SystemBroadcaseChannel::CHANNEL_USERS_MESSAGE_CHAT,
                "token" => $message->getToken(),
                "item" => $conversation, //$conversation//$conversation,
            ));

        $this->messageUserInterface->setIds(...$fetchConversationByIDS/*...$notifications*/);

        $this->messageComposeInterface->composeMessage(
            $messagingMessage,
            $this->messageUserInterface,
            //MessengerInterface::CHANNEL_SMS,
            MessengerInterface::CHANNEL_PUSH,
            //MessengerInterface::CHANNEL_EMAIL
        );

        return response()->json([
            "message" => "OK",
            "code" => 200,
            "item" => $conversation,
        ]);

    }

    /**
     * @param Request $request
     * @return JsonResponse|\Illuminate\Http\RedirectResponse|string
     * @throws AccessControlException
     * @throws InsufficientPrivilegeException
     * @throws ResourceNotFoundException
     * @param MessagingMessage
     */

    public function createGuestHostMessage(Request $request)
    {

        //  API response
        $name = $request->name;
        $email = $request->email;
        $phoneNumber = $request->phone_number;
        $role = "Guest"; //$request->role;
        $country = $request->country ?? '';
        $this->message = $request->message;

        //-- validation
        $this->createAccount = $this->guestHostMessageValidator->validate();

        if (!$request->header(Constants::RENTIFY_PROPERTY_TOKEN)) {

            throw new AccessControlException('Resource not found.', 404);
        }

        if ($request->hasHeader(Constants::RENTIFY_PROPERTY_TOKEN) &&

            !empty($request->hasHeader(Constants::RENTIFY_PROPERTY_TOKEN))) {

            $propertyToken = $request->header(Constants::RENTIFY_PROPERTY_TOKEN);

            if (empty($propertyToken)) {

                throw new ResourceNotFoundException('Resource not found', 404);

            }

            $this->property = $this->propertyInterface->findPropertyByToken($propertyToken);

            if (empty($this->property->getID() ?? 0)) {

                throw new ResourceNotFoundException('Resource not found', 404);

            }
        }

        //-- init

        $this->user = new User();
        $this->role = new Role();
        // $message = new Message();

        //-- user
        $this->user->setName($name);
        $this->user->setEmail($email);
        $this->user->setphone($phoneNumber);

        $this->role = $this->roleRepository->findRoleByIDName($role);

        $this->user->setRole($this->role);

        /// $this->createAccount = $this->authRepository->createAccount($name, $email, $phoneNumber,$country, $this->role);

        $findUser = $this->userRepository->findByEmailOrPhone($this->user->getEmail(), $this->user->getPhone()); //findByEmail($email);

        $this->findPropertyHost = $this->userRepository->find($this->property->getHost()->getUserToken());

        if (!empty($findUser)) {

            // $message->setReceiver($findReceiver);
        }

        if (!empty($findUser) && !empty($this->findPropertyHost)) {

            if ($findUser->getPhone() === $phoneNumber || $findUser->getEmail() === $email) {

                $notifications = [];

                //--
                $messagingMessage = new MessagingMessage();
                $recipient = $this->messageUserInterface;
                $recipient->setId($this->findPropertyHost->getId());
                $recipient->setName($this->findPropertyHost->getName());
                $recipient->setPhone($this->findPropertyHost->getPhone());
                $recipient->setEmail($this->findPropertyHost->getEmail());

                $notifications[] = $recipient->getId();

                $sender = clone $this->messageUserInterface;
                $sender->setId($findUser->getId());
                $sender->setName($findUser->getName());
                $sender->setPhone($findUser->getPhone());
                $sender->setEmail($findUser->getEmail());

                $notifications[] = $sender->getId();

                // $recipient->setName();
                $messagingMessage->setSender($sender);
                $messagingMessage->setRecipient($recipient);
                $messagingMessage->setRecipients($recipient);

                $this->messageUserInterface->setIds(...$notifications);

                $message = $this->composeMessage($messagingMessage);

                return response()->json(

                    [
                        "code" => 200,
                        "message" => "OK",
                        "payload" => [
                            "items" => $message ?? [],
                        ],

                    ]
                );

            }

        }

        $createAccount = $this->authRepository->createAccount($name, $email, $phoneNumber, $country, $this->role);

        if (!empty($createAccount)) {

            $utils = new Utils();
            $this->auth = new Auth();
            $user = new User();
            $this->auth->setPassword($utils->generateToken(225));
            $user->setId($createAccount->getId());
            $user->setName($name);
            $user->setPhone($phoneNumber);
            $user->setEmail($email);
            $this->auth->setUser($user);

            $this->verificationRepository->createAccountVerification($this->auth, false);
        }

        if (!empty($createAccount) && !empty($this->findPropertyHost)) {

            if ($createAccount->getPhone() === $phoneNumber || $createAccount->getEmail() === $email) {

                $notifications = [];

                //--
                $messagingMessage = new MessagingMessage();
                $recipient = $this->messageUserInterface;
                $recipient->setId($this->findPropertyHost->getId());
                $recipient->setName($this->findPropertyHost->getName());
                $recipient->setPhone($this->findPropertyHost->getPhone());
                $recipient->setEmail($this->findPropertyHost->getEmail());

                $notifications[] = $recipient->getId();

                $sender = $this->messageUserInterface;
                $sender->setId($createAccount->getId());
                $sender->setName($createAccount->getName());
                $sender->setPhone($createAccount->getPhone());
                $sender->setEmail($createAccount->getEmail());

                $notifications[] = $sender->getId();

                $messagingMessage->setSender($sender);
                $messagingMessage->setRecipient($recipient);
                $messagingMessage->setRecipients($recipient);

                $this->messageUserInterface->setIds(...$notifications);

                $message = $this->composeMessage($messagingMessage);

                return response()->json(

                    [
                        "code" => 200,
                        "message" => "OK",
                        "payload" => [
                            "items" => $message ?? [],
                        ],
                    ]
                );

            }

        }

    }

    /**
     * @param Request $request
     * @return JsonResponse|\Illuminate\Http\RedirectResponse|string
     * @throws AccessControlException
     * @throws InsufficientPrivilegeException
     * @throws ResourceNotFoundException
     */

    public function createRentHostMessage(Request $request)
    {

        $auth = $this->authRepository->enforce($request);

        if (!$auth->hasPermission(Permission::PERM_MESSAGE_SEND)) {
            throw new InsufficientPrivilegeException();
        }

        if (!$request->header(Constants::RENTIFY_PROPERTY_TOKEN)) {

            throw new AccessControlException('Sorry resource not found.', 401);
        }

        $this->message = $request['message'];

        $user = new User();
        $user->setName($auth->getUser()->getName());
        $user->setId($auth->getUser()->getId());

        $message = new Message();

        $message->setName($auth->getUsername());
        $message->setText($this->message);
        $message->setStatus(1);
        $message->setUser($user);
        //$message->setConversation($conversation);
        $notificationToken = new NotificationToken();

        if ($request->ref != null) {

            $notificationToken->setRefCode($request->ref);
        }

        if ($request->hasHeader(Constants::RENTIFY_PROPERTY_TOKEN) &&

            !empty($request->hasHeader(Constants::RENTIFY_PROPERTY_TOKEN))) {

            $propertyToken = $request->header(Constants::RENTIFY_PROPERTY_TOKEN);

            if (empty($propertyToken)) {

                throw new ResourceNotFoundException('Resource not found', 403);

            }
            $this->property = $this->propertyInterface->findPropertyByToken($propertyToken);

            if (empty($this->property->getID())) {

                throw new ResourceNotFoundException('Resource not found', 404);

            }
            if (!empty($this->property->getID())) {

                $this->propertyID = $this->property->getID();
            }

        }

        $this->findPropertyHost = $this->userRepository->find($this->property->getHost()->getUserToken());

        $findUser = $this->userRepository->findByEmailOrPhone($auth->getUser()->getEmail(), $auth->getUser()->getPhone()); //findByEmail($email);

        $this->findPropertyHost = $this->userRepository->find($this->property->getHost()->getUserToken());

        if (!empty($findUser)) {

            // $message->setReceiver($findReceiver);
        }

        if (!empty($findUser) && !empty($this->findPropertyHost)) {

            if ($findUser->getPhone() === $auth->getUser()->getPhone() || $findUser->getEmail() === $auth->getUser()->getEmail()) {

                $notifications = [];

                //--
                $messagingMessage = new MessagingMessage();
                $recipient = $this->messageUserInterface;
                $recipient->setId($this->findPropertyHost->getId());
                $recipient->setName($this->findPropertyHost->getName());
                $recipient->setPhone($this->findPropertyHost->getPhone());
                $recipient->setEmail($this->findPropertyHost->getEmail());

                $notifications[] = $recipient->getId();

                $sender = clone $this->messageUserInterface;
                $sender->setId($findUser->getId());
                $sender->setName($findUser->getName());
                $sender->setPhone($findUser->getPhone());
                $sender->setEmail($findUser->getEmail());

                $notifications[] = $sender->getId();

                // $recipient->setName();
                $messagingMessage->setSender($sender);
                $messagingMessage->setRecipient($recipient);
                $messagingMessage->setRecipients($recipient);

                $this->messageUserInterface->setIds(...$notifications);

                $message = $this->composeMessage($messagingMessage);

                return response()->json(

                    [
                        "code" => 200,
                        "message" => "OK",
                        "payload" => [
                            "items" => $message ?? [],
                        ],

                    ]
                );

            }
        }
    }

    /**
     * Compose message
     * @param Message
     * @param User
     */
    public function composeMessage(MessagingMessage $messagingMessage): ?Message
    {
        $message = new Message();

        $sender = new User();
        $sender->setId($messagingMessage->getSender()->getId());
        $sender->setName($messagingMessage->getSender()->getName());
        $sender->setEmail($messagingMessage->getSender()->getEmail());
        $sender->setPhone($messagingMessage->getSender()->getPhone());
        $message->setUser($sender);

        $receiver = new User();
        $receiver->setId($messagingMessage->getRecipient()->getId());
        $receiver->setName($messagingMessage->getRecipient()->getName());
        $receiver->setEmail($messagingMessage->getRecipient()->getEmail());
        $receiver->setPhone($messagingMessage->getRecipient()->getPhone());
        $message->setReceiver($receiver);

        $messagingMessage->setData(true);
        $messagingMessage->setChannels( //
            MessengerInterface::CHANNEL_SMS,
            MessengerInterface::CHANNEL_PUSH,
            MessengerInterface::CHANNEL_EMAIL
        );

        //----
        $messagingMessage->setEvent(SystemEvent::EVENT_AUTH_PASSWORD_RESET);
        $messagingMessage->setState(MessageMessageInterface::STATE_SUCCESS);

        //----

        $sender = clone $this->messageUserInterface;

        $subject = <<<MESSAGE
                New message from  %s
                MESSAGE;

        $sender->setSubject(sprintf($subject, "Rent App"));

        $content = <<<MESSAGE
                    Dear %s, \n\n We have sent your message to %s.\n\n\n\n\n Thank you. \n\n Rent App Team
                    MESSAGE;

        $sender->setContent(sprintf($content, $messagingMessage->getSender()->getName(), $messagingMessage->getRecipient()->getName()));

        // $recipient->setName();
        $messagingMessage->setSender($sender);

        $recipientSubject = <<<MESSAGE
                %s just messaged you
                MESSAGE;

        $messagingMessage->setSubject(sprintf($messagingMessage->getSender()->getName()), $recipientSubject);

        $recipientContent = <<<MESSAGE
                    %s\n\n\n\n\n Thank you. \n\nRent App Team
                    MESSAGE;

        $messagingMessage->setContent(sprintf($recipientContent, $this->message));

        $propertyOb = new Property();
        $propertyOb->setID($this->property != null ? $this->property->getID() : null);
        $propertyOb->setUser($this->user);

        $message->setText($messagingMessage->getContent());

        $this->message = $this->messageInterface->createRentHostMessage($message, $propertyOb ?? null);

        $sort = 'created_at'; //$request->sort;
        $order = 'desc'; //$request->order;
        $limit = '1'; //$request->limit;
        $msg = $this->messageInterface->fetchMessagesByID($sort, $order, $limit, $this->message->getId());
        unset(
            $msg['id'],
            $msg['token'],
            $msg['user_id'],
            $msg['house_id'],
            $msg['updated_at'],
            $msg['channel_id'],
            $msg['notification_token_id'],
            $msg['sender_id'],
            // $msg['created_at']
        );
        $message = new Message();

        $message->setName($msg[0]->getName());
        $message->setText($msg[0]->getText());
        $message->setStatus($msg[0]->getStatus());
        $message->setToken($msg[0]->getToken());
        $message->setLink($msg[0]->getLink());
        $message->setReference($msg[0]->getReference());
        $message->setCreatedAt($msg[0]->getCreatedAt());
        $message->setHumanCreatedAt($msg[0]->getHumanCreatedAt());

        $this->messageComposeInterface->composeMessage(
            $messagingMessage,
            $this->messageUserInterface,
            //MessengerInterface::CHANNEL_SMS,
            MessengerInterface::CHANNEL_PUSH,
            MessengerInterface::CHANNEL_EMAIL
        );

        return $message;

    }

    /**
     * Compose conversation message
     * @param MessagingMessage $messagingMessage
     * @param User
     */
    public function composeConversationMessage(MessagingMessage $messagingMessage): ?Message
    {

        $message = new Message();
        /*$content = <<<MESSAGE
        we have listed your property
        MESSAGE;*/

        //$messagingMessage = new MessagingMessage();
        /**$recipient = $this->messageUserInterface;
        $recipient->setId($messagingMessage->getUser()->getId());
        $recipient->setName($messagingMessage->getUser()->getName());
        $recipient->setPhone($auth->getUser()->getPhone());
        $recipient->setEmail($auth->getUser()->getEmail());
        $recipient->setSubject(sprintf( sprintf( $auth->getUser()->getName(), "'s")?? "Your", " property listed."));
        $recipient->setContent(sprintf("%s %s%s %s '%s'%s", 'Hi', $messagingMessage->getUser()->getName(), ',',$content,$findPropertyById->getDescription(),'.'));

        $notifications[] = ->getUser()->getId();

        $messagingMessage->setRecipients($recipient);

        $messagingMessage->setRecipient($recipient);
        $this->messageUserInterface->setIds(...$notifications);*/

        $this->messageComposeInterface->composeMessage(
            $messagingMessage,
            $this->messageUserInterface,
            MessengerInterface::CHANNEL_SMS,
            MessengerInterface::CHANNEL_PUSH,
            MessengerInterface::CHANNEL_EMAIL
        );

        return $message;

    }

}
