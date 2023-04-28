<?php
declare (strict_types = 1);
namespace App\Repositories\Message\Contact;

use App\Repositories\Model;
use App\Services\Messaging;
use App\Services\Messaging\MessageUserInterface;

class MessageContact extends Model implements MessageUserInterface
{

    /**
     *
     * @var ids[]
     */
    private $ids = [];
    /**
     *
     * @var string
     */
    private $name;

    /**
     *
     * @var string
     */
    private $phone;

    /**
     *
     * @var string
     */
    private $email;

    /**
     * @var string
     */

    private $subject;
    /**
     * @var string
     */

    private $content;

    /**
     *
     * @var string[]
     */
    private $push_tokens = [];

    /**
     * Returns user id
     *
     * @return id[]
     */
    public function getIds(int ...$ids): array
    {
        return $this->ids ?? [];
    }
    /**
     * Sets multiple users id
     *
     * @param int ...$ids
     */
    public function setIds(int...$ids): void
    {
        $this->ids = $ids;
    }

    /**
     * Sets name
     *
     * @param string $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * Sets phone number
     *
     * @param string $phone
     */
    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    /**
     * Sets email address
     *
     * @param string $email
     */
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    /**
     * Returns push notification tokens
     *
     * @return string[]
     */
    public function getPushTokens(): array
    {
        return $this->push_tokens ?? [];
    }

    /**
     * Sets push notification tokens
     *
     * @param string ...$push_tokens
     */
    public function setPushTokens(string...$push_tokens): void
    {
        $this->push_tokens = $push_tokens;
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Services\Messaging\MessageUserInterface::getPhone()
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Services\Messaging\MessageUserInterface::getName()
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Services\Messaging\MessageUserInterface::getEmail()
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }
    /**
     * Sets message subject
     *
     * @param string $email
     */
    public function setSubject(?string $subject): void
    {
        $this->subject = $subject;
    }

    /**
     * Returns message subject
     *
     * This is usually for push notification
     *
     * @return string|NULL
     */

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    /**
     * Sets message content
     *
     * @param string $email
     */
    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Services\Messaging\MessageUserInterface::getEmail()
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Model::toJson()
     */
    protected function toJson()
    {
        return get_object_vars($this);
    }
}
