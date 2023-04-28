<?php
declare(strict_types = 1);
namespace App\Services\Messaging\Messages\User;

use App\Repositories\System\Event\SystemEvent;
use App\Repositories\User\User;
use App\Services\Messaging\Message;
use App\Services\Messaging\MessageInterface;
use App\Services\Messaging\MessengerInterface;

/**
 * Message broadcasted to admin when a user account is register
 * @author Asante Foster       
 */
class UserRegistrationBroadcastMessage extends Message
{

    public function __construct(User $user)
    {
        $role = $user->getRole() ? $user->getRole()->getName() : '';
        $name = $user->getName();
        
        $this->setEvent(SystemEvent::EVENT_USER_CREATED);
        $this->setState(MessageInterface::STATE_INFORMATION);
        $this->setChannels(MessengerInterface::CHANNEL_PUSH);

        $this->setSubject('User registration');

        $content = <<<MESSAGE
        A new %s account by name %s has been registered.
        MESSAGE;

        $message = sprintf($content, $role, $name);

        $this->setContent($message);

        $this->setPayload(array(
            array(
                'item' => $user->getId()
            )
        ));
    }
}

