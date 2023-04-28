<?php
declare(strict_types = 1);
namespace App\Services\Messaging;

use App\Http\Request\ParsedFilter;
use App\Repositories\Message\Push\PushMessage;
use App\Repositories\User\Account;
use App\Repositories\User\User;

use Swift_Mailer;
use Swift_Message;
use Psr\Log\LoggerInterface;
use Exception;

/**
 * @author Asante Foster       
 */
class EmailMessenger implements MessengerInterface
{

    /**
     *
     * @var Swift_Mailer
     */
    private $mailer;

    /**
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     *
     * @var string
     */
    private $mime;

    /**
     *
     * @var string
     */
    private $charset;

    /**
     *
     * @var string
     */
    private $sender_email;

    /**
     *
     * @var string
     */
    private $sender_name;

    public function __construct(
        Swift_Mailer $mailer,
        LoggerInterface $logger, 
        string $sender_email, string $sender_name, string $mime = 'text/html', string $charset = 'utf8')
    {
        $this->sender_email = $sender_email;
        $this->sender_name = $sender_name;

        $this->mailer = $mailer;
        $this->logger = $logger;
        $this->mime = $mime;
        $this->charset = $charset;
    }

    private function format(string $content): string
    {
        $content = nl2br($content);

        $message = <<<MESSAGE
        <html>
        <body>$content</body>
        </html>
        MESSAGE;

        return $message;
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Services\Messaging\MessengerInterface::send()
     */
    public function send(MessageInterface ...$messages): bool
    {
        try {
            foreach ($this->composeMessage(...$messages) as $message) {
                
                $this->mailer->send($message);
            }
        } catch (Exception $e) {
            $this->logger->warning($e->getMessage(), array(
                'trace' => $e->getTraceAsString()
            ));

            return false;
        }

        return true;
    }

    private function composeMessage(MessageInterface ...$messages): array
    {
        $composedMessages = [];

        foreach ($messages as $message) {
            $recipient = $message->getRecipient();

            if (! $recipient || ! $recipient->getEmail()) {
                continue;
            }

            $composedMessage = new Swift_Message($message->getSubject(), $this->format($message->getContent()), $this->mime, $this->charset);

            $composedMessage->setFrom(array(
                $this->sender_email => $this->sender_name
            ));

            $composedMessage->setReplyTo(array(
                $this->sender_email => $this->sender_name
            ));

            $composedMessage->setTo(array(
                $recipient->getEmail() => $recipient->getName()
            ));

            $composedMessages[] = $composedMessage;
        }

        return $composedMessages;
    }

    private function composeBroadcastMessage(MessageInterface $message, MessageUserInterface ...$recipients): array
    {
        $composedMessages = [];

        foreach ($recipients as $recipient) {

            if (! $recipient->getEmail()) {
                continue;
            }

            $composedMessage = new Swift_Message($message->getSubject(), $this->format($message->getContent()), $this->mime, $this->charset);

            $composedMessage->setFrom(array(
                $this->sender_email => $this->sender_name
            ));

            $composedMessage->setReplyTo(array(
                $this->sender_email => $this->sender_name
            ));

            $composedMessage->setTo(array(
                $recipient->getEmail() => $recipient->getName()
            ));

            $composedMessages[] = $composedMessage;
        }

        return $composedMessages;
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Services\Messaging\MessengerInterface::broadcast()
     */
    public function broadcast(string $topic, MessageInterface $message, MessageUserInterface ...$recipients): bool
    {
        try {
            foreach ($this->composeBroadcastMessage($message, ...$recipients) as $curMessage) {
                $this->mailer->send($curMessage);
            }
        } catch (Exception $e) {
            $this->logger->warning($e->getMessage(), array(
                'trace' => $e->getTraceAsString()
            ));

            return false;
        }

        return true;
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Services\Messaging\MessengerInterface::getChannel()
     */
    public function getChannel(): string
    {
        return self::CHANNEL_EMAIL;
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Services\Messaging\MessengerInterface::subscribe()
     */
    public function subscribe(User $user, array $subscription): void
    {}

    /**
     *
     * {@inheritdoc}
     * @see \App\Services\Messaging\MessengerInterface::unsubscribe()
     */
    public function unsubscribe(User $user, array $unsubscription): void
    {}

    /**
     *
     * {@inheritdoc}
     * @see \App\Services\Messaging\MessengerInterface::fetchForUser()
     */
    public function fetchForUser(Account $user /*ParsedFilter $filter*/): array
    {
        return [];
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Services\Messaging\MessengerInterface::countForUser()
     */
    public function countForUser(Account $user/* FilterCollection $filters*/): int
    {
        return 0;
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Services\Messaging\MessengerInterface::findForUser()
     */
    public function findForUser(Account $user, int $message/* FieldCollection $fields*/): ?PushMessage
    {
        return null;
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Services\Messaging\MessengerInterface::markMessageAsRead()
     */
    public function markMessageAsRead(int $message): void
    {}
}

