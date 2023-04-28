<?php
declare(strict_types = 1);
namespace App\Services\Messaging;

use App\Repositories\Message\MessageInterface;

/**
 *
 * @author Asante Foster
 *        
 */
interface MessageUserInterface
{

    /**
     * Returns Id of message user
     *
     * @return int
     */
    //public function getId(): ?int;
    public function getId(): ?int;

    /**
     * Returns Ids of message user
     *
     * @return int
     */
    //public function getId[]: ?int;
   public function getIds(int ...$ids):? array;
  
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


    /**
     * Returns message subject
     *
     * This is usually message subject
     *
     * @return string|NULL
     */

    public function getSubject(): ?string;
  
    /**
     * Returns message subject
     *
     * This is usually message subject
     * */
    public function getContent(): ?string;
  
  
}

