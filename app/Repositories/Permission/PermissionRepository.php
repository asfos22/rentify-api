<?php
declare (strict_types = 1);

namespace App\Repositories\Permission;

use App\Http\AccessControl\AccessManagerInterface;
use App\Repositories\Auth\Auth;
use App\Repositories\DateTime;
use App\Repositories\User\User;
use Exception;
use Illuminate\Support\Facades\DB;
use PDO;
use stdClass;

/**
 * @author Foster Asante <foster@Rentifygh.com>
 */
final class PermissionRepository implements PermissionRepositoryInterface
{

    /**
     * @var AccessManagerInterface
     */
    private $accessManager;
    /**
     * @var
     */
    protected $connection;

    public function __construct(

        // TokenInterface $tokenInterface,

        AccessManagerInterface $accessManager

        // PermissionRepositoryInterface $permissionRepository

    ) {
        // $this->tokenInterface = $tokenInterface;

        $this->accessManager = $accessManager;

        $this->connection = DB::connection()->getPdo();

    }
    // //'push_token', t.push_token,
    //        'push_enabled', IF(t.push_enabled, TRUE, FALSE) IS TRUE,

    private $query = <<<QUERY
    WITH ctePermissions (role_id, permissions) AS (
    	SELECT rp.role_id, JSON_ARRAYAGG(JSON_OBJECT(
    		'id', p.id,
            'code', p.slug,
            'description', p.description,
            'created_at', DATE_FORMAT(p.created_at, '%Y-%m-%dT%H:%i:%sZ'),
            'updated_at', DATE_FORMAT(p.updated_at, '%Y-%m-%dT%H:%i:%sZ')
        )) AS permissions FROM roles_permissions rp JOIN permissions p ON rp.permission_id = p.id GROUP BY rp.role_id
    )
    SELECT
    a.id,
    a.user_id,
    a.password,
    a.reset_code,
    a.confirmation_code,
    DATE_FORMAT(a.created_at, '%Y-%m-%dT%H:%i:%sZ') AS created_at,
    DATE_FORMAT(a.updated_at, '%Y-%m-%dT%H:%i:%sZ') AS updated_at,
    p.permissions,
    JSON_OBJECT(
    	'id', t.id,
        'secret', t.token,
        'client', t.client,
        'ip', t.ip,
        'expires_at', DATE_FORMAT(t.expires_at, '%Y-%m-%dT%H:%i:%sZ'),
        'created_at', DATE_FORMAT(t.created_at, '%Y-%m-%dT%H:%i:%sZ'),
        'updated_at', DATE_FORMAT(t.updated_at, '%Y-%m-%dT%H:%i:%sZ')
    ) as authToken
    FROM auth a
    JOIN users u ON u.id = a.user_id
    JOIN ctePermissions p ON p.role_id = u.role_id
    LEFT JOIN auth_tokens t ON t.auth_id = a.id
    QUERY;

    /**
     * @param Auth $auth
     * @return mixed|string
     */
    public function findPermission(Auth $auth): Auth
    {

        $query = DB::select($query = $this->query . ' WHERE a.id = ' . $auth->getId() . ' LIMIT 1');

        if (false != $query) {

            $auth = $this->format($query[0]);

        }
        return $auth;

    }

    /**
     * {@inheritdoc}
     * @param int $id
     * @return Auth|null
     */
    public function find(int $id): ?Auth
    {
        $query = $this->query . ' WHERE a.id = ? LIMIT 1';

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
     * @see \App\Repositories\Auth\AuthRepositoryInterface::findByUsername()
     */
    public function findByUsername(string $username): ?Auth
    {
        $query = $this->query . ' WHERE u.phone = ? LIMIT 1';

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
     * @see \App\Repositories\Auth\AuthRepositoryInterface::deleteToken()
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
     * @see \App\Repositories\Auth\AuthRepositoryInterface::installToken()
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
     * @see \App\Repositories\Auth\AuthRepositoryInterface::findByToken()
     */
    public function findByToken(string $token): ?Auth
    {
        $query = $this->query . ' WHERE BINARY t.token = ? LIMIT 1';

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $token, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $data = $stmt->fetch(PDO::FETCH_OBJ);

            if (false !== $data) {
                return $this->format($data);
            }
        }

        return null;
    }

    /**
     *
     * {@inheritdoc}
     * @see \App\Repositories\Auth\AuthRepositoryInterface::findByConfirmationToken()
     */
    public function findByConfirmationToken(string $token): ?Auth
    {
        $query = $this->query . ' WHERE BINARY a.confirmation_code = ? LIMIT 1';

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
     * @see \App\Repositories\Auth\AuthRepositoryInterface::activate()
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
     *
     * {@inheritdoc}
     * @see \App\Repositories\Auth\AuthRepositoryInterface::update()
     */
    public function update(int $id, Auth $auth): int
    {
        $user = $auth->getUser();
        $time = (new DateTime())->format('Y-m-d H:i:s');

        $query = <<<UPDATE_QUERY
        UPDATE users u JOIN auth a ON a.user_id = u.id
        SET u.name = ?,
        u.email = ?,
        u.role_id = ?,
        u.parent_id = ?,
        u.country_code = ?,
        u.creator_id = ?,
        u.image_id = ?,
        u.blocked = ?,
        u.activated = ?,
        u.updated_at = ?,
        a.password = ?,
        a.updated_at = ?
        WHERE a.id = ?
        UPDATE_QUERY;

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $user->getName(), PDO::PARAM_STR);
        $stmt->bindValue(2, $user->getEmail(), PDO::PARAM_STR);
        $stmt->bindValue(3, $user->getRole()
                ->getId(), PDO::PARAM_INT);
        $stmt->bindValue(4, $user->getParent() ? $user->getParent()
                ->getId() : null, PDO::PARAM_INT);
        $stmt->bindValue(5, $user->getCountry()
                ->getIsoCode(), PDO::PARAM_STR);
        $stmt->bindValue(6, $user->getCreator() ? $user->getCreator()
                ->getId() : null, PDO::PARAM_INT);
        $stmt->bindValue(7, $user->getImage() ? $user->getImage()
                ->getId() : null, PDO::PARAM_INT);
        $stmt->bindValue(8, $user->isBlocked() ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(9, $user->isActivated() ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(10, $time, PDO::PARAM_STR);
        $stmt->bindValue(11, $auth->getPassword(), PDO::PARAM_STR);
        $stmt->bindValue(12, $time, PDO::PARAM_STR);
        $stmt->bindValue(13, $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $stmt->rowCount();
        }

        return 0;
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

        $userId = $model->user_id ?? null;

        if ($userId) {
            // $auth->setUser($this->userRepository->find($userId, new FieldCollection()));
        }

        $auth->setId(isset($model->id) ? (int) $model->id : null);
        $auth->setPassword(isset($model->password) ? (string) $model->password : null);
        $auth->setResetCode(isset($model->reset_code) ? (string) $model->reset_code : null);
        $auth->setConfirmationCode(isset($model->confirmation_code) ? (string) $model->confirmation_code : null);
        // $auth->setCreatedAt($this->createDateTime(empty($model->created_at) ? null : (string) $model->created_at));
        // $auth->setUpdatedAt($this->createDateTime(empty($model->updated_at) ? null : (string) $model->updated_at));

        //$auth->setToken($this->createToken(empty($model->authToken) ? null : json_decode($model->authToken)));

        $permissions = array_map(function (?stdClass $perm) {
            return $this->createPermission($perm);
        }, json_decode($model->permissions ?? '[]'));

        $auth->setPermissions(...$permissions);

        return $auth;
    }

    private function createPermission(?stdClass $model): ?Permission
    {
        if (empty($model)) {
            return null;
        }

        $permission = new Permission();
        $permission->setId(empty($model->id) ? null : (int) $model->id);
        $permission->setCode(empty($model->code) ? null : (string) $model->code);
        $permission->setDescription(empty($model->description) ? null : (string) $model->description);
        // $permission->setCreatedAt($this->createDateTime(empty($model->created_at) ? null : (string)$model->created_at));
        // $permission->setUpdatedAt($this->createDateTime(empty($model->updated_at) ? null : (string)$model->updated_at));

        return $permission;
    }

}
