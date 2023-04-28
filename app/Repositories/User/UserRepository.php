<?php

namespace App\Repositories\User;

use App\Repositories\Location\Country;
use App\Repositories\Repository;
use App\Repositories\Role\Role;
use Illuminate\Support\Facades\DB;
use PDO;
use App\Repositories\DateTime;
use Exception;

/**
 *
 * @author Foster Asante <asantefoster22@gmail.com>
 *
 */
class UserRepository extends Repository implements UserRepositoryInterface
{
    private static $mainQuery = <<<QUERY
    FROM users u 
    JOIN roles r ON u.role_id = r.id 
    
    QUERY;

    private static $subQuery = <<<QUERY
       u.id as uid, 
       u.name AS uname,
       u.phone_number AS uphone, 
       u.email AS uemail, 
       u.country_code AS ucountry_code,  
       u.activated AS uactivated,
       u.blocked AS ublocked,
       u.role_id AS urole_id,
       r.id AS rid,
       r.name AS rname,
       r.slug AS rslug

    QUERY;

    /**
     * @var
     */
    protected $connection;

    public function __construct()
    {

        $this->connection = DB::connection()->getPdo();

    }


    /**
     * @param int $id
     * @return User|null
     */
    public function find(int $id): ?User
    {

    
        $mainQuery = self::$mainQuery;
        $subQuery = self::$subQuery;

        $query = <<<QUERY
        SELECT  
        $subQuery
        $mainQuery WHERE u.id = ? LIMIT 1

        QUERY;

        $user = new User();

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $id, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $data = $stmt->fetch(PDO::FETCH_OBJ);

            if (false !== $data) {

                $role = new Role();

                $user->setId($data->uid);
                $user->setName($data->uname);
                $user->setPhone($data->uphone);
                $user->setEmail($data->uemail);
                $user->setActivated($data->uactivated);
                $user->setBlocked($data->ublocked);
                $role->setId($data->rid);
                $role->setName($data->rname);
                $user->setRole($role);

                return $user;
            }
        }

        return $user;
    }

    /**
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User
    {
        $user = new User();
        $mainQuery = self::$mainQuery;

        $query = <<<QUERY
        SELECT * $mainQuery WHERE u.email = ? LIMIT 1
        QUERY;

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $email, PDO::PARAM_STR);


        if ($stmt->execute()) {
            $data = $stmt->fetch(PDO::FETCH_OBJ);

            if (false !== $data) {

                $role = new Role();
                // $country = new Country();
                $account = new Account();

                $user->setId($data->id);
                $user->setName($data->name);
                $user->setPhone($data->phone_number);
                $user->setEmail($data->email);
                $user->setActivated($data->activated);
                $user->setBlocked($data->blocked);
                $role->setId($data->role_id);
                //$country->setIsoCode($data->country_code);
                $user->setRole($role);
                //$user->setCountry($country);
                // $account->setCreator($data->creator_id);
                // $user->setCreator($data->creator_id);
                // $account->setParentBlocked($data->parent_id);
                //$user->setCreator($data->creator_id);
                // $user->setImage($data->image_id);
                //$user->setParentBlocked($data->parent_id);


                return $user;
            }

            return $user;
        }

    }

    /**
     * Fetches a single user by email or phone
     * @param string|null $email
     * @param string|null $phone
     * @return User|null
     */
    public function findByEmailOrPhone(string $email = null, string $phone = null): ?User
    {
        $user = new User();

        $mainQuery = self::$mainQuery;
        $subQuery = self::$subQuery;

        $query = <<<QUERY
        SELECT $subQuery
        $mainQuery WHERE u.email = ? OR u.phone_number =? LIMIT 1
        QUERY;

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $email, PDO::PARAM_STR);
        $stmt->bindValue(2, $phone, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $data = $stmt->fetch(PDO::FETCH_OBJ);

            if (false !== $data) {

                $role = new Role();

                $user->setId($data->uid);
                $user->setName($data->uname);
                $user->setPhone($data->uphone);
                $user->setEmail($data->uemail);
                $user->setActivated($data->uactivated);
                $user->setBlocked($data->ublocked);
                $role->setId($data->rid);
                $role ->setName($data->rname);
                $role ->setCode($data->rslug);
                $user->setRole($role);

                return $user;
            }
        }

        return $user;
    }


    /**
     * @param string $phone
     * @return User|null
     */
    public function findByPhone(string $phone): ?User
    {

    }

    /**
     * @return array
     */
    public function fetch(): array
    {

        $users = [];


        return $users;
    }

    /**
     * @param array $ids
     * @return array
     */
    public function fetchByIds(array $ids): array
    {

    }

    /**
     * @return int
     */
    public function count(): int
    {


        $mainQuery = self::$mainQuery;

        $query = <<<QUERY
        SELECT COUNT(*) AS numUsers $mainQuery 
        QUERY;

        $stmt = $this->connection->prepare($query);


        if ($stmt->execute()) {
            $result = $stmt->fetch(PDO::FETCH_OBJ);
            return isset($result->numUsers) ? (int)$result->numUsers : 0;
        }

        return 0;
    }

    /**
     * @param int $id
     * @param User $user
     * @return int
     * @throws \Exception
     */
    public function update(int $id, User $user): int
    {
        $query = <<<UPDATEQUERY
        UPDATE users 
        SET name = ?,
            phone = ?,
            email = ?,
            image_id = ?,
            blocked = ?,
            activated = ?,
            role_id = ?,
            parent_id = ?,
            creator_id = ?,
            updator_id = ?,
            country_code = ?,
            updated_at = ?,
            parent_blocked = ? 
        WHERE id = ?
        UPDATEQUERY;

        $stmt = $this->connection->prepare($query);
        $time = (new DateTime())->format('Y-m-d H:i:s');

        $stmt->bindValue(1, $user->getName(), PDO::PARAM_STR);
        $stmt->bindValue(2, $user->getPhone(), PDO::PARAM_STR);
        $stmt->bindValue(3, $user->getEmail(), PDO::PARAM_STR);
        $stmt->bindValue(4, $user->getImage() ? $user->getImage()
            ->getId() : null, PDO::PARAM_INT);
        $stmt->bindValue(5, $user->isBlocked() ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(6, $user->isActivated() ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(7, $user->getRole() ? $user->getRole()
            ->getId() : null, PDO::PARAM_INT);
        $stmt->bindValue(8, $user->getParent() ? $user->getParent()
            ->getId() : null, PDO::PARAM_INT);
        $stmt->bindValue(9, $user->getCreator() ? $user->getCreator()
            ->getId() : null, PDO::PARAM_INT);
        $stmt->bindValue(10, $user->getUpdator() ? $user->getUpdator()
            ->getId() : null, PDO::PARAM_INT);
        $stmt->bindValue(11, $user->getCountry() ? $user->getCountry()
            ->getIsoCode() : null, PDO::PARAM_STR);
        $stmt->bindValue(12, $time, PDO::PARAM_STR);
        $stmt->bindValue(13, $user->isParentBlocked() ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(14, $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $stmt->rowCount();
        }

        return 0;
    }

    /**
     * return newly create user
     * @param User $user
     * @return  int Unique id of newly created user
     */
    public function create(User $user): User
    {
        //$user = $auth->getUser();
        $time = (new DateTime())->format('Y-m-d H:i:s');

        $this->connection->beginTransaction();

        try {
            $userQuery = <<<USERQUERY
            INSERT INTO users (
                name,
                phone_number,
                email,
                role_id,
                country_code,
                blocked,
                activated,
                created_at
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            USERQUERY;

            $stmt = $this->connection->prepare($userQuery);

            $stmt->bindValue(1, $user->getName(), PDO::PARAM_STR);
            $stmt->bindValue(2, $user->getPhone(), PDO::PARAM_STR);
            $stmt->bindValue(3, $user->getEmail(), PDO::PARAM_STR);
            $stmt->bindValue(4, $user->getRole() ->getId(), PDO::PARAM_INT);
            $stmt->bindValue(5, $user->getCountry()->getIsoCode(), PDO::PARAM_STR);
            $stmt->bindValue(6, $user->isBlocked() ? 1 : 0, PDO::PARAM_INT);
            $stmt->bindValue(7, $user->isActivated() ? 1 : 0, PDO::PARAM_INT);
            $stmt->bindValue(8, $time, PDO::PARAM_STR);

            $stmt->execute();
             
            $user->setId((int) $this->connection->lastInsertId());
            

           /* $userId = (int) $this->connection->lastInsertId();

            $authQuery = 'INSERT INTO auth (user_id, password, reset_code, confirmation_code, created_at) VALUES (?, ?, ?, ?, ?)';

            $astmt = $this->connection->prepare($authQuery);
            $astmt->bindValue(1, $userId, PDO::PARAM_INT);
            $astmt->bindValue(2, $auth->getPassword(), PDO::PARAM_STR);
            $astmt->bindValue(3, $auth->getResetCode(), PDO::PARAM_STR);
            $astmt->bindValue(4, $auth->getConfirmationCode(), PDO::PARAM_STR);
            $astmt->bindValue(5, $time, PDO::PARAM_STR);

            $astmt->execute();

            $authId = (int) $this->connection->lastInsertId();*/

            $this->connection->commit();

            return $user;
        } catch (Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }


}

