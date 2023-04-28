<?php
namespace App\Repositories\Message\Messaging;

interface RepoMessageInterface
{

    const STATE_SUCCESS = 'SUCCESS';

    const STATE_INFORMATION = 'INFORMATION';

    const STATE_WARNING = 'WARNING';

    const STATE_ERROR = 'ERROR';

    const TYPE_PROPERTY_CREATED = 'property.created';

    const TYPE_MESSAGE_CREATED = 'message.created';

    const TYPE_CHAT_CREATED = 'chat.created';

    const  TYPE_REVIEW_CREATED  = 'revew.created';

    const  TYPE_REPORT_CREATED  = 'report.created';


    /**
     * Returns event for which this message is associated
     *
     * @return string
     */
    public function getEvent(): ?string;

    /**
     * Returns state of the message ('SUCCESS', 'INFORMATION', 'WARNING', 'ERROR')
     *
     * @return string
     */
    public function getState(): string;

    /**
     * Returns message payload
     *
     * @return array
     */
    public function getPayload(): array;

    /**
     * Returns message sender
     * This is for account to account messaging
     *
     * @return MessageUserInterface|NULL
     */
    public function getSender(): ?MessageUserInterface;

    /**
     * Returns message recipient
     *
     * @return MessageUserInterface|NULL
     */
    public function getRecipient(): ?MessageUserInterface;

    /**
     * Sets message recpient
     *
     * @param MessageUserInterface $recipient
     */
    public function setRecipient(MessageUserInterface $recipient);

    /**
     * Returns message subject
     *
     * This is usually for push notification
     *
     * @return string|NULL
     */
    public function getSubject(): ?string;

    /**
     * Returns message content
     *
     * @return string|NULL
     */
    public function getContent(): ?string;

    /**
     * Returns channels (SMS, EMAIL, PUSH) on which this message should be sent
     *
     * @return string[]
     */
    public function getChannels(): array;

    /**
     * Indicates if this message is data only message and not a full notification
     *
     * @return bool
     */
    public function isData(): bool;

    /**
     * Return messge image url if any
     *
     * @return string|NULL
     */
    public function getImage(): ?string;
    
    /**
     * return life time of a message in seconds
     * @return int
     */
    public function getLifeTime(): int;
}

