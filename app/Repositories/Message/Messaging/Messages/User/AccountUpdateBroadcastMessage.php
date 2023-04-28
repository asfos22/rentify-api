<?php
namespace App\Repositories\Messaging\Messages\User;

use App\Services\Messaging\Message;
use App\Repositories\User\Account;
use App\Repositories\System\Event\SystemEvent;
//use App\Repositories\Messaging\MessengerInterface;
use App\Services\Messaging\MessageInterface;
use App\Services\Messaging\MessengerInterface;

//use App\Repositories\Messaging\MessageInterface;

/**
 * Message broadcasted to all accounts when a user account is updated
 *
 * @author Asante Foster
 *        
 */
class AccountUpdateBroadcastMessage extends Message
{

    public function __construct(Account $account)
    {
        $this->setEvent(SystemEvent::EVENT_USER_UPDATED);
        $this->setState(MessageInterface::STATE_INFORMATION);
        $this->setChannels(MessengerInterface::CHANNEL_PUSH);

        $this->setSubject('User account updated');

        $content = <<<MESSAGE
        Account belonging to %s has been updated.
        MESSAGE;

        $message = sprintf($content, $account->getName());

        $this->setContent($message);

        $this->setPayload(array(
            array(
                'item' => $account->getId()
            )
        ));
    }
}

