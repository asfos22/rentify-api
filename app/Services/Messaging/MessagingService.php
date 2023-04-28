<?php
declare(strict_types = 1);
namespace App\Services\Messaging;

use App\Http\Request\ParsedFilter;
use App\Repositories\Message\Push\PushMessage;
use App\Repositories\User\Account;
use App\Repositories\User\User;
use Exception;
use App\Services\TaskRunner\TaskRunnerInterface;
use App\Repositories\Message\Contact\MessageContactRepositoryInterface;

class MessagingService implements MessagingServiceInterface
{

    /**
     *
     * @var TaskRunnerInterface
     */
    private $runner;

    /**
     *
     * @var MessageContactRepositoryInterface
     */
    private $contactRepository;

    /**
     * Messaging/channel handlers
     *
     * @var MessengerInterface[]
     */
    private $handlers = [];

    public function __construct(TaskRunnerInterface $runner, MessageContactRepositoryInterface $contactRepo, MessengerInterface ...$handlers)
    {
        $this->runner = $runner;

        $this->contactRepository = $contactRepo;

        foreach ($handlers as $handler) {
            $this->handlers[$handler->getChannel()] = $handler;
        }
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Services\Messaging\MessagingServiceInterface::broadcast()
     */
    public function broadcast(string $topic, MessageInterface $message): void
    {
        $contacts = $this->contactRepository->fetchByTopics($topic);

        if (! count($contacts)) {
            return;
        }

        $this->runner->run(function () use ($topic, $message, $contacts) {
            $mappings = $this->mapMessagesToHandlers($message);

            foreach ($mappings as $channel => $mappedMessages) {
                $handler = $this->handlers[$channel] ?? null;

                if (! $handler) {
                    $this->throwHandlerNotFoundException($channel);
                }

                foreach ($mappedMessages as $curMessage) {
                    $handler->broadcast($topic, $curMessage, ...$contacts);
                }
            }
        });
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Services\Messaging\MessagingServiceInterface::send()
     */
    public function send(MessageInterface ...$messages): void
    {  
        $recipientIds = array_filter(array_map(function (MessageInterface $message) {
            return $message->getRecipient() ? $message->getRecipient()->getId() : null;
        }, $messages));

        if (! count($recipientIds)) {
            return;
        }

        // Some message handlers, currently only the push messager, need additional contact information like
        // So we load and provide them as additional contact information
        $contacts = $this->contactRepository->fetchByIds(...$recipientIds);

        $messages = array_map(function (MessageInterface $message) use ($contacts) {
            $recipient = $message->getRecipient();

            if ($recipient && $recipient->getId()) {
                $mappedContact = $contacts[$recipient->getId()] ?? null;

                if ($mappedContact instanceof MessageUserInterface) {
                    $message->setRecipient($mappedContact);
                }
            }

            return $message;
        }, $messages);

        $this->runner->run(function () use ($messages) {
            $mappings = $this->mapMessagesToHandlers(...$messages);

            foreach ($mappings as $channel => $mappedMessages) {
                $handler = $this->handlers[$channel] ?? null;

                if (! $handler) {
                    $this->throwHandlerNotFoundException($channel);
                }
                
                $handler->send(...$mappedMessages);
            }
        });
    }

    /**
     * Maps messages to their respective handlers
     *
     * @param MessageInterface ...$messages
     * @return MessageInterface[]
     */
    private function mapMessagesToHandlers(MessageInterface ...$messages): array
    {
        $mappings = [];

        foreach ($messages as $message) {
            foreach ($message->getChannels() as $channel) {
                if (! array_key_exists($channel, $mappings)) {
                    $mappings[$channel] = [];
                }

                $mappings[$channel][] = $message;
            }
        }

        return $mappings;
    }

    /**
     *
     * {@inheritdoc}
     * @see App\Services\Messaging\MessagingServiceInterface::subscribe()
     */
    public function subscribe(User $user, string $channel, array $subscription): void
    {
        if (! array_key_exists($channel, $this->handlers)) {
            $this->throwHandlerNotFoundException($channel);
        }

        $this->handlers[$channel]->subscribe($user, $subscription);
    }

    /**
     *
     * {@inheritdoc}
     * @see App\Services\Messaging\MessagingServiceInterface::unsubscribe()
     */
    public function unsubscribe(User $user, string $channel, array $unsubscription): void
    {
        if (! array_key_exists($channel, $this->handlers)) {
            $this->throwHandlerNotFoundException($channel);
        }

        $this->handlers[$channel]->unsubscribe($user, $unsubscription);
    }

    /**
     *
     * {@inheritdoc}
     * @see \Api\Services\Messaging\MessagingServiceInterface::fetchForUser()
     */
    public function fetchForUser(string $channel, Account $user/* ParsedFilter $filter*/): array
    {
        if (! array_key_exists($channel, $this->handlers)) {
            $this->throwHandlerNotFoundException($channel);
        }

    return $this->handlers[$channel]->fetchForUser($user /*$filter*/);
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Services\Messaging\MessagingServiceInterface::countForUser()
     */
    public function countForUser(string $channel, Account $user /*FilterCollection $filters*/): int
    {
        if (! array_key_exists($channel, $this->handlers)) {
            $this->throwHandlerNotFoundException($channel);
        }

    return $this->handlers[$channel]->countForUser($user /*$filters*/);
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Services\Messaging\MessagingServiceInterface::findForUser()
     */
    public function findForUser(string $channel, Account $user, int $message /*FieldCollection $fields*/): ?PushMessage
    {
        if (! array_key_exists($channel, $this->handlers)) {
            $this->throwHandlerNotFoundException($channel);
        }

    return $this->handlers[$channel]->findForUser($user, $message /*$fields*/);
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Services\Messaging\MessagingServiceInterface::markMessageAsRead()
     */
    public function markMessageAsRead(string $channel, PushMessage $message): void
    {
        if (! array_key_exists($channel, $this->handlers)) {
            $this->throwHandlerNotFoundException($channel);
        }

        $this->handlers[$channel]->markMessageAsRead($message->getId());
    }

    protected function throwHandlerNotFoundException(string $channel, int $statusCode = 500): void
    {
        throw new Exception(sprintf('No handler defined for "%s" messaging channel.', $channel), $statusCode);
    }
}

