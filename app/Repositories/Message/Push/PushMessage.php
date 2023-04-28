<?php
declare(strict_types = 1);
namespace App\Repositories\Message\Push;

use App\Repositories\Model;
use App\Repositories\Message\MessageState;
use App\Repositories\System\Event\SystemEvent;
use App\Repositories\User;
use App\Repositories\DateTime;
use App\Repositories\User\Account;

class PushMessage extends Model
{

    /**
     *
     * @var string
     */
    private $subject;

    /**
     *
     * @var string
     */
    private $content;

    /**
     *
     * @var Account
     */
    private $user;

    /**
     *
     * @var MessageState
     */
    private $state;

    /**
     *
     * @var SystemEvent
     */
    private $event;

    /**
     *
     * @var array
     */
    private $payload;

    /**
     *
     * @var bool
     */
    private $is_read;

    /**
     *
     * @var DateTime
     */
    private $read_at;

    /**
     *
     * @return boolean
     */
    public function isRead(): bool
    {
        return $this->is_read ?? false;
    }

    /**
     *
     * @return App\Repositories\DateTime
     */
    public function getReadAt()
    {
        return $this->read_at;
    }

    /**
     *
     * @param boolean $read
     */
    public function setRead(?bool $read): void
    {
        $this->is_read = $read;
    }

    /**
     *
     * @param \App\Repositories\DateTime $read_at
     */
    public function setReadAt(?DateTime $read_at): void
    {
        $this->read_at = $read_at;
    }

    /**
     *
     * @return array | NULL
     */
    public function getPayload(): ?array
    {
        return $this->payload;
    }

    /**
     *
     * @param array $payload
     */
    public function setPayload(?array $payload): void
    {
        $this->payload = $payload;
    }

    /**
     *
     * @return string|NULL
     */
    public function getSubject(): ?string
    {
        return $this->subject;
    }

    /**
     *
     * @param string $subject
     */
    public function setSubject(?string $subject): void
    {
        $this->subject = $subject;
    }

    /**
     *
     * @return string|NULL
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     *
     * @param string $content
     */
    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    /**
     *
     * @return App\Repositories\User\Account
     */
    public function getUser(): ?Account
    {
        return $this->user ?? new Account();
    }

    /**
     *
     * @param \App\Repositories\User\Account $user
     */
    public function setUser(?Account $user): void
    {
        $this->user = $user;
    }

    /**
     *
     * @return \App\Repositories\System\Event\SystemEvent
     */
    public function getEvent(): ?SystemEvent
    {
        return $this->event ?? new SystemEvent();
    }

    /**
     *
     * @param \App\Repositories\System\Event\SystemEvent $event
     */
    public function setEvent(?SystemEvent $event): void
    {
        $this->event = $event;
    }

    /**
     *
     * @return \App\Repositories\Message\MessageState
     */
    public function getState(): ?MessageState
    {
        return $this->state ?? new MessageState();
    }

    /**
     *
     * @param \App\Repositories\Message\MessageState $state
     */
    public function setState(?MessageState $state): void
    {
        $this->state = $state;
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Message\Message::toJson()
     */
    public function toJson()
    {
        return get_object_vars($this);
    }
}

