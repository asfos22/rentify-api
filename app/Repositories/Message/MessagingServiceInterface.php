<?php
declare(strict_types = 1);
namespace App\Repositories\Message;

use App\Repositories\Message\Push\PushMessage;
use App\Repositories\User\User;
use App\Repositories\User\Account;

//use App\Repositories\Message\Push\PushMessage;
//use App\Services\Messaging\MessageInterface as MessagingMessageInterface;
use App\Services\Messaging\MessageInterface;
use App\Services\Messaging\MessageUserInterface;

interface MessagingServiceInterface
{

    /**
     * Sends messages
     *
     * @param MessageInterface ...$messages
     */
    public function send(MessageUserInterface $messageUserInterface,MessageInterface ...$messages): void;

        /**
         * Broadcast a single message to a topic
         *
         * @param string $topic
         * @param MessageInterface $message
         */
        public function broadcast(string $topic, MessageInterface $message): void;
    
        /**
         * Subscribes for messaging from a given channel
         *
         * @param User $user
         * @param string $channel
         *            One of 'SMS', 'PUSH', 'EMAIL'
         * @param array $subscription
         */
        public function subscribe(User $user, string $channel, array $subscription): void;
    
        /**
         * Unsubscribes for messaging from a given channel
         *
         * @param User $user
         * @param string $channel
         *            One of 'SMS', 'PUSH', 'EMAIL'
         * @param array $unsubscription
         */
        public function unsubscribe(User $user, string $channel, array $unsubscription): void;
    
        /**
         * Counts messages for a given user for a given channel
         *
         * @param string $channel
         * @param Account $user
         * @param FilterCollection $filters
         * @return int
         */
        public function countForUser(string $channel, Account $user, /*FilterCollection $filters*/): int;
    
        /**
         * Fetches messages for a given user for a given channel
         *
         * @param string $channel
         * @param Account $user
         * @param ParsedFilter $filter
         * @return PushMessage[]
         */
        
         public function fetchForUser(string $channel, Account $user): array;
    
        /**
         * Fetches a single message for a given user on a given channel
         *
         * @param string $channel
         * @param Account $user
         * @param int $message
         * @param FieldCollection $fields
         * @return PushMessage|NULL
         */
        public function findForUser(string $channel, Account $user, int $message): ?PushMessage;
    
        /**
         * Marsk a message as read on a given channel
         *
         * @param string $channel
         * @param PushMessage $message
         */
        public function markMessageAsRead(string $channel, PushMessage $message): void;
    

}

