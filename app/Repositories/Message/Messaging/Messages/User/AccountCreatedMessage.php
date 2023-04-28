<?php
declare(strict_types = 1);
namespace App\Repositories\Message\Messaging\Messages\User;

use App\Repositories\System\Event\SystemEvent as EventSystemEvent;
use App\Repositories\User\Account as UserAccount;
use App\Services\Messaging\Message;
use App\Services\User\Account;
use App\Services\System\Event\SystemEvent;
use App\Services\Messaging\MessageInterface;
use App\Services\Messaging\MessengerInterface;

/**
 * Message sent to user upon registration.
 * This message contains account activation code for the new user
 *
 * @autho Asante Foster
 *        
 */
class AccountCreatedMessage extends Message
{

    public function __construct(UserAccount $account, string $activationCode)
    {
        $this->setEvent(EventSystemEvent::EVENT_USER_CREATED);
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

