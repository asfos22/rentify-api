<?php
declare(strict_types = 1);
namespace App\Services\Message;

use App\Repositories\Message\Messaging\MessengerInterface;
use App\Repositories\Messaging\MessageUserInterface;
use App\Repositories\System\Event\SystemEvent;
use App\Services\Messaging\ServiceMessageInterface;

/**
 *
 * @author Asante Foster
 *        
 */
class ServiceMessage implements ServiceMessageInterface
{

    /**
     *
     * @var MessageUserInterface
     */
    private $sender;

    /**
     *
     * @var MessageUserInterface
     */
    private $recipient;

    /**
     *
     * @var string
     */
    private $content;

    /**
     *
     * @var string
     */
    private $subject;

    /**
     *
     * @var array
     */
    private $payload;

    /**
     *
     * @var string
     */
    private $event;

    /**
     *
     * @var array
     */
    private $channels = [];

    /**
     *
     * @var string
     */
    private $state;

    /**
     *
     * @var bool
     */
    private $is_data;

    /**
     *
     * @var string
     */
    private $image;

    /**
     *
     * @var int
     */
    private $life_time;

    /**
     *
     * {@inheritdoc}
     * @see App\Services\Messaging\MessageInterface::getLifeTime()
     */
    public function getLifeTime(): int
    {
        return $this->life_time ?? 600; // Defaults to 10 minutes
    }

    /**
     *
     * @param int $life_time
     */
    public function setLifeTime(?int $life_time)
    {
        $this->life_time = $life_time;
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Services\Messaging\MessageInterface::getImage()
     */
    public function getImage(): ?string
    {
        return $this->image;
    }

    /**
     *
     * @param string $image
     */
    public function setImage(?string $image): void
    {
        $this->image = $image;
    }

    /**
     * Indicates whether this message is considered data only message/notification.
     * Data only messages are only supported by push notification messenger
     *
     * @return boolean
     */
    public function isData(): bool
    {
        return $this->is_data === true;
    }

    /**
     * Sets if the message/notification should be sent as a data only notification.
     * Data only messages are only supported by push notification messenger
     *
     * @param boolean $is_data
     */
    public function setData(bool $is_data): void
    {
        $this->is_data = $is_data;
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Services\Messaging\MessageInterface::getState()
     */
    public function getState(): string
    {
        return $this->state ?? self::STATE_INFORMATION;
    }

    /**
     * Sets state of the message ('SUCCESS', 'INFORMATION', 'WARNING', 'ERROR')
     *
     * @param string $state
     */
    public function setState(string $state): void
    {
        $this->state = $state;
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Services\Messaging\MessageInterface::getEvent()
     */
    public function getEvent(): ?string
    {
        return $this->event ?? SystemEvent::EVENT_GENERIC;
    }

    /**
     *
     * @param string $event
     */
    public function setEvent(?string $event): void
    {
        $this->event = $event;
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Services\Messaging\MessageInterface::getPayload()
     */
    public function getPayload(): array
    {
        return $this->payload ?? [];
    }

    /**
     * Sets message payload
     *
     * @param array $payload
     */
    public function setPayload(array $payload): void
    {
        $this->payload = $payload;
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Services\Messaging\MessageInterface::getSender()
     */
    public function getSender(): ?MessageUserInterface
    {
        return $this->sender;
    }

    /**
     * Sets sender
     *
     * @param MessageUserInterface $sender
     */
    public function setSender(?MessageUserInterface $sender)
    {
        $this->sender = $sender;
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Services\Messaging\MessageInterface::getRecipient()
     */
    public function getRecipient(): ?MessageUserInterface
    {
        return $this->recipient;
    }

    /**
     * Sets recipient
     *
     * @param MessageUserInterface $recipient
     */
    public function setRecipient(?MessageUserInterface $recipient)
    {
        $this->recipient = $recipient;
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Services\Messaging\MessageInterface::getSubject()
     */
    public function getSubject(): ?string
    {
        return $this->subject;
    }

    /**
     * Sets subject
     *
     * @param string $subject
     */
    public function setSubject(?string $subject)
    {
        $this->subject = $subject;
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Services\Messaging\MessageInterface::getContent()
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * Sets content
     *
     * @param string $content
     */
    public function setContent(?string $content)
    {
        $this->content = $content;
    }

    /**
     *
     * {@inheritdoc}
     * @see App\Services\Messaging\MessageInterface::getChannels()
     */
    public function getChannels(): array
    {
        $defaultChannels = array(
            // MessengerInterface::CHANNEL_SMS,
            MessengerInterface::CHANNEL_PUSH
            // MessengerInterface::CHANNEL_EMAIL
        );

        return is_array($this->channels) && count($this->channels) ? $this->channels : $defaultChannels;
    }

    /**
     *
     * @param string[] $channels
     */
    public function setChannels(string ...$channels): void
    {
        $this->channels = $channels;
    }
}

