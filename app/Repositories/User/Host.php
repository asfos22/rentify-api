<?php
declare(strict_types=1);

namespace App\Repositories\User;


use App\Repositories\Model;

/**
 *
 * @author Foster Asante <asantefoster22@gmail.com>
 *
 */
class Host extends Model
{


    /**
     *
     * @var string
     */
    private $user_token;

    /**
     *
     * @var string
     */
    private $name;

    /**
     *
     * @var string
     */
    private $message;

    


   /**
     * Returns user token
     *
     * @return string|NULL
     */
    public function getUserToken(): ?string
    {
        return $this->user_token;
    }

    /**
     * Sets user token 
     *
     * @param string $token
     */
    public function setUserToken(?string $token)
    {
        $this->user_token = $token;
    }



    /**
     * Returns host name
     *
     * @return string|NULL
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Sets host name
     *
     * @param string $name
     */
    public function setName(?string $name)
    {
        $this->name = $name;
    }


    /**
     * Returns host message
     *
     * @return string|NULL
     */
    public function getWelcomeMessage(): ?string
    {
        return $this->message;
    }

    /**
     * Sets host welcome
     *
     * @param string $msg
     */
    public function setWelcomeMessage(?string $msg)
    {
        $this->message = $msg;
    }


    /**
     *
     * @return array
     */
    /**
     * @return array
     */
    protected function toJson()
    {
        $content = get_object_vars($this);
        unset($content['id'], $content['created_at'], $content['updated_at']);
        return $content;
    }

}
