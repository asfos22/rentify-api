<?php
declare(strict_types = 1);
namespace App\Repositories\Message\Messaging\Messages\User;

use App\Repositories\Message\MessageInterface as MessageMessageInterface;
use App\Repositories\Messaging\Message;
use App\Repositories\User\Account;
use App\Repositories\System\Event\SystemEvent;
use App\Repositories\Messaging\MessageInterface;
use App\Repositories\Messaging\MessengerInterface;
use App\Repositories\Messaging\RepoMessage;
use App\Repositories\Messaging\RMessage;
use App\Services\Messaging\Message as MessagingMessage;

/**
 * Message sent to user when a forgotten password reset is requested
 *
 * @author Asante Foster
 *        
 */
class RAccountForgottenPasswordMessage extends MessagingMessage
{

    public function __construct()
    {
        $this->setEvent(SystemEvent::EVENT_AUTH_PASSWORD_RESET);
        $this->setState(""/*MessageMessageInterface::STATE_SUCCESS*/);
        $this->setChannels(MessengerInterface::CHANNEL_SMS, MessengerInterface::CHANNEL_EMAIL);

       // $this->setRecipient($account);

        $this->setSubject('Account password reset');

        $content = <<<MESSAGE
        Your new Rentify account password is %s .\nThank you.
        MESSAGE;

        $this->setContent(sprintf($content, $password));
    }
}

