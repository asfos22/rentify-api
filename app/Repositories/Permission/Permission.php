<?php

namespace App\Repositories\Permission;

use App\Repositories\Model;

/**
 *
 * @author Foster Asante<asantefoster22@gmail.com>
 *
 */
final class Permission extends Model
{

    const PERM_ACCOUNT_READ = 'account.read';

    const PERM_ACCOUNT_CREATE = 'account.create';

    const PERM_ACCOUNT_UPDATE = 'account.update';

    const PERM_ACCOUNT_DELETE = 'account.delete';

    const PERM_ACCOUNT_STATUS_MANAGE = 'account.status.manage';


    const PERM_PROPERTY_READ = 'property.read';

    const PERM_PROPERTY_CREATE = 'property.create';

    const PERM_PROPERTY_UPDATE = 'property.update';

    const PERM_PROPERTY_DELETE = 'property.delete';


    /**
     * Indicates whether a user has permission to read all messages
     *
     * @var string
     */
    const PERM_MESSAGE_READ = 'message.read';

    /**
     * Indicates whether a user can send messages
     *
     * @var string
     */
    const PERM_MESSAGE_SEND = 'message.send';

    /**
     * Indicates whether a user can update messages
     *
     * @var string
     */
    const PERM_UPDATE_MESSAGE = 'message.update';


    /**
     * Indicates if a user can delete messages
     *
     * @var string
     */
    const PERM_MESSAGE_DELETE = 'message.delete';


    /**
     * Indicates whether a user has permission to read all conversations
     *
     * @var string
     */
    const PERM_MESSAGE_CONVERSATION_READ = 'conversation.read';

    /**
     * Indicates whether a user can send conversations
     *
     * @var string
     */
    const PERM_MESSAGE_CONVERSATION_SEND = 'conversation.send';

    /**
     * Indicates if a user can delete conversations
     *
     * @var string
     */
    const PERM_MESSAGE_CONVERSATION_DELETE = 'conversation.delete';



    /**
     * Indicates whether a user has permission to read all reviews
     *
     * @var string
     */
    const PERM_SELF_REVIEW_READ = 'review.self.read';

    /**
     * Indicates whether a user can send review
     *
     * @var string
     */
    const PERM_REVIEW_SEND = 'review.send';

    /**
     * Indicates if a user can self delete review
     *
     * @var string
     */
    const PERM__SELF_REVIEW_DELETE = 'review.self.delete';

     /**
     * Indicates if a user can delete review
     *
     * @var string
     */
    const PERM__REVIEW_DELETE = 'review.delete';



    /**
     *
     * @var string
     */
    private $code;

    /**
     *
     * @var string
     */
    private $description;

    /**
     * Returns permission description
     *
     * @return string|NULL
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Sets permission description
     *
     * @param string $description
     */
    public function setDescription(?string $description)
    {
        $this->description = $description;
    }

    /**
     * Returns role code
     *
     * @return string|NULL
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * Sets role code
     *
     * @param string $code
     */
    public function setCode(?string $code)
    {
        $this->code = $code;
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

