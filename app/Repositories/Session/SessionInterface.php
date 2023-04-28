<?php

namespace App\Repositories\Session;


interface SessionInterface
{

    /**
     * Create Account session for account
     * @param String name
     * @param Session $session
     * @return mixed
     */
    public function createAccountSession(String $name, Session $session);


    /**
     * Get account session  for account
     * @param String name
     * @return mixed
     */

    public function getAccountSession(String $name);


    /**
     * Change account session
     * @param String name
     * @param Session $session
     * @return mixed
     */
    public function changeAccountSession(String $name, Session $session);


    /**
     * Destroy session for account
     * @param String name
     * @param Session $session
     * @return mixed
     */
    public function destroyAccountSession();


}
