<?php
declare(strict_types = 1);
namespace App\Repositories\Message;

use App\Repositories\Model;
use App\Services\Messaging\MessageInterface as MessagingMessageInterface;
use App\Services\Messaging\MessageInterface;
use Exception;

/**
 * Represents message state
 *
 * @author Asante Foster
 *        
 */
class MessageState extends Model
{

    private static $validStates = array(
        MessagingMessageInterface::STATE_SUCCESS,
        MessagingMessageInterface::STATE_INFORMATION,
        MessagingMessageInterface::STATE_WARNING,
        MessagingMessageInterface::STATE_ERROR
    );

    /**
     *
     * @var string
     */
    private $name;

    /**
     *
     * @var string
     */
    private $code;

    /**
     *
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     *
     * @return string
     */
    public function getCode(): ?string
    {
        return $this->code ??  MessagingMessageInterface::STATE_INFORMATION;
    }

    /**
     *
     * @param string $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     *
     * @param string $code
     */
    public function setCode(?string $code): void
    {
        if ($code && ! in_array($code, self::$validStates)) {
            throw new Exception(sprintf('"%s" is not a valid message state. Valid states are %s.', implode(', ', self::$validStates)));
        }

        $this->code = $code;
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

