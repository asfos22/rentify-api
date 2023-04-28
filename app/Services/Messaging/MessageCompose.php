<?php
namespace App\Services\Messaging;

use App\Http\Request\ParsedFilter;
use App\Repositories\Auth\Token;
use App\Repositories\Message\Push\PushMessage;
use App\Repositories\Message\Push\PushMessageRepositoryInterface;
use App\Repositories\User\Account;
use App\Repositories\User\User;
use Psr\Log\LoggerInterface;
use Exception;
use App\Repositories\Message\NotificationSettingManagerInterface;
use App\Repositories\Message\NotificationSetting;
use App\Repositories\Message\Contact\MessageContact;
use App\Repositories\System\Event\SystemEvent;
use App\Services\Messaging\Message as MessagingMessage;
use App\Services\Messaging\MessageInterface as MessageMessageInterface;
use App\Services\Messaging\MessageUserInterface;
use App\Repositories\Message\Message;
use App\Repositories\Message\MessagingServiceInterface;

/**
 * Messenger for sending push notification
 *
 * @author Asante Foster
 *        
 */
class MessageCompose implements MessageComposeInterface
{

    /**
     *
     * @var Messaging
     */
    private $client;

    /**
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     *
     * @var PushMessageRepositoryInterface
     */
    private $repository;

    /**
     *
     * @var NotificationSettingManagerInterface
     */
    private $manager;

    /**
     * @var
     */
    private $messenger;

    public function __construct(
        
       // Messaging $client, 
         PushMessageRepositoryInterface $repository,
         NotificationSettingManagerInterface $manager,
         MessagingServiceInterface $messenger,
         // LoggerInterface $logger)
    )
    {
        //$this->client = $client;

        $this->repository = $repository;

        $this->manager = $manager;

        $this->messenger = $messenger;

       // $this->logger = $logger;
    }

    
    /**
     * @param MessagingMessage $messagingMessage
     * @param MessageUserInterface $messageUserInterface,
     * @param string ...$channels
     * @return Message
     */
    public function composeMessage(MessagingMessage $messagingMessage,
                                   MessageUserInterface $messageUserInterface,
                                    string ...$channels ):?Message
    {
        $message = new Message();
        $receiver = new User();
       // $receiver->setId($messagingMessage->getRecipient()->getId());
       // $message->setReceiver($receiver);
        $messagingMessage->setData(true);
        //-- set channels
        $messagingMessage->setChannels(...$channels);
    
        //----
        //$messagingMessage->setEvent(SystemEvent::EVENT_AUTH_ACCOUNT_VERIFICATION);
       // $messagingMessage->setState(MessageMessageInterface::STATE_SUCCESS);

        //----
        //$sender = clone $messageUserInterface;
        //$messagingMessage->setSender($sender);


        // dump("SENDER 1", $messageUserInterface);

         
        //$messagingMessage->setSubject("subject content");
       // $messagingMessage->setContent("content content");

        $this->messenger->send($messageUserInterface, $messagingMessage);
        return $message;

    }

}

