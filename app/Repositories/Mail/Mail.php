<?php

namespace App\Repositories\Mail;

use App\Repositories\Message\Message;
use App\Repositories\Model;

final class Mail extends Model
{

    /**
     *
     * @var messages[]
     */
    private $messages = [];

    /**
     * Returns messages
     *
     * @return Message[]
     */
    public function getMessage(): array
    {
        return $this->messages;
    }

    /**
     * Sets message
     *
     * @param Message ...messages
     */
    public function setMessage(Message...$messages)
    {
        $this->messages = $messages;
    }

    /**
     *
     * {@inheritdoc}
     * @see \Rentify\Api\Repositories\Model::toJson()
     */
    protected function toJson()
    {
        return get_object_vars($this);
    }

}
