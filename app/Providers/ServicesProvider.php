<?php

namespace App\Providers;

use App\Http\AccessControl\AccessManager;
use App\Http\AccessControl\AccessManagerInterface;
use App\Http\Response\JsonResponseWriter;
use App\Http\Response\ResponseWriterInterface;
use App\Repositories\Property\Amenity\AmenityRepository;
use App\Repositories\Property\Amenity\AmenityRepositoryInterface;
use App\Repositories\Auth\AuthRepository;
use App\Repositories\Auth\AuthRepositoryInterface;
use App\Repositories\Auth\Confirmation\ConfirmationRepository;
use App\Repositories\Auth\Confirmation\ConfirmationRepositoryInterface;
use App\Repositories\Auth\Verification\VerificationRepository;
use App\Repositories\Auth\Verification\VerificationRepositoryInterface;
use App\Repositories\Currency\CurrencyRepository;
use App\Repositories\Currency\CurrencyRepositoryInterface;
use App\Repositories\FCM\FCMInterface;
use App\Repositories\FCM\FCMRepository;
use App\Repositories\GeoCode\NomiNatim\NomiNatimGeoCodeRepositoryInterface;
use App\Repositories\Guest\GuestTokenInterface;
use App\Repositories\Guest\GuestTokenRepository;
use App\Repositories\Location\AddressInterface;
use App\Repositories\Location\AddressRepository;
use App\Repositories\Location\CountryRepository;
use App\Repositories\Location\CountryRepositoryInterface;
use App\Repositories\Location\GeoLocation;
use App\Repositories\Location\GeoLocationInterface;
use App\Repositories\Mail\MailRepository;
use App\Repositories\Media\MediaInterface;
use App\Repositories\Media\MediaRepository;
use App\Repositories\Message\Contact\MessageContact;
use App\Repositories\Message\Contact\MessageContactRepository;
use App\Repositories\Message\Contact\MessageContactRepositoryInterface;
use App\Repositories\Message\MessageInterface;
use App\Repositories\Message\MessageRepository;
use App\Repositories\Message\MessagingService as PMessagingService;
use App\Repositories\Message\MessagingServiceInterface as PMessagingServiceInterface;
use App\Repositories\Message\NotificationSettingManager;
use App\Repositories\Message\NotificationSettingManagerInterface;
use App\Repositories\Message\PServiceMessage;
use App\Repositories\Message\PServiceMessageInterface;
use App\Repositories\Message\Push\PushMessageRepository;
use App\Repositories\Message\Push\PushMessageRepositoryInterface;
//use App\Repositories\Messaging\ServiceMessage;
//use App\Repositories\Messaging\ServiceMessageInterface;
use App\Repositories\Messaging\RepoMessage;
use App\Repositories\NotificationToken\NotificationTokenInterface;
use App\Repositories\NotificationToken\NotificationTokenRepository;
use App\Repositories\Notification\NotificationInterface;
use App\Repositories\Notification\NotificationRepository;
use App\Repositories\Permission\PermissionRepository;
use App\Repositories\Permission\PermissionRepositoryInterface;
use App\Repositories\Property\Age\PropertyAgeRepository;
use App\Repositories\Property\Age\PropertyAgeRepositoryInterface;
use App\Repositories\Property\Category\CategoryRepository;
use App\Repositories\Property\Category\CategoryRepositoryInterface;
use App\Repositories\Property\PropertyInterface;
use App\Repositories\Property\PropertyRepository;
use App\Repositories\Property\Status\PropertyStatusRepository;
use App\Repositories\Property\Status\PropertyStatusRepositoryInterface;
use App\Repositories\Property\StayDuration\PropertyStayDurationRepository;
use App\Repositories\Property\StayDuration\PropertyStayDurationRepositoryInterface;
use App\Repositories\Property\TermsRules\TermsRulesRepository;
use App\Repositories\Property\TermsRules\TermsRulesRepositoryInterface;
use App\Repositories\Review\ReviewInterface;
use App\Repositories\Review\ReviewRepository;
use App\Repositories\Role\RoleRepository;
use App\Repositories\Role\RoleRepositoryInterface;
use App\Repositories\Session\SessionInterface;
use App\Repositories\Session\SessionRepository;
use App\Repositories\Tokens\TokenInterface;
use App\Repositories\Tokens\TokenRepository;
use App\Repositories\User\UserRepository;
use App\Repositories\User\UserRepositoryInterface;
use App\Services\Api\MailInterface;
use App\Services\GeoCode\NomiNatim\GeoCodeService;
use App\Services\Messaging\EmailMessenger;
use App\Services\Messaging\MessageCompose;
use App\Services\Messaging\MessageComposeInterface;
use App\Services\Messaging\Messages\Push\FcmInterface as PushFcmInterface;
use App\Services\Messaging\Messages\Push\FcmService;
use App\Services\Messaging\MessageUser;
use App\Services\Messaging\MessageUserInterface;
//use App\Services\Messaging\ServiceMessage;
//use App\Services\Messaging\ServiceMessageInterface;
use App\Services\Messaging\MessengerInterface;
use App\Services\Messaging\PushMessenger;
use App\Services\Messaging\SMSMessenger;
use App\Services\TaskRunner\AfterShutdownTaskRunner;
use App\Services\TaskRunner\TaskRunnerInterface;
use Illuminate\Support\ServiceProvider;

class ServicesProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

        $this->app->bind(AuthRepositoryInterface::class, AuthRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(VerificationRepositoryInterface::class, VerificationRepository::class);
        $this->app->bind(PropertyInterface::class, PropertyRepository::class);
        $this->app->bind(MessageInterface::class, MessageRepository::class);
        $this->app->bind(NotificationInterface::class, NotificationRepository::class);
        $this->app->bind(NomiNatimGeoCodeRepositoryInterface::class, GeoCodeService::class);
        $this->app->bind(TokenInterface::class, TokenRepository::class);
        $this->app->bind(MailInterface::class, MailRepository::class);
        $this->app->bind(FCMInterface::class, FCMRepository::class);
        $this->app->bind(AccessManagerInterface::class, AccessManager::class);
        $this->app->bind(SessionInterface::class, SessionRepository::class);
        $this->app->bind(MediaInterface::class, MediaRepository::class);
        $this->app->bind(ReviewInterface::class, ReviewRepository::class);
        $this->app->bind(PermissionRepositoryInterface::class, PermissionRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(ConfirmationRepositoryInterface::class, ConfirmationRepository::class);
        $this->app->bind(NotificationTokenInterface::class, NotificationTokenRepository::class);
        $this->app->bind(GuestTokenInterface::class, GuestTokenRepository::class);
        $this->app->bind(GeoLocationInterface::class, GeoLocation::class);
        $this->app->bind(CountryRepositoryInterface::class, CountryRepository::class);
        $this->app->bind(PMessagingServiceInterface::class, PMessagingService::class);
        $this->app->bind(MessageContactRepositoryInterface::class, MessageContactRepository::class);
        $this->app->bind(PServiceMessageInterface::class, PServiceMessage::class);
        $this->app->bind(PMessagingServiceInterface::class, PMessagingService::class);
        $this->app->bind(MessengerInterface::class, EmailMessenger::class);
        $this->app->bind(MessengerInterface::class, PushMessenger::class);
        $this->app->bind(MessengerInterface::class, SMSMessenger::class);
        $this->app->bind(RepoMessageInterface::class, RepoMessage::class);
        $this->app->bind(MessageUserInterface::class, MessageUser::class);
        $this->app->bind(MessageUserInterface::class, MessageContact::class);
        $this->app->bind(NotificationSettingManagerInterface::class, NotificationSettingManager::class);
        $this->app->bind(TaskRunnerInterface::class, AfterShutdownTaskRunner::class);
        $this->app->bind(PushMessageRepositoryInterface::class, PushMessageRepository::class);
        $this->app->bind(PushFcmInterface::class, FcmService::class);
        $this->app->bind(ResponseWriterInterface::class,  JsonResponseWriter::class);
        $this->app->bind(AmenityRepositoryInterface::class, AmenityRepository::class); 
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class); 
        $this->app->bind(PropertyStatusRepositoryInterface::class, PropertyStatusRepository::class); 
        $this->app->bind(PropertyAgeRepositoryInterface::class, PropertyAgeRepository::class); 
        $this->app->bind(CurrencyRepositoryInterface::class,  CurrencyRepository::class); 
        $this->app->bind(PropertyStayDurationRepositoryInterface::class,   PropertyStayDurationRepository::class); 
        $this->app->bind(TermsRulesRepositoryInterface::class,   TermsRulesRepository::class); 
        $this->app->bind(AddressInterface::class,    AddressRepository::class); 
        $this->app->bind(MessageComposeInterface::class, MessageCompose::class);
      
        /*$this->app->tag([TaskRunnerInterface::class,
            MessageInterface::class,
            MessengerInterface::class], 'interface');
        $this->app->bind('InterfaceAggregator', function ($app) {
            return new InterfaceAggregator($app->tagged('interface'));
        });*/

        

    }
}
