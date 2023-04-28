<?php
declare(strict_types = 1);
namespace App\Repositories\System\Event;

use App\Repositories\Model;

/**
 * Represents a system event
 *
 * @author Asante Foster
 *        
 */
class SystemEvent extends Model
{

    /**
     * Generic system event
     *
     * @var string
     */
    const EVENT_GENERIC = 'system.generic';

    /**
     * Event emitted when a account verification
     *
     * @var string
     */
    const EVENT_AUTH_ACCOUNT_VERIFICATION = 'auth.account.verification';


    /**
     * Event emitted when a forgotten password is reset
     *
     * @var string
     */
    const EVENT_AUTH_PASSWORD_RESET = 'auth.password.reset';

    /**
     * Event emitted when a user account is created
     *
     * @var string
     */
    const EVENT_USER_CREATED = 'user.created';

    /**
     * Event emitted when a user account has been updated
     *
     * @var string
     */
    const EVENT_USER_UPDATED = 'user.updated';

    /**
     * Event emitted when a user account has been delete
     *
     * @var string
     */
    const EVENT_USER_DELETED = 'user.deleted';


      /**
     * Event emitted when a new service request has been created by a user
     *
     * @var string
     */
    const EVENT_SERVICE_CREATED = 'service.created';
    /**
     * Event emitted when a new service request has been made by a user
     *
     * @var string
     */
    const EVENT_SERVICE_REQUESTED = 'service.requested';
    
    /**
     * Event emitted when a new service request has been approved
     *
     * @var string
     */
    const EVENT_SERVICE_APPROVED = 'service.approved';

    /**
     * Event emitted when a service has been cancelled by the user
     *
     * @var string
     */
    const EVENT_SERVICE_CANCELLED = 'service.cancelled';

    /**
     * Event emitted when a user's service has been declined
     *
     * @var string
     */
    const EVENT_SERVICE_DECLINED = 'service.declined';

    /**
     * Event emitted when a user's service needs reassignment
     *
     * @var string
     */
    const EVENT_SERVICE_UNASSIGNED = 'service.unassigned';

    /**
     * Event emitted when a user's service in progress
     *
     * @var string
     */
    const EVENT_SERVICE_IN_PROGRESS = 'service.in_progress';

    /**
     * Event emitted when a user's service has been in progress
     *
     * @var string
     */
    const EVENT_SERVICE_CHAT_IN_PROGRESS  = 'service.in_progress';

    /**
     * Event emitted when a user's service has been chat completed
     *
     * @var string
     */
    const EVENT_SERVICE_CHAT_COMPLETED = 'service.chat.completed';




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
     *
     * @return string
     */
    public function getCode(): ?string
    {
        return $this->code ?? self::EVENT_GENERIC;
    }

    /**
     *
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     *
     * @param string $code
     */
    public function setCode(?string $code): void
    {
        $this->code = $code;
    }

    /**
     *
     * @param string $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     *
     * {@inheritdoc}
     * @see \Api\Repositories\Model::toJson()
     */
    protected function toJson()
    {
        return get_object_vars($this);
    }
}

