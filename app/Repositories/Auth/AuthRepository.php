<?php

namespace App\Repositories\Auth;

use App\Http\AccessControl\AccessManager;
use App\Http\Exception\AccessControlException;
use App\Repositories\Auth\Verification\VerificationRepositoryInterface;
use App\Repositories\DateTime;
use App\Repositories\Location\Country;
use App\Repositories\Permission\Permission;
use App\Repositories\Repository;
use App\Repositories\Role\Role;
use App\Repositories\Tokens\TokenInterface;
use App\Repositories\User\User;
use App\Repositories\User\UserRepositoryInterface;
use App\Repositories\Util\Crypto as UtilCrypto;
use App\Traits\Tokens;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PDO;
use stdClass;

/**
 * @author Foster Asante <asantefoster22@gmail.com>
 */

class AuthRepository extends Repository implements AuthRepositoryInterface
{

    /**
     *
     */
    use Tokens;

    /**
     * @var TokenInterface
     */
    private $tokenInterface;

    /**
     * @var VerificationRepositoryInterface
     */
    private $verificationRepository;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var
     */
    private $token;

    /**
     * @var
     */
    private $auth;

    /**
     * @var
     */
    protected $connection;

    /**
     *
     */

    public function __construct(

        //  LoginValidator $loginValidator,
        VerificationRepositoryInterface $verificationRepository,
        TokenInterface $tokenInterface,
        UserRepositoryInterface $userRepository

    ) {
        $this->verificationRepository = $verificationRepository;
        $this->tokenInterface = $tokenInterface;
        $this->userRepository = $userRepository;
        //$this->loginValidator = $loginValidator;
        $this->connection = DB::connection()->getPdo();

    }

    /**
     *Find account by email and password
     * @param Request $request
     * @param string $email, $password
     * @return array|mixed
     * @throws AccessControlException
     */
    public function verifyAccount(Request $request, string $email, string $password): ?Auth
    {

        $auth = new Auth();

        $authQuery = new AuthQuery();

        $mainQuery = $authQuery::$query;

        $query = <<<QUERY

        $mainQuery

        WHERE u.email= ? LIMIT 1

        QUERY;

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $email, PDO::PARAM_STR);
        // $stmt->bindValue(2, $password, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $data = $stmt->fetch(PDO::FETCH_OBJ);
            $auth = new Auth();
            if (false != $data) {

                if ($data && Hash::check($password, $data->password)) {

                    $this->tokenInterface->updateAccountToken($request, $this->format($data));

                    return $this->find($data->id);
                }
            }

            if (false == $data || !Hash::check($password, $data->password)) {

                throw new AccessControlException('Invalid user name or password', 422);

            }
        }

        return $auth;

    }

    /**
     * @param String $name
     * @param String $email
     * @param String $phone
     * @param String $countryName
     * @param Role $role
     * @return \App\Models\User
     */

    public function createAccount(String $name, String $email, String $phone, String $countryName = '', Role $role = null): User
    {
        //$query = $this->findByUsername($email);

        $findByEmailOrPhone = $this->userRepository->findByEmailOrPhone($email, $phone); //findByEmail($email);

        if (!empty($findByEmailOrPhone)) {

            if ($findByEmailOrPhone->getPhone() === $phone) {

                throw new AccessControlException('Phone number already registered by another user.', 423);
            }

            if ($findByEmailOrPhone->getEmail() === $email) {

                throw new AccessControlException('Email already registered by another user.', 424);
            }

        }

        $country = new Country();
        $user = new User();
        $user->setName($name);
        $user->setEmail($email);
        $user->setPhone($phone);
        $user->setRole($role);

        $country->setIsoCode($countryName);
        $user->setCountry($country);

        $this->userRepository->create($user);

        return $user;

    }

    /**
     * @param Request $request
     * @return Auth
     * @throws AccessControlException
     */
    public function enforceHeader(Request $request): Auth
    {

        $this->auth = new Auth();

        if (!$request->header(AccessManager::TOKEN_NAME)) {

            throw new AccessControlException('Missing authorization header.', 401);
        }

        $token = $request->header(AccessManager::TOKEN_NAME);

        if (empty($token)) {

            throw new AccessControlException('Missing authorization token.', 401);
        }

        // $this->auth = $this->findByToken($token);

        if ($token !== null) {

            $this->auth = $this->findByToken($token);
        }

        return $this->auth; //$this->auth;
    }

    /**
     *
     * {@inheritdoc}
     * @see \Rentify\Api\Http\AccessControl\AccessManagerInterface::enforce()
     */
    public function enforce(Request $request): Auth
    {

        $crypto = new UtilCrypto();

        $keys = Config::get('custom.encryption_key');

        if (!$request->header(AccessManager::TOKEN_NAME)) {

            throw new AccessControlException('The request is missing a valid API key.', 403);
        }

        $token = $crypto->decryptAesGcm($request->header(AccessManager::TOKEN_NAME), $keys['auth-token-encryption-key'], "base64");

        if (empty($token)) {

            throw new AccessControlException('The request is missing a valid API key.', 403);
        }

        $auth = $this->findByToken($token);

        if (empty($auth->getUser())) {

            throw new AccessControlException('The request is missing a valid API key.', 401);
        }

        if (!($auth instanceof Auth)) {

            throw new AccessControlException('The request is missing a valid API key.', 401);
        }

        /*if ($auth->getUser()->isBlocked()) {
        throw new AccessControlException('Account blocked.');
        }

        if ($auth->getUser()->isParentBlocked()) {
        throw new AccessControlException('Account blocked. Contact your account manager for assistance.');
        }

        if (!$auth->getUser()->isActivated()) {
        throw new AccessControlException('Account activation required.');
        }*/

        if ($auth->getToken()->hasExpired()) {
            // Delete token ?
            throw new AccessControlException('Invalid access token.', 401);
        }

        return $auth;
    }

    /**
     * Reset users account
     * {@inheritdoc}
     * @see \Rentify\Api\Http\AccessControl\AccessManagerInterface::resetPassword()
     */
    public function resetPassword(Auth $auth, string $new_password): int
    {

        if (empty($new_password)) {
            throw new AccessControlException('New account password required.');
        }

        //dump($this->userRepository->find( $auth->getUser()->getId()));

        $auth->setUser($this->userRepository->find($auth->getUser()->getId()));
        $auth->setPassword($this->createHashPassword($new_password));
        $auth->setResetCode($this->generateIntegerOTP(6));
        return $this->update($auth);

    }

    /**
     * @param Request $request
     * @param Auth $auth
     * @return Auth
     */
    public function findAccount(Request $request, Auth $auth)
    {
        $account = DB::table('users')
            ->select('users.name as name',
                'users.phone as phone_number',
                'users.role_id as role',
                'auth.id as id',
                'auth.password as password',
                'roles.code as role')
            ->where('phone', $auth->getUsername())
            ->leftJoin('auth', 'auth.user_id', '=', 'users.id')
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->first();

        if ($account && Hash::check($auth->getPassword(), $account->password)) {
            // here you know data is valid

            //-- unset password

            unset($account->password);

            $auth->setId($account->id);

            // --set account token

            //$this->accessManager->setLoginToken($request, $auth);

            // $token = $this->tokenInterface->changeAccountToken($request, $auth);

            /*$perm = $this->permissionRepository->findPermission($auth);

            if (!$perm->hasPermission(Permission::PERM_GPS_CREATE)) {
            throw new InsufficientPrivilegeException();
            }*/

            /*
             * print(" permission of the users");
             * //dump($perm->getPermissions());
             *
             * if (!$perm->hasPermission(Permission::PERM_DELIVERY_OWN_STATUS_SET_COMPLETE )) {
             * throw new InsufficientPrivilegeException();
             * }
             *
             * // $auth = new Auth();
             *
             * // return dump($auth->getPermissions());
             *
             * return $perm;
             */

            // return
            return response()->json(
                [
                    "message" => "OK",
                    "code" => 200,
                    "payload" => [

                        "name" => $account->name,
                        "phone_number" => $account->phone_number,
                        "id" => $account->id,
                        "secret" => $token->getSecret(),
                        "role" => $account->role,

                    ],

                ]);

        } else {
            return response()->json(
                [
                    "code" => 422,
                    "message" => "Invalid username or password.",

                ]);

        }

        // if ($userVerified->count() > 0) return true;

        return response()->json(
            [
                "code" => 422,
                "message" => "Invalid username or password.",

            ]);
    }

    /**
     *
     * {@inheritdoc}
     * @see \Rentify\Api\Http\AccessControl\AccessManagerInterface::createPassword()
     */
    public function createHashPassword(string $text): string
    {
        return password_hash($text, PASSWORD_BCRYPT);
    }

    /**
     * @param int $id
     * @param Auth $auth
     * @return mixed
     */
    public function createPassword(int $id, Auth $auth)
    {

        $query = DB::table('auth')->
            insertGetId(
            [
                'user_id' => $id,
                'password' => $auth->getPassword(),
                'reset_code' => $auth->getResetCode(),
                'confirmation_code' => $auth->getConfirmationCode(),
            ]
        );

        return $query;

    }

    /**
     *
     * {@inheritdoc}
     * @see \Rentify\Api\Repositories\Auth\AuthRepositoryInterface::find()
     */
    public function find(int $id): ?Auth
    {

        $authQuery = new AuthQuery();

        $mainQuery = $authQuery::$query;

        $query = <<< SUB_QUERY
        $mainQuery
        WHERE a.id = ? LIMIT 1
        SUB_QUERY;

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $data = $stmt->fetch(PDO::FETCH_OBJ);

            if (false != $data) {

                return $this->format($data);
            }
        }

        return null;
    }

    /**
     *
     * {@inheritdoc}
     * @see \Rentify\Api\Repositories\Auth\AuthRepositoryInterface::findByUsername()
     */
    public function findByUsername(string $username): ?Auth
    {
        $authQuery = new AuthQuery();

        $mainQuery = $authQuery::$query;

        $query = <<< SUB_QUERY
        $mainQuery
        WHERE u.email = ? LIMIT 1
        SUB_QUERY;

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $username, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $data = $stmt->fetch(PDO::FETCH_OBJ);

            if (false != $data) {

                return $this->format($data);
            }
        }

        return null;
    }

    /**
     *
     * {@inheritdoc}
     * @see \Rentify\Api\Repositories\Auth\AuthRepositoryInterface::deleteToken()
     */
    public function deleteToken(string $token): int
    {
        $query = 'DELETE FROM auth_tokens WHERE BINARY secret = ?';

        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(1, $token, PDO::PARAM_STR);

        if ($stmt->execute()) {
            return $stmt->rowCount();
        }

        return 0;
    }

    /**
     *
     * {@inheritdoc}
     * @see \Rentify\Api\Repositories\Auth\AuthRepositoryInterface::installToken()
     */
    public function installToken(Auth $auth): int
    {
        $query = 'INSERT INTO auth_tokens (auth_id, secret, ip, client, expires_at, created_at) VALUES (?, ?, ?, ?, ?, ?)';

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $auth->getId(), PDO::PARAM_INT);
        $stmt->bindValue(2, $auth->getToken()
                ->getSecret(), PDO::PARAM_STR);
        $stmt->bindValue(3, $auth->getToken()
                ->getIp(), PDO::PARAM_STR);
        $stmt->bindValue(4, $auth->getToken()
                ->getClient(), PDO::PARAM_STR);
        $stmt->bindValue(5, $auth->getToken()
                ->getExpiresAt()
                ->format('Y-m-d H:i:s'), PDO::PARAM_STR);
        $stmt->bindValue(6, (new DateTime())->format('Y-m-d H:i:s'), PDO::PARAM_STR);

        if ($stmt->execute()) {
            return $stmt->rowCount();
        }

        return 0;
    }

    /**
     *
     * {@inheritdoc}
     * @see \Rentify\Api\Repositories\Auth\AuthRepositoryInterface::findByToken()
     */
    public function findByToken(string $token): ?Auth
    {

        $auth = new Auth();

        $authQuery = new AuthQuery();

        $mainQuery = $authQuery::$query;

        $query = <<< SUB_QUERY
        $mainQuery
        WHERE BINARY t.token = ? LIMIT 1
        SUB_QUERY;

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $token, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $data = $stmt->fetch(PDO::FETCH_OBJ);

            if (false !== $data) {
                return $this->format($data);
            }
        }

        return $auth;
    }

    /**
     *
     * {@inheritdoc}
     * @see \Rentify\Api\Repositories\Auth\AuthRepositoryInterface::findByConfirmationToken()
     */
    public function findByConfirmationToken(string $token): ?Auth
    {
        $authQuery = new AuthQuery();

        $mainQuery = $authQuery::$query;

        $query = <<< SUB_QUERY
        $mainQuery
        WHERE BINARY a.confirmation_code = ? LIMIT 1
        SUB_QUERY;

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $token, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $data = $stmt->fetch(PDO::FETCH_OBJ);

            if (false != $data) {

                return $this->format($data);
            }
        }

        return null;
    }

    /**
     *
     * {@inheritdoc}
     * @see \Rentify\Api\Repositories\Auth\AuthRepositoryInterface::findByUserId(int $id)
     */
    public function findByUserId(int $id): ?Auth
    {
        $authQuery = new AuthQuery();

        $mainQuery = $authQuery::$query;

        $query = <<< SUB_QUERY
        $mainQuery
        WHERE BINARY a.user_id = ? LIMIT 1
        SUB_QUERY;

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $id, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $data = $stmt->fetch(PDO::FETCH_OBJ);

            if (false != $data) {

                return $this->format($data);
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     * @see \Rentify\Api\Repositories\Auth\AuthRepositoryInterface::activate()
     */
    public function activate(int $id): int
    {
        $query = <<<UPDATEQUERY
        UPDATE auth a INNER JOIN users u ON a.user_id = u.id
        SET a.confirmation_code = NULL,
            a.updated_at = ?,
            u.activated = 1,
            u.updated_at = ?
        WHERE a.id = ?
        UPDATEQUERY;

        $time = (new DateTime())->format('Y-m-d H:i:s');

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $time, PDO::PARAM_STR);
        $stmt->bindValue(2, $time, PDO::PARAM_INT);
        $stmt->bindValue(3, $id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            return $stmt->rowCount();
        }

        return 0;
    }

    /**
     * activate reset code
     * @see \Rentify\Api\Repositories\Auth\AuthRepositoryInterface::activateResetCode()
     */
    public function activateResetCode(int $id): int
    {
        $query = <<<UPDATEQUERY
        UPDATE auth a INNER JOIN users u ON a.user_id = u.id
        SET a.reset_code = NULL,
            a.updated_at = ?,
            u.activated = 1,
            u.updated_at = ?
        WHERE a.id = ?
        UPDATEQUERY;

        $time = (new DateTime())->format('Y-m-d H:i:s');

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $time, PDO::PARAM_STR);
        $stmt->bindValue(2, $time, PDO::PARAM_INT);
        $stmt->bindValue(3, $id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            return $stmt->rowCount();
        }

        return 0;
    }

    /**
     * @param Auth $auth
     * @return int
     * @throws \Exception
     */
    public function update(Auth $auth): int
    {
        $user = $auth->getUser();
        $time = (new DateTime())->format('Y-m-d H:i:s');

        $query = <<<UPDATE_QUERY
        UPDATE users u JOIN auth a ON a.user_id = u.id
        SET u.name = ?,
        u.email = ?,
        u.phone_number = ?,
        u.role_id = ?,
        u.blocked = ?,
        u.activated = ?,
        u.updated_at = ?,
        a.password = ?,
        a.reset_code = ?,
        a.updated_at = ?
        WHERE a.id = ?
        UPDATE_QUERY;

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $user->getName(), PDO::PARAM_STR);
        $stmt->bindValue(2, $user->getEmail(), PDO::PARAM_STR);
        $stmt->bindValue(3, $user->getPhone(), PDO::PARAM_INT);
        $stmt->bindValue(4, $user->getRole()->getId(), PDO::PARAM_INT);
        $stmt->bindValue(5, $user->isBlocked() ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(6, $user->isActivated() ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(7, $time, PDO::PARAM_STR);
        $stmt->bindValue(8, $auth->getPassword(), PDO::PARAM_STR);
        $stmt->bindValue(9, $auth->getResetCode(), PDO::PARAM_STR);
        $stmt->bindValue(10, $time, PDO::PARAM_STR);
        $stmt->bindValue(11, $auth->getId(), PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $auth->getId(); //$stmt->rowCount();
        }

        return 0;
    }

    /**
     * Formats a given model/object into an instance of Auth object
     *
     * @param stdClass $model
     * @return Auth|NULL
     */
    private function format(?stdClass $model): ?Auth
    {

        if (empty($model)) {
            return null;
        }

        $auth = new Auth();
        $user = new User();

        $country = new Country();
        $userId = $model->user_id ?? null;
        $authId = $model->id ?? null;

        if ($authId) {

            // $this->activate($authId);
            //$auth->setUser($this->find($userId));

        }

        if ($userId) {
            //$auth->setUser($this->find($userId));

            $user->setId($userId);
            $user->setName($model->name);
            $user->setEmail($model->email);
            $user->setPhone($model->phone);
            $user->setActivated($model->activated);
            $user->setBlocked($model->blocked);

            $country->setName($model->country);
            $user->setCountry($country);
            //$user->setId($userId);

        }

        $role = new Role();

        if (isset($model->role)) {
            $decodeRole = json_decode($model->role);

            $role->setName($decodeRole->name);
            $role->setId($decodeRole->id);

            $user->setRole($role);
        }

        $auth->setUser($user);
        $auth->setId(isset($model->id) ? (int) $model->id : null);
        $auth->setPassword(isset($model->password) ? (string) $model->password : null);
        $auth->setResetCode(isset($model->reset_code) ? (string) $model->reset_code : null);
        $auth->setConfirmationCode(isset($model->confirmation_code) ? (string) $model->confirmation_code : null);
        $auth->setCreatedAt($this->createDateTime(empty($model->created_at) ? null : (string) $model->created_at));
        $auth->setUpdatedAt($this->createDateTime(empty($model->updated_at) ? null : (string) $model->updated_at));

        $auth->setToken($this->createToken(empty($model->authToken) ? null : json_decode($model->authToken)));

        $permissions = array_map(function (?stdClass $perm) {
            return $this->createPermission($perm);
        }, json_decode($model->permissions ?? '[]'));

        $auth->setPermissions(...$permissions);

        return $auth;
    }

    /**
     * Creates a token from a given data model
     *
     * @param stdClass $model
     * @return Token|NULL
     */
    private function createToken(?stdClass $model): ?Token
    {
        if (empty($model)) {
            return null;
        }

        $token = new Token();
        $token->setId(empty($model->id) ? null : (int) $model->id);
        $token->setSecret(empty($model->secret) ? null : (string) $model->secret);
        $token->setClient(empty($model->client) ? null : (string) $model->client);
        $token->setPushToken(empty($model->push_token) ? null : (string) $model->push_token);
        $token->setPushEnabled(empty($model->push_enabled) ? null : boolval($model->push_enabled));
        $token->setIp(empty($model->ip) ? null : (string) $model->ip);
        $token->setExpiresAt($this->createDateTime(empty($model->expires_at) ? null : (string) $model->expires_at));
        $token->setCreatedAt($this->createDateTime(empty($model->created_at) ? null : (string) $model->created_at));
        $token->setUpdatedAt($this->createDateTime(empty($model->updated_at) ? null : (string) $model->updated_at));

        return $token;
    }

    /**
     * Creates permission from a given data model
     *
     * @param stdClass $model
     * @return Permission|NULL
     */
    private function createPermission(?stdClass $model): ?Permission
    {
        if (empty($model)) {
            return null;
        }

        $permission = new Permission();
        $permission->setId(empty($model->id) ? null : (int) $model->id);
        $permission->setCode(empty($model->code) ? null : (string) $model->code);
        $permission->setDescription(empty($model->description) ? null : (string) $model->description);
        $permission->setCreatedAt($this->createDateTime(empty($model->created_at) ? null : (string) $model->created_at));
        $permission->setUpdatedAt($this->createDateTime(empty($model->updated_at) ? null : (string) $model->updated_at));

        return $permission;
    }

    /**
     * @param String $verificationCode
     * @return mixed
     */
    public function confirmAccount(String $verificationCode)
    {
        // TODO: Implement confirmAccount() method.
    }

    /**
     * @param String $phone
     * @return mixed|string
     * @throws AccessControlException
     */
    public function resetForgottenPassword(String $email)
    {

        // Check validity of user email
        $auth = $this->verify($email);

        if (!($auth instanceof Auth)) {

            throw new AccessControlException('Sorry something happen, try again.', 404);

        }

        // Reset password to a random string
        $password = $this->resetAccountForgottenPassword($auth, 9);

        return $password;

    }

    public function verify(string $username): ?Auth
    {
        return $this->findByUsername($username);
    }

    /**
     *
     * {@inheritdoc}
     * @see \Rentify\Api\Http\AccessControl\AccessManagerInterface::resetForgottenPassword()
     */
    public function resetAccountForgottenPassword(Auth $auth, int $length = 5): Auth
    {
        $password = strtoupper($this->createRandomCode($length));

        $auth->setPassword($this->createAccountPassword($password));
        $auth->setResetCode($this->generateIntegerOTP(6));

        $this->update($auth);

        return $auth;
    }

    /**
     *
     * {@inheritdoc}
     * @see \Rentify\Api\Http\AccessControl\AccessManagerInterface::createPassword()
     */
    public function createAccountPassword(string $text): string
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
        return bin2hex(random_bytes($length));
    }

    /**
     * Change existing password
     *
     * @param String $current
     * @param String $new
     * @param String $token
     * @return mixed
     */
    public function changePassword(Auth $auth, String $current, String $new, String $token)
    {

        if (empty($old_password)) {
            throw new AccessControlException('Current account password required.');
        }

        if (empty($new_password)) {
            throw new AccessControlException('New account password required.');
        }

        // Check correctness of old password
        if (true !== Hash::check($old_password, $auth->getPassword())) {
            throw new AccessControlException('Invalid account password.');
        }

        // Ensure that user is not resetting password to the same one
        if ($old_password === $new_password) {
            throw new AccessControlException('New and current passwords must not be the same.');
        }

        /// $auth->setPassword($this->createHashPassword($new_password));
        /// dump($this->update($auth->getId(), $auth));

        //return $this->update($auth->getId(), $auth);

        //dump($this->userRepository->find( $auth->getUser()->getId()));

        $auth->setUser($this->userRepository->find($auth->getUser()->getId()));

        $auth->setPassword($this->createHashPassword($new_password));
        $auth->setResetCode($this->generateIntegerOTP(6));

        return $this->update($auth);

    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Auth\AuthRepositoryInterface::create()
     */
    public function create(Auth $auth): int
    {
        $user = $auth->getUser();
        $time = (new DateTime())->format('Y-m-d H:i:s');

        $this->connection->beginTransaction();

        try {
            $userQuery = <<<USERQUERY
            INSERT INTO users (
                name,
                phone,
                email,
                role_id,
                parent_id,
                country_code,
                creator_id,
                image_id,
                blocked,
                activated,
                created_at
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            USERQUERY;

            $stmt = $this->connection->prepare($userQuery);

            $stmt->bindValue(1, $user->getName(), PDO::PARAM_STR);
            $stmt->bindValue(2, $user->getPhone(), PDO::PARAM_STR);
            $stmt->bindValue(3, $user->getEmail(), PDO::PARAM_STR);
            $stmt->bindValue(4, $user->getRole()
                    ->getId(), PDO::PARAM_INT);
            $stmt->bindValue(5, $user->getParent() ? $user->getParent()
                    ->getId() : null, PDO::PARAM_INT);
            $stmt->bindValue(6, $user->getCountry()
                    ->getIsoCode(), PDO::PARAM_STR);
            $stmt->bindValue(7, $user->getCreator() ? $user->getCreator()
                    ->getId() : null, PDO::PARAM_INT);
            $stmt->bindValue(8, $user->getImage() ? $user->getImage()
                    ->getId() : null, PDO::PARAM_INT);
            $stmt->bindValue(9, $user->isBlocked() ? 1 : 0, PDO::PARAM_INT);
            $stmt->bindValue(10, $user->isActivated() ? 1 : 0, PDO::PARAM_INT);
            $stmt->bindValue(11, $time, PDO::PARAM_STR);

            $stmt->execute();

            $userId = (int) $this->connection->lastInsertId();

            $authQuery = 'INSERT INTO auth (user_id, password, reset_code, confirmation_code, created_at) VALUES (?, ?, ?, ?, ?)';

            $astmt = $this->connection->prepare($authQuery);
            $astmt->bindValue(1, $userId, PDO::PARAM_INT);
            $astmt->bindValue(2, $auth->getPassword(), PDO::PARAM_STR);
            $astmt->bindValue(3, $auth->getResetCode(), PDO::PARAM_STR);
            $astmt->bindValue(4, $auth->getConfirmationCode(), PDO::PARAM_STR);
            $astmt->bindValue(5, $time, PDO::PARAM_STR);

            $astmt->execute();

            $authId = (int) $this->connection->lastInsertId();

            $this->connection->commit();

            return $authId;
        } catch (Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

}
