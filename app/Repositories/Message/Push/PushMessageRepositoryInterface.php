<?php
declare(strict_types = 1);
namespace App\Repositories\Message\Push;

use App\Repositories\Auth\Token;
use App\Repositories\Message\MessageInterface as MessageMessageInterface;
use App\Repositories\DateTime;
use App\Repositories\Repository;
use PDO;
use App\Services\Messaging\MessageInterface;
use Exception;
use App\Repositories\IDModel;

interface PushMessageRepositoryInterface

{

    /**
     * Returns number of messages matching given filters
     *
     * @param FilterCollection $filters
     * @return int
     */
    public function count(/*FilterCollection $filters*/): int;

    /**
     * Fetches a number of messages
     *
     * @param int $limit
     * @param int $offset
     * @param FieldCollection $fields
     * @param FilterCollection $filters
     * @param OrderCollection $ordering
     * @return PushMessage[]
     */
    public function fetch(int $limit, int $offset, /*FieldCollection $fields, FilterCollection $filters, OrderCollection $ordering*/): array;

    /**
     * Finds a single messages by Id
     *
     * @param int $id
     * @param FieldCollection $fields
     * @return PushMessage|NULL
     */
    public function find(int $id, /*FieldCollection $fields*/): ?PushMessage;

    /**
     * Returns number of messages matching given filters for a given user
     *
     * @param int $user
     * @param FilterCollection $filters
     * @return int
     */
    public function countForUser(int $user, /*FilterCollection $filters*/): int;

    /**
     * Fetches a number of messages for a given user
     *
     * @param int $user
     * @param int $limit
     * @param int $offset
     * @param FieldCollection $fields
     * @param FilterCollection $filters
     * @param OrderCollection $ordering
     * @return PushMessage[]
     */
    public function fetchForUser(int $user, int $limit, int $offset/*, FieldCollection $fields, FilterCollection $filters, OrderCollection $ordering*/): array;

    /**
     * Finds a single message by Id for a given user
     *
     * @param int $user
     * @param int $id
     * @param FieldCollection $fields
     * @return PushMessage|NULL
     */
    public function findForUser(int $user, int $id /*FieldCollection $fields*/): ?PushMessage;

    /**
     * Creates a single message
     *
     * @param MessageInterface $message
     * @return int
     */
    public function create(MessageInterface $message): int;

    /**
     * Creates a multiple messages
     *
     * @param MessageInterface ...$messages
     * @return int[]
     */
    public function createMany(MessageInterface ...$messages): array;

    /**
     * Subscribe to push notification for a given authentication/session token
     *
     * @param Token $auth_token
     * @param string $push_registration_token
     */
    public function subscribe(Token $auth_token, string $push_registration_token): void;

    /**
     * Unsubscribe to push notification for a given authentication/session token
     *
     * @param Token $auth_token
     */
    public function unsubscribe(Token $auth_token): void;

    /**
     * Marks a message as read
     *
     * @param int $message
     * @return int
     */
    public function markAsRead(int $message): int;

    /**
     * Load push tokens a list of user Ids.
     * The returned push tokens array has the user Ids as keys and the token objects as values
     *
     * @param int ...$ids
     * @return MappedPushToken[]
     */
    public function loadUsersTokensByUserIds(int ...$ids): array;

    /**
     * Load push tokens for a given user id.
     *
     * @param int $id
     * @return MappedPushToken | NULL
     */
    public function loadUserTokenByUserId(int $id): ?MappedPushToken;

    /**
     * Loads Ids of users who have subscribed to a given topic
     *
     * @param string $topic
     * @return int[]
     */
    public function loadUserIdsByBroadcastTopic(string $topic): array;

    /**
     * Invalidates a set of tokens
     *
     * @param string ...$tokens
     */
    public function invalidateTokens(string ...$tokens): void;
}

