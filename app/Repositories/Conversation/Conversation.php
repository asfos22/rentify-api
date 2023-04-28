<?php

namespace App\Repositories\Conversation;


use App\Repositories\Model;

class Conversation extends Model
{
    /**
     *
     * @var string
     */
    protected $id;

    /**
     * @var name
     */
    private $name;

    /**
     * @var
     */
    private $text;

    /**
     * @var
     */
    private $status;


    private $ref;


    /**
     * @var
     */
    private $seen;


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
     *  Sets message text
     * @param string null $name
     */
    public function setText(?String $name)
    {
        $this->text = $name;
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
     * Returns status
     *
     * @return int|NULL
     */
    public function getStatus(): ?String
    {
        return $this->status;
    }

    /**
     *  Sets status
     * @param String|null $status
     */
    public function setStatus(?String $status)
    {
        $this->status = $status;
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
     * Returns conversation associated with this message
     *
     * @return Conversation[]
     */
    /* public function getConversation(): array
     {
         return is_array($this->conversation) ? $this->conversation : [];
     }*/

    /**
     * Sets conversation for this message
     * @param Conversation ...$conversations
     */
    /* public function setConversation(Conversation ...$conversations)
     {
         if (count($conversations)) {
             $this->conversation = $conversations;
         } else {
             $this->conversation = null;
         }
     }*/

    /**
     * Returns seen
     *
     * @return int|NULL
     */
    public function getSeen(): ?int
    {
        return $this->seen;
    }

    /**
     * Returns link
     * @param int|null $seen
     */
    public function setSeen(?int $seen)
    {
        $this->seen = $seen;
    }


    /**
     * @return array
     */
    protected function toJson()
    {
        $content = get_object_vars($this);
        unset($content['id'], $content['updated_at']);
        return $content;
    }

}