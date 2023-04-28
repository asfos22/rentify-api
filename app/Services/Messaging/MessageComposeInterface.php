<?php
declare(strict_types = 1);
namespace App\Services\Messaging;
use App\Services\Messaging\Message as MessagingMessage;
use App\Services\Messaging\MessageInterface as MessageMessageInterface;
use App\Services\Messaging\MessageUserInterface;
use App\Repositories\Message\Message;

/**
 *
 * @author Asante Foster
 *        
 */
interface MessageComposeInterface
{

    /**
     * @param MessagingMessage $messagingMessage
     * @param  MessageUserInterface $messageUserInterface,
     * @param string ...$channels
     * @return Message
     */
    //public function getId(): ?int;
    public function composeMessage(MessagingMessage $messagingMessage, MessageUserInterface $messageUserInterface, string ...$channels ):?Message;

  
  
}

