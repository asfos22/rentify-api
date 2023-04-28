<?php
declare(strict_types=1);

namespace App\Http\AccessControl;

use App\Repositories\Auth\Auth;
use App\Repositories\Auth\AuthRepositoryInterface;

//use Psr\Http\Message\MessageInterface;
//use Psr\Http\Message\ServerRequestInterface;
use App\Http\Exception\AccessControlException;
use App\Repositories\DateTime;
use App\Repositories\User\User;
use App\Repositories\Auth\Token;
//use Rentify\App\Repositories\DateTime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DateInterval;

/**
 *
 * @author Foster Asante <asantefoster22@gmail.com>
 *
 */
class AccessManager implements AccessManagerInterface
{

    const TOKEN_NAME = 'RENTIFY-USER-TOKEN';

    /**
     *
     * @var AuthRepositoryInterface
     */
    private $repository;

    /*public function __construct(AuthRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }*/

    /**
     *
     * {@inheritdoc}
     * @see \Rentify\App\Http\AccessControl\AccessManagerInterface::enforce()
     */
    public function enforce(Request $request): Auth
    {
        if (!$request->hasHeader(self::TOKEN_NAME)) {

            // return  redirect()->intended('auth/login');

            throw new AccessControlException('Missing authorization header.', 401);
        }

        $token = $request->getHeaderLine(self::TOKEN_NAME);

        if (empty($token)) {
            throw new AccessControlException('Missing authorization token.', 401);
        }

        $auth = $this->repository->findByToken($token);

        if (!($auth instanceof Auth)) {
            throw new AccessControlException('Invalid access token.', 401);
        }

        if ($auth->getUser()->isBlocked()) {
            throw new AccessControlException('Account blocked.');
        }

        if ($auth->getUser()->isParentBlocked()) {
            throw new AccessControlException('Account blocked. Contact your account manager for assistance.');
        }

        if (!$auth->getUser()->isActivated()) {
            throw new AccessControlException('Account activation required.');
        }

        if ($auth->getToken()->hasExpired()) {
            // Delete token ?
            throw new AccessControlException('Invalid access token.', 401);
        }

        return $auth;
    }

    /**
     *
     * {@inheritdoc}
     * @see \Rentify\Api\Http\AccessControl\AccessManagerInterface::activate()
     */
    public function activate(string $token, Request $request = null): ?Auth
    {
        $auth = $this->repository->findByConfirmationToken($token);

        if (!($auth instanceof Auth)) {
            throw new AccessControlException('Invalid confirmation token.', 422);
        }

        $auth->setConfirmationCode(null);

        $this->repository->activate($auth->getId());

        return $this->repository->find($auth->getId());
    }

    /**
     *
     * {@inheritdoc}
     * @see \Rentify\Api\Http\AccessControl\AccessManagerInterface::forget()
     */
    public function forget(Auth $auth)
    {
        $this->repository->deleteToken($auth->getToken()
            ->getSecret());
    }

    /**
     *
     * {@inheritdoc}
     * @see \Rentify\Api\Http\AccessControl\AccessManagerInterface::authenticate()
     */
    public function authenticate(string $username, string $password, Request $request = null): ?Auth
    {
        if (empty($username) || empty($password)) {
            throw new AccessControlException('Username and password combination required.');
        }

        $auth = $this->repository->findByUsername($username);

        if (!($auth instanceof Auth) || !($auth->getUser() instanceof User)) {
            throw new AccessControlException('Invalid username or password.');
        }

        if (true !== password_verify($password, $auth->getPassword())) {
            throw new AccessControlException('Invalid username or password.');
        }

        if (!$auth->getUser()->isActivated()) {
            throw new AccessControlException('Account activation required.');
        }

        if ($auth->getUser()->isBlocked()) {
            throw new AccessControlException('Account suspended.');
        }

        // Generate access token for this login session
        return $this->setLoginToken($request, $auth);
    }

    /**
     *
     * {@inheritdoc}
     * @see \Rentify\Api\Http\AccessControl\AccessManagerInterface::resetPassword()
     */
    public function resetPassword(Auth $auth, string $old_password, string $new_password): int
    {
        if (empty($old_password)) {
            throw new AccessControlException('Current account password required.');
        }

        if (empty($new_password)) {
            throw new AccessControlException('New account password required.');
        }

        // Check correctness of old password
        if (true !== password_verify($old_password, $auth->getPassword())) {
            throw new AccessControlException('Invalid account password.');
        }

        // Ensure that user is not resetting password to the same one
        if ($old_password === $new_password) {
            throw new AccessControlException('New and current passwords must not be the same.');
        }

        $auth->setPassword($this->createPassword($new_password));

        return $this->repository->update($auth->getId(), $auth);
    }

    /**
     *
     * {@inheritdoc}
     * @see \Rentify\Api\Http\AccessControl\AccessManagerInterface::verify()
     */
    public function verify(string $username): ?Auth
    {
        return $this->repository->findByUsername($username);
    }

    /**
     *
     * {@inheritdoc}
     * @see \Rentify\Api\Http\AccessControl\AccessManagerInterface::resetForgottenPassword()
     */
    public function resetForgottenPassword(Auth $auth, int $length = 5): string
    {
        $password = $this->createRandomCode($length);

        $auth->setPassword($this->createPassword($password));

        $this->repository->update($auth->getId(), $auth);

        return $password;
    }

    /**
     *
     * {@inheritdoc}
     * @see \Rentify\Api\Http\AccessControl\AccessManagerInterface::createPassword()
     */
    public function createPassword(string $text): string
    {
        return password_hash($text, PASSWORD_BCRYPT);
    }

    /**
     *
     * {@inheritdoc}
     * @see \Rentify\Api\Http\AccessControl\AccessManagerInterface::createRandomCode()
     */
    public function createRandomCode(int $length): string
    {
        return strtoupper(bin2hex(random_bytes($length)));  //bin2hex(random_bytes($length));
    }

    /**
     * @param Request $request
     * @param Auth $auth
     * @return Token|mixed
     * @throws \Exception
     */
    public function setLoginToken(Request $request, Auth $auth)
    {
        $token = new Token();
        $secret = strtoupper($this->createRandomCode(16));

        $ip = null;
        $client = null;

        if (null !== $request) {

            $ip = $request->ip();
            $client = $request->header('User-Agent');

        }


        $token->setSecret($secret);
        $token->setClient($client);
        $token->setIp($ip);
        // Set token expiration time to one (6) month from now
        $expires_at = new DateTime();
        $expires_at->add(new DateInterval('P6M'));
        $token->setExpiresAt($expires_at);

        // $auth->setToken($token);

        // $this->repository->installToken($auth);

        return $token; //$this->repository->findByToken($secret);
    }

    /**
     * Fetch account details
     * @param Auth $auth
     * @return mixed
     */
    public function find(Auth $auth)
    {
        // TODO: Implement find() method.
    }
}

