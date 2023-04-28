<?php
declare(strict_types = 1);
namespace App\Services\Messaging;

class MessageUser implements MessageUserInterface
{

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

