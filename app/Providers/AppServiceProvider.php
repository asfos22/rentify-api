<?php

namespace App\Providers;

use App\Http\Controllers\Message\MessageController;
use App\Repositories\Messaging\RepoMessage;
use App\Services\Messaging\Message;
use App\Services\Messaging\Messages\User\AccountForgottenPasswordMessage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->app->when(MessageController::class)
        ->needs(RepoMessage::class)
        ->give(AccountForgottenPasswordMessage::class);
       
       /* $this->app->when(EmailMessenger::class)
        ->needs(MessengerInterface::class)
        ->give(EmailMessenger::class);

        $this->app->when(PushController::class)
        ->needs(MessagingServiceInterface::class)
        ->give(MessagingService::class);


       // $this->app->bind(MessagingServiceInterface::class, MessagingService::class);

        $this->app->when(SMSController::class)
        ->needs(MessengerInterface::class)
        ->give(SMSMessenger::class);


        $this->app->when(MessageController::class)
        ->needs(MessagingServiceInterface::class)
        ->give(MessagingService::class);

       // MessagingService implements MessagingServiceInterface

       $this->app->bind(MessengerInterface::class, EmailMessenger::class);
        $this->app->bind(MessengerInterface::class, PushMessenger::class);
        $this->app->bind(MessengerInterface::class, SMSMessenger::class);

   /* $this->app->when(StripeController::class)
        ->needs(PaymentInterface::class)
        ->give(StripeService::class);

    $this->app->when(SquarePayController::class)
        ->needs(PaymentInterface::class)
        ->give(SquarePayService::class);*/
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        $this->app->register(ServicesProvider::class);
    }
}
