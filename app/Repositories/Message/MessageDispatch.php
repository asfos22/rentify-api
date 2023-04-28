<?php
declare (strict_types = 1);
namespace App\Repositories\Message;

use App\Repositories\Message\Contact\MessageContactRepositoryInterface;

class MessageDispatch

{

    private $contactRepo;

    // class EmailMessenger implements MessengerInterface

    public function __construct(
       
        // TaskRunnerInterface $runner,
        MessageContactRepositoryInterface $contactRepo,
       // MessageUserInterface $recipient,
       // PushMessageRepositoryInterface $pushMessageRepositoryInterface,
       // NotificationSettingManagerInterface $notificationSettingManagerInterface,
       // FcmInterface $fcmInterface,
       // MessengerInterface...$handlers,
        //MessengerInterface  $messengerInterface ,
        

   ) {
       // $this->connection = DB::connection()->getPdo();
        // $this->runner = $runner;

        $this->contactRepository = $contactRepo;
        /*$this->recipient = $recipient;
        $this->pushMessageRepositoryInterface = $pushMessageRepositoryInterface;
        $this->notificationSettingManagerInterface = $notificationSettingManagerInterface;
        $this->fcmInterface = $fcmInterface;
        $this->messengerInterface = $handlers;
        //$this->messengerInterface  = $messengerInterface ;
        foreach ($handlers as $handler) {
            $this->handlers[$handler->getChannel()] = $handler;
        }*/
   }

    /**
     *
     * {@inheritdoc}
     * @see \App\Services\Messaging\MessagingServiceInterface::send()
     */
    public function send(/*MessageInterface...$messages*/): void
    {

        dump("Send Message");

    }

}
