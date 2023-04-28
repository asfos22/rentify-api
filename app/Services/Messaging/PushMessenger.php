<?php
namespace App\Services\Messaging;

use App\Http\Request\ParsedFilter;
use App\Repositories\Auth\Token;
use App\Repositories\Message\Push\PushMessage;
use App\Repositories\Message\Push\PushMessageRepositoryInterface;
use App\Repositories\User\Account;
use App\Repositories\User\User;
use Psr\Log\LoggerInterface;
use Exception;
use App\Repositories\Message\NotificationSettingManagerInterface;
use App\Repositories\Message\NotificationSetting;
use App\Repositories\Message\Contact\MessageContact;

/**
 * Messenger for sending push notification
 *
 * @author Asante Foster
 *        
 */
class PushMessenger implements MessengerInterface
{

    /**
     *
     * @var Messaging
     */
    private $client;

    /**
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     *
     * @var PushMessageRepositoryInterface
     */
    private $repository;

    /**
     *
     * @var NotificationSettingManagerInterface
     */
    private $manager;

    public function __construct(
        
       // Messaging $client, 
        PushMessageRepositoryInterface $repository,
         NotificationSettingManagerInterface $manager
         // LoggerInterface $logger)
    )
    {
        //$this->client = $client;

        $this->repository = $repository;

        $this->manager = $manager;

       // $this->logger = $logger;
    }

    /**
     * Maps message recipients to their push tokens.
     * The return tokens array has the user Ids as keys and the corresponding token objects as values
     *
     * @param MessageInterface ...$messages
     * @see \App\Repositories\Message\Push\PushMessageRepositoryInterface::loadUsersTokensByUserIds()
     */
    private function mapRecipientsToPushTokens(MessageInterface ...$messages): array
    {
        $recipientIds = [];

        foreach ($messages as $message) {
            if ($message->getRecipient() && $message->getRecipient()->getId()) {
                $recipientIds[] = $message->getRecipient()->getId();
            }
        }

        if (! count($recipientIds)) {
            return [];
        }

        return $this->repository->loadUsersTokensByUserIds(...$recipientIds);
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Services\Messaging\MessengerInterface::send()
     */
    public function send(MessageInterface ...$messages): bool
    {
        if (! count($messages)) {
            return false;
        }

        $cloudMessages = [];

        foreach ($messages as $message) {
            $recipient = $message->getRecipient();

            if (! ($recipient instanceof MessageContact)) {
                continue;
            }

            foreach ($recipient->getPushTokens() as $token) {
                if (empty($token)) {
                    continue;
                }

                $data = array(
                    'event' => $message->getEvent(),
                    'state' => $message->getState(),
                    'payload' => json_encode($message->getPayload())
                );

                $notification = Notification::create($message->getSubject(), $message->getContent(), $message->getImage());

                $cloudMessage = CloudMessage::withTarget('token', $token)->withNotification($notification)->withData($data);

                if ($message->isData()) {
                    $cloudMessage = CloudMessage::withTarget('token', $token)->withData($data);
                }

                $cloudMessages[] = $cloudMessage;
            }
        }

        if (! count($cloudMessages)) {
            return true;
        }

        try {
            $invalidTokens = [];

            foreach (array_chunk($cloudMessages, 500) as $batch) {
                foreach ($this->client->sendAll($batch)->getItems() as $response) {
                    if ($response->isFailure()) {
                        
                        if ($response->messageTargetWasInvalid() || $response->messageWasSentToUnknownToken()) {
                            $invalidTokens[] = $response->target()->value();
                            continue;
                        }

                        $exception = $response->error();
                        $this->logger->warning($exception->getMessage(), array(
                            'trace' => $exception->getTraceAsString()
                        ));
                    }
                }
            }

            $this->repository->createMany(...$messages);

            if (count($invalidTokens)) {
                $this->repository->invalidateTokens(...array_unique($invalidTokens));
            }

            return true;
        } catch (Exception $e) {
            $this->logger->warning($e->getMessage(), array(
                'trace' => $e->getTraceAsString()
            ));
        }

        return false;
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Services\Messaging\MessengerInterface::broadcast()
     */
    public function broadcast(
        string $topic, MessageInterface $message, MessageUserInterface ...$recipients): bool
    {
        if (! count($recipients)) {
            return false;
        }

        $cloudMessages = [];

        foreach ($recipients as $recipient) {
            if (! ($recipient instanceof MessageContact)) {
                continue;
            }

            if (! count($recipient->getPushTokens())) {
                continue;
            }

            foreach ($recipient->getPushTokens() as $token) {
                if (empty($token)) {
                    continue;
                }

                $notification = Notification::create($message->getSubject(), $message->getContent(), $message->getImage());

                $data = array(
                    'event' => $message->getEvent(),
                    'state' => $message->getState(),
                    'payload' => json_encode($message->getPayload())
                );

                $cloudMessage = CloudMessage::withTarget('token', $token);

                if ($message->isData()) {
                    $cloudMessage = $cloudMessage->withData($data);
                } else {
                    $cloudMessage = $cloudMessage->withNotification($notification)->withData($data);
                }

                $cloudMessage = $cloudMessage->withAndroidConfig($this->buildAndroidConfig($message))
                    ->withApnsConfig($this->buildIOSConfig($message))
                    ->withWebPushConfig($this->buildWebConfig($message));

                $cloudMessages[] = $cloudMessage;
            }
        }

        if (! count($cloudMessages)) {
            return false;
        }

        try {
            $invalidTokens = [];

            foreach (array_chunk($cloudMessages, 500) as $batch) {
                foreach ($this->client->sendAll($batch)->getItems() as $response) {
                    if ($response->isFailure()) {
                        
                        if ($response->messageTargetWasInvalid() || $response->messageWasSentToUnknownToken()) {
                            $invalidTokens[] = $response->target()->value();
                            continue;
                        }

                        $exception = $response->error();
                        $this->logger->warning($exception->getMessage(), array(
                            'trace' => $exception->getTraceAsString()
                        ));
                    }
                }
            }

            if (count($invalidTokens)) {
                $this->repository->invalidateTokens(...array_unique($invalidTokens));
            }

            return true;
        } catch (Exception $e) {
            $this->logger->warning($e->getMessage(), array(
                'trace' => $e->getTraceAsString()
            ));

            return false;
        }

        /*
         * try {
         * $notification = Notification::create($message->getSubject(), $message->getContent(), $message->getImage());
         *
         * $data = array(
         * 'event' => $message->getEvent(),
         * 'state' => $message->getState(),
         * 'payload' => json_encode($message->getPayload())
         * );
         *
         * $cloudMessage = CloudMessage::withTarget('topic', $topic)->withNotification($notification)->withData($data);
         *
         * if ($message->isData()) {
         * $cloudMessage = CloudMessage::withTarget('topic', $topic)->withData($data);
         * }
         *
         * $cloudMessage = $cloudMessage->withAndroidConfig($this->buildAndroidConfig($message))
         * ->withApnsConfig($this->buildIOSConfig($message))
         * ->withWebPushConfig($this->buildWebConfig($message));
         *
         * $this->client->send($cloudMessage);
         *
         * return true;
         * } catch (Exception $e) {
         * $this->logger->warning($e->getMessage(), array(
         * 'trace' => $e->getTraceAsString()
         * ));
         *
         * return false;
         * }
         */
    }

    /**
     * Builds Android specific message configuration options
     *
     * @param MessageInterface $message
     * @return array
     */
    private function buildAndroidConfig(MessageInterface $message): array
    {
        return array(
            'priority' => 'high',
            'ttl' => sprintf('%ds', $message->getLifeTime())
        );
    }

    /**
     * Builds iOS specific message configuration options
     *
     * @param MessageInterface $message
     * @return array
     */
    private function buildIOSConfig(MessageInterface $message): array
    {
        return array(
            'headers' => array(
                'apns-priority' => '10',
                'apns-expiration' => sprintf('%d', $message->getLifeTime())
            )
        );
    }

    /**
     * Builds web specific message configuration options
     *
     * @param MessageInterface $message
     * @return array
     */
    private function buildWebConfig(MessageInterface $message): array
    {
        return array(
            'headers' => array(
                'Urgency' => 'high',
                'TTL' => sprintf('%d', $message->getLifeTime())
            )
        );
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Services\Messaging\MessengerInterface::getChannel()
     */
    public function getChannel(): string
    {
        return self::CHANNEL_PUSH;
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Services\Messaging\MessengerInterface::subscribe()
     */
    public function subscribe(User $user, array $subscription): void
    {
        $authToken = $subscription["auth_token"] ?? null;
        $pushToken = $subscription['push_token'] ?? null;

        if (! ($authToken instanceof Token)) {
            $message = sprintf('Subscription parameter passed to "%s" channel must contain an "auth_token" field which must be an instance of %s.', $this->getChannel(), Token::class);
            throw new Exception($message, 500);
        }

        if (empty($pushToken) || ! is_string($pushToken)) {
            $message = sprintf('Subscription parameter passed to "%s" channel must contain a "push_token" field which must be a string.', $this->getChannel());
            throw new Exception($message, 500);
        }

        $this->repository->subscribe($authToken, $pushToken);

        // Subscribe user to push notification channels for his/her roles
        if (! $authToken->isPushEnabled()) {
            $setting = new NotificationSetting();

            try {
                $setting->setPush(true);

                if ($mappedToken = $this->repository->loadUserTokenByUserId($user->getId())) {

                    foreach ($mappedToken->getTopics() as $topic) {
                        $this->client->subscribeToTopic($topic, $pushToken);
                    }

                    // Update notification settings
                    $this->manager->setForAuthToken($authToken->getId(), $setting);
                }
            } catch (Exception $e) {
                // Roll back notification settings
                $setting->setPush(false);
                $this->manager->setForAuthToken($authToken->getId(), $setting);

                $this->logger->warning($e->getMessage(), array(
                    'trace' => $e->getTraceAsString()
                ));
            }
        }
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Services\Messaging\MessengerInterface::unsubscribe()
     */
    public function unsubscribe(User $user, array $unsubscription): void
    {
        $authToken = $unsubscription["auth_token"] ?? null;

        if (! ($authToken instanceof Token)) {
            $message = sprintf('Unsubscription parameter passed to "%s" channel must contain an "auth_token" field which must be an instance of %s.', $this->getChannel(), Token::class);
            throw new Exception($message, 500);
        }

        $this->repository->unsubscribe($authToken);
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Services\Messaging\MessengerInterface::fetchForUser()
     */
    public function fetchForUser(Account $user, ParsedFilter $filter): array
    {
        return $this->repository->fetchForUser($user->getId(), $filter->getLimit(), $filter->getOffset(), $filter->getSelections(), $filter->getFilters(), $filter->getOrderings());
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Services\Messaging\MessengerInterface::countForUser()
     */
    public function countForUser(Account $user, FilterCollection $filters): int
    {
        return $this->repository->countForUser($user->getId(), $filters);
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Services\Messaging\MessengerInterface::findForUser()
     */
    public function findForUser(Account $user, int $message, FieldCollection $fields): ?PushMessage
    {
        return $this->repository->findForUser($user->getId(), $message, $fields);
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Services\Messaging\MessengerInterface::markMessageAsRead()
     */
    public function markMessageAsRead(int $message): void
    {
        $this->repository->markAsRead($message);
    }
}

