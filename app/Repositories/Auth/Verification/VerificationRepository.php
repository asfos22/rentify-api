<?php

namespace App\Repositories\Auth\Verification;

use App\Http\Exception\AccessControlException;
use App\Models\User;
use App\Repositories\Auth\Auth;
use App\Repositories\Auth\AuthQuery;
use App\Repositories\Auth\Confirmation\ConfirmationRepositoryInterface;
use App\Repositories\Auth\Token;
use App\Repositories\Auth\Verification\VerificationRepositoryInterface;
use App\Repositories\DateTime;
use App\Repositories\Mail\MailRepository;
use App\Repositories\Tokens\TokenInterface;
use App\Repositories\User\Account;
use App\Repositories\Util\Utils;
use App\Traits\Tokens;
use Carbon\Carbon;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Exception;
use PDO;
use stdClass;
use App\Repositories\User\User AS RUser;


/**
 * @author Foster Asante <asantefoster22@gmail.com>
 */
class VerificationRepository implements VerificationRepositoryInterface
{
    /**
     * @var
     */
    use Tokens;
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
    private $mailRepository;

    /**
     * @var ConfirmationRepositoryInterface;
     */
    private $confirmationRepositoryInterface;

    protected $connection;
   

    public function __construct(

        MailRepository $mailRepository,
        Utils $utils,
        ConfirmationRepositoryInterface $confirmationRepositoryInterface 

    )
    {
        $this->mailRepository = $mailRepository;
        $this->util = $utils;
       // $this->confirmationRepositoryInterface = $confirmationRepositoryInterface;

        $this->connection = DB::connection()->getPdo();
    }


    /**
     * @param Request $request
     * @return mixed
     */
    public function verifyAccountAuth(Request $request)
    {
        // TODO: Implement verifyAccountAuth() method.
    }

    /**
     * Create auth account and send email|| sms verification 
     * @param Auth $auth
     * @return mixed|void
     * @throws \Exception
     */
    public function createAccountVerification(Auth $auth, bool $isSendNotification =false)
    {
    
        $account = DB::table('users')
            ->select('email', 'name')
            ->where('id', $auth->getUser()->getId())
            ->latest()->first();

        $user = new User();
       
       // $this->token->setSecret(/*$user->token(new DateTime())*/);
       // $this->token->setReset
        $time = (new DateTime())->format('Y-m-d H:i:s');
        $auth->setResetCode($this->generateIntegerOTP(6));
        $auth->setCreatedAt(new DateTime($time));
        $auth->setUpdatedAt(new DateTime($time));
    
        $user = $auth->getUser();
       

        $this->connection->beginTransaction();

        try {
            $userQuery = <<<USERQUERY
            INSERT INTO auth (

                user_id, 
                password,
                reset_code,
                confirmation_code, 
                created_at
            )
            VALUES (?, ?, ?, ?, ?)
            USERQUERY;

            $stmt = $this->connection->prepare($userQuery);

           /* $stmt->bindValue(1, $user->getName(), PDO::PARAM_STR);
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

            $authQuery = 'INSERT INTO auth (
                user_id, password, reset_code, confirmation_code, created_at) VALUES (?, ?, ?, ?, ?)';

            $stmt = $this->connection->prepare($authQuery);*/

            $stmt->bindValue(1, $auth->getUser()->getId(), PDO::PARAM_INT);
            $stmt->bindValue(2, $this->util->createPassword($auth->getPassword()), PDO::PARAM_STR);
            $stmt->bindValue(3, $auth->getResetCode(), PDO::PARAM_STR);
            $stmt->bindValue(4, $this->generateIntegerOTP(6), PDO::PARAM_STR);
            $stmt->bindValue(5, $auth->getCreatedAt(), PDO::PARAM_STR);

           
            if (!empty($account)) {

                $stmt->execute();
            }

            $authId = (int) $this->connection->lastInsertId();

            $this->connection->commit();

            return $authId;
        } catch (Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }



    }


    /**
     * @param String $verificationCode
     * @return Auth|null
     * @throws AccessControlException
     */
    public function verifyAccountByVerification(String $email, String $verificationCode): ?Auth
    {
        
        $authQuery = new AuthQuery();

        $mainQuery =   $authQuery::$query;

        $query = <<<UPDATEQUERY
        
        $mainQuery

        WHERE u.email = ? AND BINARY a.confirmation_code = ? LIMIT 1

        UPDATEQUERY;

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $email, PDO::PARAM_STR);
        $stmt->bindValue(2, $verificationCode, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $data = $stmt->fetch(PDO::FETCH_OBJ);

            if (false != $data) {

            
                $authId = $data->id ?? null;

                if ($authId) {
                   
                  /// $this->confirmationRepositoryInterface->activate($authId);
                    $user = new RUser();
                    $auth = new Auth();
                    $user->setId($data->user_id);
                    $auth->setResetCode($data->reset_code);
                    $auth->setConfirmationCode($data->confirmation_code);
                    $auth->setId($data->id);
                    $auth->setUser($user);

                   /// $this->activate($auth->getId());

                    return $auth;

                }

            }

            /*if (false == $data) {

                throw new AccessControlException('Sorry we couldnot verify account', 402);

            }*/
        }

        return null;

    }


    /**
     * Verify forgotten password acccount
     * @param String $verificationCode
     * @return Auth|null
     * @throws AccessControlException
     */
    public function verifyForgotAccountByVerification(String $email, String $verificationCode): ?Auth
    {
        
        $authQuery = new AuthQuery();

        $mainQuery =   $authQuery::$query;

        $query = <<<UPDATEQUERY
        
        $mainQuery

        WHERE u.email = ? AND BINARY a.reset_code = ? LIMIT 1

        UPDATEQUERY;

        //$query = $this->query . ' WHERE u.email=? AND BINARY a.confirmation_code = ? LIMIT 1';

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $email, PDO::PARAM_STR);
        $stmt->bindValue(2, $verificationCode, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $data = $stmt->fetch(PDO::FETCH_OBJ);

            if (false != $data) {

                $authId = $data->id ?? null;

                if ($authId) {
                   
                  /// $this->confirmationRepositoryInterface->activate($authId);
                    $user = new RUser();
                    $auth = new Auth();
                    $user->setId($data->user_id);
                    $auth->setResetCode($data->reset_code);
                    $auth->setConfirmationCode($data->confirmation_code);
                    $auth->setId($data->id);
                    $auth->setUser($user);

                   // $this->activate($auth->getId());

                   //$user = new User();
       
                   // $this->token->setSecret($this->generateIntegerOTP(6)/*$user->token(new DateTime())*/);
                   // $this->token->setReset
                    $auth->setResetCode($this->token(new DateTime()));
                    $this->activateForgotPassword($auth->getId(), $auth->getResetCode());
                
                    return $auth;

                }

            }

            if (false == $data) {

                throw new AccessControlException('Sorry we couldnot verify account', 402);

            }
        }

        return null;

    }



    /**
     * activate account reset 
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


    /**
     * activate forgot account reset 
     * @param $id
     * @param $resetCode
     */

    public function activateForgotPassword(int $id, string $resetCode): int
    {
        $query = <<<UPDATEQUERY
        UPDATE auth a INNER JOIN users u ON a.user_id = u.id
        SET a.reset_code = ?,
            a.updated_at = ?,
            u.activated = 1,
            u.updated_at = ?
        WHERE a.id = ?

        UPDATEQUERY;

        $time = (new DateTime())->format('Y-m-d H:i:s');

        $stmt = $this->connection->prepare($query);

        $stmt->bindValue(1, $resetCode, PDO::PARAM_STR);
        $stmt->bindValue(2, $time, PDO::PARAM_STR);
        $stmt->bindValue(3, $time, PDO::PARAM_INT);
        $stmt->bindValue(4, $id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            return $stmt->rowCount();
        }

        return 0;
    }



}