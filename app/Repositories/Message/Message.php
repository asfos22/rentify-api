<?php

namespace App\Repositories\Message;

use App\Repositories\Conversation\Conversation;
use App\Repositories\Messaging\MessageUserInterface;
use App\Repositories\Model;
use App\Repositories\User\User;

/**
 *
 * @author Foster Asante<asantefoster22@gmail.com>
 *
 */
final class Message extends Model
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
   // public function getEvent(): ?string;

    /**
     * Returns state of the message ('SUCCESS', 'INFORMATION', 'WARNING', 'ERROR')
     *
     * @return string
     */
  //  public function getState(): string;

    /**
     * Returns message payload
     *
     * @return array
     */
    //public function getPayload(): array;

    /**
     * Returns message sender
     * This is for account to account messaging
     *
     * @return MessageUserInterface|NULL
     */
   // public function getSender(): ?MessageUserInterface;

    /**
     * Returns message recipient
     *
     * @return MessageUserInterface|NULL
     */
  //  public function getRecipient(): ?MessageUserInterface;

    /**
     * Sets message recpient
     *
     * @param MessageUserInterface $recipient
     */
    //public function setRecipient(MessageUserInterface $recipient);

    /**
     * Returns message subject
     *
     * This is usually for push notification
     *
     * @return string|NULL
     */
    //public function getSubject(): ?string;

    /**
     * Returns message content
     *
     * @return string|NULL
     */
   // public function getContent(): ?string;

    /**
     * Returns channels (SMS, EMAIL, PUSH) on which this message should be sent
     *
     * @return string[]
     */
   // public function getChannels(): array;

    /**
     * Indicates if this message is data only message and not a full notification
     *
     * @return bool
     */
   // public function isData(): bool;

    /**
     * Return messge image url if any
     *
     * @return string|NULL
     */
   // public function getImage(): ?string;
    
    /**
     * return life time of a message in seconds
     * @return int
     */
    //public function getLifeTime(): int;
    /**
     *
     * @var string
     */
    public $id;


    /**
     * @var  name
     */
    private $name;

    /**
     * @var
     */
    private $text;

    /**
     * @var status
     */
    private $status;

    /**
     *
     * @var Token
     */
    private $token;

    /**
     * @var conversation
     */
    private $conversation;

    /**
     * @var link
     */
    private $link;
    /**
     * @var  reference
     */

    private $ref;

    /**
     * @var count
     */
    private $count;

    /**
     * @var
     */
    private $sender;

    /**
     * @var
     */
    private $receiver;

    /**
     * @var
     */
    private $user;

    /**
     * @var
     */
    private $notificationID;


    /**
     * Returns property id
     *
     * @return int|NULL
     */
    public function getID(): ?int
    {
        return $this->id;
    }

    /**
     * Set message id
     * @param int|null $id
     */
    public function setID(?int $id)
    {
        $this->id = $id;
    }

    /**
     *  Sets message text
     * @param string null $text
     */
    public function setText(?String $text)
    {
        $this->text = $text;
    }


    /**
     * Returns text
     *
     * @return String|NULL
     */
    public function getText(): ?String
    {
        return $this->text;
    }


    /**
     *  Sets name text
     * @param string null $name
     */
    public function setName(?String $name)
    {
        $this->name = $name;
    }


    /**
     * Returns name
     *
     * @return String|NULL
     */
    public function getName(): ?String
    {
        return $this->name;
    }


    /**
     * Returns status
     *
     * @return int|NULL
     */
    public function getStatus(): ?int
    {
        return $this->status;
    }

    /**
     *  Sets status
     * @param int|null $status
     */
    public function setStatus(?int $status)
    {
        $this->status = $status;
    }


    /**
     * Returns token associated with this auth instance
     *
     * @return Token|NULL
     */
    public function getToken(): ?String
    {
        return $this->token;
    }

    /**
     * Sets reference associated with this message instances
     * @param String|null $reference
     */

    public function setReference(?String $reference)
    {
        $this->ref = $reference;
    }


    /**
     * Returns token associated with this message instance
     *
     * @return Token|NULL
     */
    public function getReference(): ?String
    {
        return $this->ref;
    }

    /**
     * Sets token associated with this message instances
     * @param String|null $token
     */

    public function setToken(?String $token)
    {
        $this->token = $token;
    }

    /**
     * Returns unread count
     *
     * @return int|NULL
     */
    public function getCountConvo(): ?int
    {
        return $this->count;
    }

    /**
     * Set unread message 
     * @param int|null $count
     */
    public function setCountConvo(?int $count)
    {
        $this->count = $count;
    
    }

    /**
     * Returns conversation associated with this message
     *
     * @return Conversation[]
     */
    public function getConversation(): array
    {
        return is_array($this->conversation) ? $this->conversation : [];
    }

    /**
     * Sets conversation for this message
     * @param Conversation ...$conversations
     */
    public function setConversation(Conversation ...$conversations)
    {
        if (count($conversations)) {
            $this->conversation = $conversations;
        } else {
            $this->conversation = null;
        }
    }

    /**
     * Returns link
     *
     * @return string|NULL
     */
    public function getLink(): ?string
    {
        return $this->link;
    }

    /**
     * Returns link
     * @param string|null $link
     */
    public function setLink(?string $link)
    {
        $this->link = $link;
    }

    /**
     * Returns user
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Set user
     * @param User|null $user
     */
    public function setUser(?User $user)
    {
        $this->user = $user;
    }

    /**
     * Set receiver

     * @return User|NULL
     */
    public function setReceiver(?User $receiver)
    {
        $this->receiver = $receiver;
    }

    /**
     * Returns receiver
     * @return User|null
     */
    public function getReceiver(): ?User
    {
        return $this->receiver;
    }

    /**
     * Set sender id
     * @param int|null $senderID
     */
   /* public function setSenderID(?int $senderID)
    {
        $this->sender = $senderID;
    }*/



    /**
     * Returns notification id
     * @return User|null
     */
    public function getNotificationID(): ?int
    {
        return $this->notificationID;
    }

    /**
     *  Set notification id
     * @param int|null $notificationsID
     */
    public function setNotificationID(?int $notificationsID)
    {
        $this->notificationID = $notificationsID;
    }

     /**
     * Returns user
     *
     * @return User|NULL
     */
   /* public function getSender(): ?User
    {
        return $this->sender;
    }*/

    /**
     * Sets country
     *
     * @param User user
     */
   /* public function setSender(?User $sender)
    {
        $this->sender = $sender;
    }*/


    /**
     * @return array
     */
    protected function toJson()
    {
        $content = get_object_vars($this);
        unset($content['id'], $content['updated_at'], $content['null']);
        return $content;
    }
}

