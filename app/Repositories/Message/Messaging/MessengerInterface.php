<?php
declare(strict_types = 1);
namespace App\Repositories\Message\Messaging;

//use App\Http\Request\ParsedFilter;
use App\Repositories\Message\Push\PushMessage;
use App\Repositories\User\Account;
use App\Repositories\User\User;
use App\Repositories\Message\Contact\MessageContact;
use App\Services\Messaging\MessageInterface;
use App\Services\Messaging\MessageUserInterface;

/**
 *
 * @author Asante Foster
 *        
 */
interface MessengerInterface
{

    /**
     * SMS channel
     *
     * @var string
     */
    const CHANNEL_SMS = 'SMS';

    /**
     * EMAIL channel
     *
     * @var string
     */
    const CHANNEL_EMAIL = 'EMAIL';

    /**
     * PUSH notification channel
     *
     * @var string
     */
    const CHANNEL_PUSH = 'PUSH';

    /**
     * Returns messaging channel (SMS, EMAIL, PUSH) handled by this messenger
     *
     * @return string
     */
    public function getChannel(): string;

    /**
     * Sends messages
     *
     * @param MessageInterface ...$messages
     * @return bool
     */
    public function send(MessageInterface ...$messages): bool;

    /**
     * Broadcasts a message to a list of contacts
     *
     * @param string $topic
     * @param MessageInterface $message
     * @param MessageUserInterface ...$recipients
     * @return bool
     */
    public function broadcast(string $topic, MessageInterface $message, MessageUserInterface ...$recipients): bool;

    /**
     * Subscribes for messaging
     *
     * @param User $user
     * @param array $subscription
     */
    public function subscribe(User $user, array $subscription): void;

    /**
     * Unsubscribes for messaging
     *
     * @param User $user
     * @param array $unsubscription
     */
    public function unsubscribe(User $user, array $unsubscription): void;

    /**
     * Counts messages for a given user
     *
     * @param Account $user
     * @param FilterCollection $filters
     * @return int
     */
    public function countForUser(Account $user, /*FilterCollection $filters*/): int;

    /**
     * Fetches messages for a given user
     *
     * @param Account $user
     * @param ParsedFilter $filter
     * @return PushMessage[]
     */
    public function fetchForUser(Account $user, /*ParsedFilter $filter*/): array;

    /**
     * Finds a single message for a given user
     *
     * @param Account $user
     * @param int $message
     * @param FieldCollection $fields
     * @return PushMessage|NULL
     */
    public function findForUser(Account $user, int $message, /*FieldCollection $fields*/): ?PushMessage;

    /**
     * Marks a message as read
     *
     * @param int $message
     */
    public function markMessageAsRead(int $message): void;
}

