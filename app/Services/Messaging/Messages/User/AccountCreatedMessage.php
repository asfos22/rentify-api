<?php
declare(strict_types = 1);
namespace App\Services\Messaging\Messages\User;

use App\Services\Messaging\Message;
use App\Repositories\User\Account;
use App\Repositories\System\Event\SystemEvent;
use App\Services\Messaging\MessageInterface;
use App\Services\Messaging\MessengerInterface;

/**
 * @author Asante Foster       
 */
class AccountCreatedMessage extends Message
{

    public function __construct(Account $account, string $activationCode)
    {
        $this->setEvent(SystemEvent::EVENT_USER_CREATED);
        $this->setState(MessageInterface::STATE_INFORMATION);
        $this->setChannels(MessengerInterface::CHANNEL_SMS, MessengerInterface::CHANNEL_EMAIL);

        $this->setRecipient($account);
        $this->setSubject('Account account registraion');

        $content = <<<MESSAGE
        Hello %s, you have successfully registered an account with Rentify. Your account activation code is "%s" \nThank you.
        MESSAGE;

        $message = sprintf($content, trim($account->getName()), $activationCode);

        $this->setContent($message);
    }
}

