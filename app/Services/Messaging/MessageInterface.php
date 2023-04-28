<?php
namespace App\Services\Messaging;

/**
 * @author Asante Foster       
 */

interface MessageInterface
{


    /**
     * SMS channel
     *
     * @var string
     */
    const CHANNEL_SMS = 'SMS';

    /**
     * EMAIL channel
     *
     * @var string
     */
    const CHANNEL_EMAIL = 'EMAIL';

    /**
     * PUSH notification channel
     *
     * @var string
     */
    const CHANNEL_PUSH = 'PUSH';


    const STATE_SUCCESS = 'SUCCESS';

    const STATE_INFORMATION = 'INFORMATION';

    const STATE_WARNING = 'WARNING';

    const STATE_ERROR = 'ERROR';

    const TYPE_DELIVERY_CREATED = 'delivery.created';

    const TYPE_DELIVERY_ASSIGNED = 'delivery.assigned';

    const TYPE_DELIVERY_COMPLETED = 'delivery.completed';

    const TYPE_DELIVERY_PAYMENT_FAILED = 'delivery.payment.failed';

    const TYPE_DELIVERY_PAYMENT_SUCCESSFUL = 'delivery.payment.successful';

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
     * Returns channels (SMS, EMAIL, PUSH) on which this message should be sent
     *
     * @return string[]
     */
    public function getRecipients(): array;


    
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

