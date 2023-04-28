<?php
declare(strict_types = 1);
namespace App\Repositories\Message\Messaging;

use App\Services\Messaging\MessageUserInterface;

/**
 * Message sent to user when a forgotten password reset is requested
 *
 * @author Asante Foster
 *        
 */
class MessageUser implements MessageUserInterface
{
   /*
    private $messageUserInterface;

   
    public function __construct(

        MessageUserInterface $messageUserInterface

    )
    {
        $this->messageUserInterface = $messageUserInterface;

    }*/

    /**
     * Returns Id of message user
     *
     * @return int
     */
    
    public function getId(): ?int;

    /**
     * Returns name of message user
     *
     * @return string|NULL
     */
    public function getName(): ?string;

    /**
     * Returns phone number of message user
     *
     * @return string|NULL
     */
    public function getPhone(): ?string;

    /**
     * Returns email address of message user
     *
     * @return string|NULL
     */
    public function getEmail(): ?string;


}

