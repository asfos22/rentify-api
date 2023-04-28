<?php
declare(strict_types = 1);
namespace App\Services\Messaging\Messages\User;

use App\Repositories\User\Account;
use App\Services\Messaging\Message;
use App\Services\Messaging\MessengerInterface;

/**
 * Message to send to users upon first sign up
 *  @author Asante Foster         
 */

class UserSignUpMessage extends Message
{

    public function __construct(Account $user)
    {
        $content = <<<MESSAGE
        System testing. Please ignore.
        MESSAGE;

        $message = sprintf($content, $user->getName());

        $this->setSubject('System testing');
        $this->setContent($message);
        $this->setRecipient($user);

        $this->setChannels(MessengerInterface::CHANNEL_PUSH);
    }
}

