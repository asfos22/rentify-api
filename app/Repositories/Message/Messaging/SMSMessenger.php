<?php
declare(strict_types = 1);
namespace App\Repositories\Message\Messaging;

use App\Http\Request\ParsedFilter;
use App\Repositories\Message\MessageRepositoryInterface;
use App\Repositories\Message\Push\PushMessage;
use App\Repositories\User\Account;
use App\Repositories\User\User;
use Psr\Log\LoggerInterface;
use Exception;

/**
 * Messenger for sending SMS
 *
 * @author Asante Foster
 *        
 */
class SMSMessenger implements MessengerInterface
{

    /**
     *
     * @var MessageRepositoryInterface
     */
    private $repository;

    /**
     *
     * @var LoggerInterface;
     */
    private $logger;

    /**
     *
     * @var Client
     */
    private $client;

    /**
     *
     * @var string
     */
    private $from;

    /**
     * Tells if this service should fake sending SMS.
     * This is useful for testing
     *
     * @var bool
     */
    private $fake;

    public function __construct(Client $client, string $from, MessageInterface $repository, LoggerInterface $logger, bool $fake = false)
    {
        $this->client = $client;

        $this->from = $from;

        $this->logger = $logger;

        $this->fake = $fake;

        $this->repository = $repository;
    }

    /**
     *
     * {@inheritdoc}
     * @see \Api\Services\Messaging\MessengerInterface::send()
     */
    public function send(MessageInterface ...$messages): bool
    {
        if ($this->fake) {
            return true;
        }

        foreach ($messages as $message) {
            $recipient = $message->getRecipient() ? $message->getRecipient()->getPhone() : null;

            if (! $recipient || ! $message->getContent()) {
                continue;
            }

            $sender = $message->getSender() ? $message->getSender()->getPhone() : null;

            try {
                $this->dispatch($message->getContent(), $recipient, $message->getSubject(), $sender);
            } catch (Exception $e) {
                $this->logger->warning('SMS Exception: ' . $e->getMessage());
            }
        }

        return true;
    }

    /**
     * Sends a message and returns Id of sent message
     *
     * @param string $message
     * @param string $recipient
     * @param string $subject
     * @param string $sender
     * @return string
     */
    private function dispatch(string $message, string $recipient, string $subject = null, string $sender = null): string
    {
        $content = array(
            'from' => $this->from,
            'body' => $message
        );

        $sentMessage = $this->client->messages->create($recipient, $content);

        // Record messages sent between users
        if ($sender && $sender !== $this->from) {
            $this->repository->createByPhones($sender, $recipient, $message, $sentMessage->sid, $subject);
        }

        return $sentMessage->sid;
    }

    /**
     *
     * {@inheritdoc}
     * @see \Api\Services\Messaging\MessengerInterface::broadcast()
     */
    public function broadcast(string $topic, MessageInterface $message, MessageUserInterface ...$recipients): bool
    {
        if ($this->fake) {
            return true;
        }

        if (! count($recipients)) {
            return true;
        }

        return true; // DISABLE BULK SMS VIA TOPIC ENTIRELY

        try {
            foreach ($recipients as $recipient) {
                if (! $recipient->getPhone() || ! $message->getContent()) {
                    continue;
                }

                $sender = $message->getSender() ? $message->getSender()->getPhone() : null;

                $this->dispatch($message->getContent(), $recipient->getPhone(), $message->getSubject(), $sender);
            }
        } catch (Exception $e) {
            $this->logger->warning('SMS Exception: ' . $e->getMessage());
            return false;
        }

        return true;
    }

    /**
     *
     * {@inheritdoc}
     * @see \Api\Services\Messaging\MessengerInterface::getChannel()
     */
    public function getChannel(): string
    {
        return self::CHANNEL_SMS;
    }

    /**
     *
     * {@inheritdoc}
     * @see \Api\Services\Messaging\MessengerInterface::subscribe()
     */
    public function subscribe(User $user, array $subscription): void
    {}

    /**
     *
     * {@inheritdoc}
     * @see \Api\Services\Messaging\MessengerInterface::unsubscribe()
     */
    public function unsubscribe(User $user, array $unsubscription): void
    {}

    /**
     *
     * {@inheritdoc}
     * @see \Api\Services\Messaging\MessengerInterface::fetchForUser()
     */
    public function fetchForUser(Account $user, ParsedFilter $filter): array
    {
        return [];
    }

    /**
     *
     * {@inheritdoc}
     * @see \Api\Services\Messaging\MessengerInterface::countForUser()
     */
    public function countForUser(Account $user, FilterCollection $filters): int
    {
        return 0;
    }

    /**
     *
     * {@inheritdoc}
     * @see \Api\Services\Mesaging\MessengerInterface::findForUser()
     */
    public function findForUser(Account $user, int $message, FieldCollection $fields): ?PushMessage
    {
        return null;
    }

    /**
     *
     * {@inheritdoc}
     * @see \Api\Services\Messaging\MessengerInterface::markMessageAsRead()
     */
    public function markMessageAsRead(int $message): void
    {}
}

