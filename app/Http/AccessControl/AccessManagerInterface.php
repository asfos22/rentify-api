<?php

namespace App\Http\AccessControl;

use App\Repositories\Auth\Auth;
use Illuminate\Http\Request;

//use Psr\Http\Message\MessageInterface;
//use Psr\Http\Message\ServerRequestInterface;

/**
 *
 * @author Asante Foster <asantefoster22@gmail.com>
 *
 */
interface AccessManagerInterface
{

    /**
     * Enforces access control
     *
     * @param ServerRequestInterface $request
     * @return Auth
     */
    public function enforce(Request $request): Auth;

    /**
     * Forgets/deletes an auth session/token
     *
     * @param Auth $auth
     */
    public function forget(Auth $auth);

    /**
     * Authenticates a user
     *
     * @param string $username
     * @param string $password
     * @param MessageInterface $request
     * @return Auth|NULL
     */
    public function authenticate(string $username, string $password, Request $request): ?Auth;

    /**
     * Fetches an auth by its username
     *
     * @param string $username
     * @return Auth|NULL
     */
    public function verify(string $username): ?Auth;

    /**
     * Activates a newly created account
     *
     * @param string $token
     * @return Auth|NULL
     */
    public function activate(string $token): ?Auth;

    /**
     * Resets an existing auth's password
     *
     * @param Auth $auth
     * @param string $old_password
     * @param string $new_password
     * @return int Number of affected account
     */
    public function resetPassword(Auth $auth, string $old_password, string $new_password): int;

    /**
     * Resets an auth's password randomly and returns the password
     *
     * @param Auth $auth
     * @return string
     */
    public function resetForgottenPassword(Auth $auth): string;

    /**
     * Creates a new password from a given text
     *
     * @param string $text
     * @return string
     */
    public function createPassword(string $text): string;

    /**
     *
     * /**
     * Generates a random has of a given length
     *
     * @param int $length
     * @return string
     */
    public function createRandomCode(int $length): string;

    /**
     * @param Request $request
     * @param Auth $auth
     * @return mixed
     */
    public function setLoginToken(Request $request, Auth $auth);

    /**
     * Fetch account details
     * @param Auth $auth
     * @return mixed
     */
    public function find(Auth $auth);

}

