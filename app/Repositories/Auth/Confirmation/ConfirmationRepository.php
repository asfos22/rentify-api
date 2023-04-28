<?php

namespace App\Repositories\Auth\Confirmation;

use App\Http\Exception\AccessControlException;
use App\Repositories\Auth\Auth;
use App\Repositories\Auth\AuthQuery;
use App\Repositories\Auth\Token;
use App\Repositories\DateTime;
use App\Repositories\Mail\MailRepository;
use App\Repositories\User\User as RUser;
use App\Repositories\Util\Utils;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PDO;

/**
 * @author Foster Asante <asantefoster22@gmail.com>
 */
class ConfirmationRepository implements ConfirmationRepositoryInterface
{

    /**
     * @var
     */
    private $token;
    /**
     * @var Utils
     */

    private $util;

    /**
     * @var
     */
    protected $connection;

    /**
     * @var
     */
    private $mailRepository;

    public function __construct(

        MailRepository $mailRepository,
        Utils $utils

    ) {
        // $this->mailRepository = $mailRepository;
        $this->util = $utils;

        $this->connection = DB::connection()->getPdo();

    }

    /**
     *
     * {@inheritdoc}
     * @see \Rentify\Api\Repositories\Auth\AuthRepositoryInterface::findByToken()
     */
    public function findById(string $token): ?Auth
    {
        $auth = new Auth();

        $authQuery = new AuthQuery();

        $mainQuery = $authQuery::$query;

        $query = <<<QUERY

        $mainQuery

        WHERE a.id = ? LIMIT 1

        QUERY;

        /* $auth = new Auth();
        $query = $this->query . ' WHERE a.id = ? LIMIT 1';

         */

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $token, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $data = $stmt->fetch(PDO::FETCH_OBJ);

            if (false !== $data) {
                //return $this->format($data);
            }
        }

        return $auth;
    }

    /**
     * Find account by confirmation codes
     * @param String $confirmationCode
     * @return Auth|null
     * @throws AccessControlException
     */
    public function confirmAccountByToken(String $confirmationCode): ?Auth
    {
        $auth = new Auth();

        $authQuery = new AuthQuery();

        $mainQuery = $authQuery::$query;

        $query = <<<QUERY

        $mainQuery

        WHERE BINARY a.confirmation_code = ? LIMIT 1

        QUERY;

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $confirmationCode, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $data = $stmt->fetch(PDO::FETCH_OBJ);

            if (false != $data) {

                $authId = $data->id ?? null;

                if ($authId) {

                    $this->activate($authId);

                    $user = new RUser();

                    $user->setId($data->user_id);
                    $auth->setId($data->id);
                    $auth->setUser($user);
                    $auth->setConfirmationCode($data->confirmation_code);

                    return $auth;

                }

            }

            /* if (false == $data) {

        throw new AccessControlException('Invalid confirmation code', 402);

        }*/
        }

        return $auth;

    }

    /**
     * Find account by resetCode
     * @param String $resetCode
     * @return Auth|null
     * @throws AccessControlException
     */
    public function findByResetCode(String $resetCode): ?Auth
    {

        $auth = new Auth();

        $authQuery = new AuthQuery();

        $mainQuery = $authQuery::$query;

        $query = <<<QUERY

        $mainQuery

        WHERE BINARY a.reset_code = ? LIMIT 1

        QUERY;

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $resetCode, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $data = $stmt->fetch(PDO::FETCH_OBJ);

            if (false != $data) {

                $authId = $data->id ?? null;

                if ($authId) {

                   // $this->activate($authId);

                    $user = new RUser();

                    $user->setId($data->user_id);
                    $auth->setId($data->id);
                    $auth->setUser($user);
                    $auth->setResetCode($data->reset_code);

                    return $auth;

                }

            }

            /*if (false == $data) {

             throw new AccessControlException('Invalid confirmation code', 402);

             }*/
        }

        return $auth;

    }

    /**
     * activate account
     * @param $id
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

    //--------

    public function findbyByEmailOrPhone(String $verificationCode): ?Auth
    {

    }

    public function findByPhone(String $verificationCode): ?Auth
    {

    }

}
