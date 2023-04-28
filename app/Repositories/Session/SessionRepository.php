<?php

namespace App\Repositories\Session;

use Session;
use App\Repositories\Session\Session as AccountSession;

/**
 * @author Foster Asante <asantefoster22@gmail.com>
 */
class SessionRepository implements SessionInterface
{


    /**
     * Create Account session for account
     * @param String name
     * @param Session $session
     * @return mixed
     */
    public function createAccountSession(String $name, AccountSession $session)
    {

        $item = array(
            'data' => [
                "name" => $session->getName(),
                "id" => $session->getId(),
                "secret" => $session->getSecret(),
                "expires_at" => null
            ],

        );

        return Session::put($name, $item);

    }

    /**
     * Get account session  for account
     * @param String name
     * @return mixed
     */
    public function getAccountSession(String $name)
    {

        return Session::get($name);
    }

    /**
     * Change account session
     * @param String name
     * @param Session $session
     * @return mixed
     */
    public function changeAccountSession(String $name, AccountSession $session)
    {
        // TODO: Implement changeAccountSession() method.
    }

    /**
     * Destroy session for account
     * @param String name
     * @param Session $session
     * @return mixed
     */
    public function destroyAccountSession()
    {
        Session::flush();

        return redirect()->intended('auth/login');

    }
}
