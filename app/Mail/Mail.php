<?php

namespace App\Repositories\Mail;

use App\Repositories\Model;


/**
 *
 * @author Foster Asante<asantefoster22@gmail.com>
 *
 */
final class Mail extends Model
{

    /**
     * @var string name
     */
    protected $name;

    /**
     * @var  email
     */
    private $email;

    /**
     *
     * @var string $subject
     */
    private $subject;

    /**
     * @var string $message
     */
    private $message;


    /**
     * Returns name
     *
     * @return string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Sets media id
     *
     * @param string name
     */
    public function setEmail(?string $email)
    {
        $this->email = $email;
    }


    /**
     * Returns name
     *
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Sets media id
     *
     * @param string name
     */
    public function setName(?string $name)
    {
        $this->name = $name;
    }

    /**
     * Get subject name
     * @return String|null
     */
    public function getSubject(): ?string
    {
        return $this->subject;
    }


    /**
     * Sets media name
     *
     * @param string $subject
     */
    public function setSubject(?string $subject)
    {
        $this->subject = $subject;
    }

    /**
     * Get message
     * @return String|null
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }


    /**
     * Sets message
     * @param string|null $message
     */
    public function setMessage(?string $message)
    {
        $this->message = $message;
    }


    /**
     * @return array
     */
    protected function toJson()
    {
        return get_object_vars($this);
    }
}

