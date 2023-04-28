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
class AccountForgottenPasswordMessage extends Message
{

    public function __construct(Account $account, string $password)
    {
        $this->setEvent(SystemEvent::EVENT_AUTH_PASSWORD_RESET);
        $this->setState(MessageInterface::STATE_SUCCESS);
        $this->setChannels(MessengerInterface::CHANNEL_SMS, MessengerInterface::CHANNEL_EMAIL);

        $this->setRecipient($account);

        $this->setSubject('Account password reset');

        $content = <<<MESSAGE
        Your new KLLOYDS account password is %s .\nThank you.
        MESSAGE;

        $this->setContent(sprintf($content, $password));
    }
}

